<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Quote;
use App\Models\QuoteTask;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status');
        $quotes = Quote::query()
            ->with('client')
            ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('project_name', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($clientQuery) => $clientQuery->where('name', 'like', "%{$search}%"));
            }))
            ->when(array_key_exists($status, Quote::STATUSES), fn ($query) => $query->where('status', $status))
            ->latest('issue_date')
            ->paginate(10)
            ->withQueryString();

        return view('admin.quotes.index', compact('quotes', 'search', 'status'));
    }

    public function create(): View
    {
        return view('admin.quotes.form', [
            'quote' => new Quote([
                'issue_date' => now()->toDateString(),
                'status' => 'pending',
                'materials_amount' => 0,
            ]),
            'clients' => Client::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['expiry_date'] = Carbon::parse($data['issue_date'])->addDays(21)->toDateString();
        $tasks = $data['tasks'];
        unset($data['tasks']);

        $quote = DB::transaction(function () use ($data, $tasks): Quote {
            $quote = Quote::create($data);
            $quote->tasks()->createMany($tasks);

            return $quote;
        });

        return redirect()->route('admin.presupuestos.show', $quote)->with('success', 'Presupuesto creado correctamente.');
    }

    public function show(Quote $quote): View
    {
        $quote->load(['client', 'tasks', 'payments']);

        return view('admin.quotes.show', compact('quote'));
    }

    public function edit(Quote $quote): View
    {
        $quote->load('tasks');

        return view('admin.quotes.form', [
            'quote' => $quote,
            'clients' => Client::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Quote $quote): RedirectResponse
    {
        $data = $this->validated($request);
        $data['expiry_date'] = Carbon::parse($data['issue_date'])->addDays(21)->toDateString();
        $tasks = $data['tasks'];
        unset($data['tasks']);

        DB::transaction(function () use ($quote, $data, $tasks): void {
            $lockedQuote = Quote::query()->lockForUpdate()->findOrFail($quote->id);
            $paidAmount = (float) $lockedQuote->payments()->sum('amount');
            $newTotal = round((float) $data['labor_amount'] + (float) $data['materials_amount'], 2);

            if ($paidAmount > 0 && $data['status'] !== 'accepted') {
                throw ValidationException::withMessages([
                    'status' => 'No se puede cambiar el estado mientras haya pagos registrados.',
                ]);
            }

            if ($paidAmount > $newTotal) {
                throw ValidationException::withMessages([
                    'materials_amount' => 'El nuevo total no puede ser menor que los pagos ya registrados.',
                ]);
            }

            $lockedQuote->update($data);
            $lockedQuote->tasks()->delete();
            $lockedQuote->tasks()->createMany($tasks);
        });

        return redirect()->route('admin.presupuestos.show', $quote)->with('success', 'Presupuesto actualizado correctamente.');
    }

    public function destroy(Quote $quote): RedirectResponse
    {
        $quote->delete();

        return redirect()->route('admin.presupuestos.index')->with('success', 'Presupuesto eliminado correctamente.');
    }

    public function pdf(Quote $quote): Response
    {
        $quote->load(['client', 'tasks']);
        $pdf = Pdf::loadView('admin.quotes.pdf', compact('quote'))->setPaper('a4');

        return $pdf->download('presupuesto-horus-' . $quote->id . '.pdf');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'project_name' => ['required', 'string', 'max:180'],
            'issue_date' => ['required', 'date'],
            'observations' => ['nullable', 'string', 'max:5000'],
            'tasks' => ['required', 'array', 'min:1', 'max:50'],
            'tasks.*.description' => ['required', 'string', 'max:2000'],
            'tasks.*.pricing_mode' => ['nullable', 'in:fixed,quantity'],
            'tasks.*.labor_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'tasks.*.quantity' => ['nullable', 'numeric', 'min:0.001', 'max:999999999.999'],
            'tasks.*.unit' => ['nullable', 'in:m2,linear_meter,unit,other'],
            'tasks.*.custom_unit' => ['nullable', 'string', 'max:30'],
            'tasks.*.unit_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'materials_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'status' => ['required', 'in:' . implode(',', array_keys(Quote::STATUSES))],
        ]);

        $tasks = $this->normalizeTasks($data['tasks']);
        $laborAmount = round(array_sum(array_column($tasks, 'labor_amount')), 2);
        $materialsAmount = round((float) ($data['materials_amount'] ?? 0), 2);

        if ($laborAmount + $materialsAmount > 9999999999.99) {
            throw ValidationException::withMessages([
                'tasks' => 'El total del presupuesto supera el importe máximo permitido.',
            ]);
        }

        $data['description'] = implode("\n", array_column($tasks, 'description'));
        $data['labor_amount'] = $laborAmount;
        $data['materials_amount'] = $materialsAmount;
        $data['tasks'] = $tasks;

        return $data;
    }

    private function normalizeTasks(array $tasks): array
    {
        $normalized = [];

        foreach ($tasks as $index => $task) {
            $mode = $task['pricing_mode'] ?? 'fixed';
            $normalizedTask = [
                'description' => trim($task['description']),
                'pricing_mode' => $mode,
                'quantity' => null,
                'unit' => null,
                'unit_price' => null,
                'labor_amount' => 0,
            ];

            if ($mode === 'fixed') {
                $normalizedTask['labor_amount'] = round((float) ($task['labor_amount'] ?? 0), 2);
                $normalized[] = $normalizedTask;
                continue;
            }

            if (!isset($task['quantity']) || $task['quantity'] === '') {
                throw ValidationException::withMessages([
                    "tasks.{$index}.quantity" => 'Ingresá la cantidad para calcular la mano de obra.',
                ]);
            }

            if (!isset($task['unit_price']) || $task['unit_price'] === '') {
                throw ValidationException::withMessages([
                    "tasks.{$index}.unit_price" => 'Ingresá el precio por unidad.',
                ]);
            }

            $unitCode = $task['unit'] ?? '';
            if ($unitCode === 'other') {
                $unit = trim($task['custom_unit'] ?? '');
            } else {
                $unit = QuoteTask::UNIT_OPTIONS[$unitCode] ?? '';
            }

            if ($unit === '') {
                throw ValidationException::withMessages([
                    "tasks.{$index}.unit" => 'Seleccioná o ingresá una unidad de medida.',
                ]);
            }

            $quantity = round((float) $task['quantity'], 3);
            $unitPrice = round((float) $task['unit_price'], 2);
            $laborAmount = round($quantity * $unitPrice, 2);

            if ($laborAmount > 9999999999.99) {
                throw ValidationException::withMessages([
                    "tasks.{$index}.unit_price" => 'El importe calculado supera el máximo permitido.',
                ]);
            }

            $normalizedTask['quantity'] = $quantity;
            $normalizedTask['unit'] = $unit;
            $normalizedTask['unit_price'] = $unitPrice;
            $normalizedTask['labor_amount'] = $laborAmount;
            $normalized[] = $normalizedTask;
        }

        return $normalized;
    }
}
