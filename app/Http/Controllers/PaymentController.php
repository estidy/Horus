<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function pdf(Quote $quote): Response
    {
        abort_unless($quote->status === 'accepted', 404);

        $quote->load(['client', 'payments' => fn ($query) => $query
            ->orderBy('payment_date')
            ->orderBy('id')]);
        $paidAmount = (float) $quote->payments->sum('amount');
        $remainingAmount = max(0, round((float) $quote->total_amount - $paidAmount, 2));
        $pdf = Pdf::loadView('admin.quotes.payments-pdf', compact('quote', 'paidAmount', 'remainingAmount'))
            ->setPaper('a4');

        return $pdf->download('reporte-pagos-horus-' . $quote->id . '.pdf');
    }

    public function store(Request $request, Quote $quote): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'method' => ['required', 'in:' . implode(',', array_keys(Payment::METHODS))],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($quote, $data): void {
            $lockedQuote = Quote::query()->lockForUpdate()->findOrFail($quote->id);

            if ($lockedQuote->status !== 'accepted') {
                throw ValidationException::withMessages([
                    'payment' => 'Solo se pueden registrar pagos de presupuestos aceptados.',
                ]);
            }

            $paidAmount = (float) $lockedQuote->payments()->sum('amount');
            $remainingAmount = round((float) $lockedQuote->total_amount - $paidAmount, 2);
            $amount = round((float) $data['amount'], 2);

            if ($remainingAmount <= 0 || $amount > $remainingAmount) {
                throw ValidationException::withMessages([
                    'amount' => 'El importe ingresado supera el saldo pendiente del presupuesto.',
                ]);
            }

            $lockedQuote->payments()->create($data);
        });

        return redirect()->route('admin.presupuestos.show', $quote)->with('success', 'Pago registrado correctamente.');
    }

    public function destroy(Quote $quote, Payment $payment): RedirectResponse
    {
        DB::transaction(function () use ($quote, $payment): void {
            $lockedQuote = Quote::query()->lockForUpdate()->findOrFail($quote->id);
            $paymentToDelete = $lockedQuote->payments()->findOrFail($payment->id);
            $paymentToDelete->delete();
        });

        return redirect()->route('admin.presupuestos.show', $quote)->with('success', 'Pago eliminado y saldo actualizado.');
    }
}
