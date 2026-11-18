@extends('layouts.admin')

@section('title', 'Presupuesto ' . $quote->project_name . ' | Horus')
@section('breadcrumb', 'Presupuestos / Detalle')

@section('content')
@php
    $paidAmount = (float) $quote->payments->sum('amount');
    $remainingAmount = max(0, round((float) $quote->total_amount - $paidAmount, 2));
@endphp
<div class="page-heading"><div><a class="back-link" href="{{ route('admin.presupuestos.index') }}">← Volver a presupuestos</a><p class="eyebrow">PRESUPUESTO #{{ str_pad((string) $quote->id, 5, '0', STR_PAD_LEFT) }}</p><h1>{{ $quote->project_name }}</h1><p>Creado el {{ $quote->issue_date->format('d/m/Y') }}</p></div><div class="heading-actions"><a class="button button-outline" href="{{ route('admin.presupuestos.edit', $quote) }}">Editar</a><a class="button button-dark" href="{{ route('admin.presupuestos.pdf', $quote) }}">↓ Descargar PDF</a></div></div>
<section class="panel quote-detail">
    <div class="quote-detail-top"><div><span class="detail-label">CLIENTE</span><h2>{{ $quote->client->name }}</h2><p>{{ $quote->client->email ?: 'Sin correo registrado' }} @if ($quote->client->phone) · {{ $quote->client->phone }} @endif</p><p>{{ $quote->client->address }}</p></div><span class="badge badge-{{ $quote->status }}">{{ \App\Models\Quote::STATUSES[$quote->status] }}</span></div>
    <div class="quote-dates"><div><span class="detail-label">FECHA DE EMISIÓN</span><strong>{{ $quote->issue_date->format('d/m/Y') }}</strong></div><div><span class="detail-label">VÁLIDO HASTA</span><strong>{{ $quote->expiry_date->format('d/m/Y') }}</strong><small>21 días desde la emisión</small></div></div>
    <div class="quote-description"><span class="detail-label">TAREAS INCLUIDAS</span>
        @if ($quote->tasks->isNotEmpty())
            <div class="quote-task-list">@foreach ($quote->tasks as $task)<div><p>{{ $task->description }}</p><strong>$ {{ number_format((float) $task->labor_amount, 2, ',', '.') }}</strong></div>@endforeach</div>
        @else
            <p>{{ $quote->description }}</p>
        @endif
    </div>
    <div class="quote-costs"><div><span>Mano de obra</span><strong>$ {{ number_format((float) $quote->labor_amount, 2, ',', '.') }}</strong></div><div><span>Materiales</span><strong>$ {{ number_format((float) $quote->materials_amount, 2, ',', '.') }}</strong></div><div class="quote-total"><span>TOTAL DEL PRESUPUESTO</span><strong>$ {{ number_format((float) $quote->total_amount, 2, ',', '.') }} <small>ARS</small></strong></div></div>
    @if ($quote->status === 'accepted')
        <section class="payment-section">
            <div class="payment-heading"><div><span class="detail-label">SEGUIMIENTO DE PAGOS</span><h2>Pagos del presupuesto</h2></div><div class="payment-heading-actions"><a class="button button-outline" href="{{ route('admin.presupuestos.pagos.pdf', $quote) }}">↓ Descargar reporte</a><span class="badge {{ $remainingAmount > 0 ? 'badge-pending' : 'badge-accepted' }}">{{ $remainingAmount > 0 ? 'Saldo pendiente' : 'Pagado' }}</span></div></div>
            <div class="payment-summary">
                <div><span>Total del presupuesto</span><strong>$ {{ number_format((float) $quote->total_amount, 2, ',', '.') }}</strong></div>
                <div><span>Total abonado</span><strong>$ {{ number_format($paidAmount, 2, ',', '.') }}</strong></div>
                <div class="payment-balance"><span>Saldo pendiente</span><strong>$ {{ number_format($remainingAmount, 2, ',', '.') }}</strong></div>
            </div>
            @if ($quote->payments->isNotEmpty())
                <div class="table-scroll payment-history"><table><thead><tr><th>FECHA</th><th>MEDIO</th><th>REFERENCIA</th><th>NOTAS</th><th>IMPORTE</th><th></th></tr></thead><tbody>
                    @foreach ($quote->payments->sortByDesc('payment_date') as $payment)
                        <tr><td>{{ $payment->payment_date->format('d/m/Y') }}</td><td>{{ \App\Models\Payment::METHODS[$payment->method] }}</td><td>{{ $payment->reference ?: '—' }}</td><td>{{ $payment->notes ?: '—' }}</td><td class="table-strong">$ {{ number_format((float) $payment->amount, 2, ',', '.') }}</td><td><form method="post" action="{{ route('admin.presupuestos.pagos.destroy', [$quote, $payment]) }}" onsubmit="return confirm('¿Eliminar este pago? El saldo pendiente se actualizará.')">@csrf @method('DELETE')<button class="row-action row-danger" type="submit">Eliminar</button></form></td></tr>
                    @endforeach
                </tbody></table></div>
            @else
                <p class="payment-empty">Todavía no hay pagos registrados.</p>
            @endif
            @if ($remainingAmount > 0)
                <form class="payment-form" method="post" action="{{ route('admin.presupuestos.pagos.store', $quote) }}">
                    @csrf
                    <h3>Registrar un pago</h3>
                    <div class="payment-fields">
                        <label>Importe (ARS) <span>*</span><input name="amount" type="number" min="0.01" max="{{ number_format($remainingAmount, 2, '.', '') }}" step="0.01" value="{{ old('amount') }}" required></label>
                        <label>Fecha de pago <span>*</span><input name="payment_date" type="date" max="{{ now()->toDateString() }}" value="{{ old('payment_date', now()->toDateString()) }}" required></label>
                        <label>Medio de pago <span>*</span><select name="method" required>@foreach (\App\Models\Payment::METHODS as $key => $label)<option value="{{ $key }}" @selected(old('method') === $key)>{{ $label }}</option>@endforeach</select></label>
                        <label>Referencia <span>(opcional)</span><input name="reference" value="{{ old('reference') }}" maxlength="120" placeholder="N.º de operación"></label>
                        <label class="payment-notes">Notas <span>(opcional)</span><input name="notes" value="{{ old('notes') }}" maxlength="1000" placeholder="Observaciones del pago"></label>
                    </div>
                    <button class="button button-dark" type="submit">＋ Registrar pago</button>
                </form>
            @else
                <p class="payment-complete">El presupuesto está totalmente abonado.</p>
            @endif
        </section>
    @endif
    <div class="quote-delete"><span>Esta acción no se puede deshacer.</span><form method="post" action="{{ route('admin.presupuestos.destroy', $quote) }}" onsubmit="return confirm('¿Eliminar este presupuesto?')">@csrf @method('DELETE')<button class="row-action row-danger" type="submit">Eliminar presupuesto</button></form></div>
</section>
@endsection
