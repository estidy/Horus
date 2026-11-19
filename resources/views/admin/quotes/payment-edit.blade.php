@extends('layouts.admin')

@section('title', 'Modificar pago | Horus')
@section('breadcrumb', 'Presupuestos / Modificar pago')

@section('content')
<div class="page-heading"><div><a class="back-link" href="{{ route('admin.presupuestos.show', $quote) }}">← Volver al presupuesto</a><h1>Modificar pago</h1><p>{{ $quote->project_name }}</p></div></div>
<section class="panel form-panel">
    <form method="post" action="{{ route('admin.presupuestos.pagos.update', [$quote, $payment]) }}">
        @csrf
        @method('PUT')
        <div class="payment-fields">
            <label>Importe (ARS) <span>*</span><input name="amount" type="number" min="0.01" max="{{ number_format($remainingAmount + 100000, 2, '.', '') }}" step="0.01" value="{{ old('amount', $payment->amount) }}" required><small>Saldo disponible antes de este pago: $ {{ number_format($remainingAmount, 2, ',', '.') }}. Hasta $ 100.000,00 de exceso se acepta, pero no se registra por encima del saldo.</small></label>
            <label>Fecha de pago <span>*</span><input name="payment_date" type="date" max="{{ now()->toDateString() }}" value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}" required></label>
            <label>Medio de pago <span>*</span><select name="method" required>@foreach (\App\Models\Payment::METHODS as $key => $label)<option value="{{ $key }}" @selected(old('method', $payment->method) === $key)>{{ $label }}</option>@endforeach</select></label>
            <label>Referencia <span>(opcional)</span><input name="reference" value="{{ old('reference', $payment->reference) }}" maxlength="120" placeholder="N.º de operación"></label>
            <label class="payment-notes">Notas <span>(opcional)</span><input name="notes" value="{{ old('notes', $payment->notes) }}" maxlength="1000" placeholder="Observaciones del pago"></label>
        </div>
        <div class="form-actions"><a class="button button-outline" href="{{ route('admin.presupuestos.show', $quote) }}">Cancelar</a><button class="button button-dark" type="submit">Guardar cambios</button></div>
    </form>
</section>
@endsection
