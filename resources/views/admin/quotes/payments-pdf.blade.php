<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 42px 48px; }
        body { color: #242621; font-family: DejaVu Sans, sans-serif; font-size: 13px; line-height: 1.6; }
        .top { border-bottom: 2px solid #c8963e; padding-bottom: 18px; }
        .brand { color: #20231f; font-size: 22px; font-weight: bold; letter-spacing: 3px; }
        .brand span { color: #c8963e; }
        .muted { color: #797b76; }
        .right { float: right; text-align: right; }
        h1 { font-size: 24px; margin: 25px 0 5px; }
        h2 { font-size: 16px; margin: 25px 0 8px; }
        .meta { color: #6b6d67; font-size: 11px; letter-spacing: 1px; margin-top: 20px; }
        .box { background: #f5f4f0; margin-top: 17px; padding: 16px; }
        .box strong { display: block; font-size: 16px; margin: 5px 0; }
        table { border-collapse: collapse; margin-top: 12px; width: 100%; }
        th { border-bottom: 1px solid #d9d8d1; color: #696b64; font-size: 10px; letter-spacing: .5px; text-align: left; }
        th, td { padding: 11px 8px; }
        td { border-bottom: 1px solid #e6e5e0; }
        td:last-child, th:last-child { text-align: right; white-space: nowrap; }
        .summary { margin-top: 25px; }
        .summary td { border: 0; padding: 7px 8px; }
        .summary td:last-child { font-weight: bold; }
        .summary .balance td { border-top: 1px solid #d9d8d1; color: #20231f; font-size: 16px; font-weight: bold; padding-top: 14px; }
        .empty { color: #797b76; font-style: italic; padding: 20px 8px; }
        .footer { border-top: 1px solid #e6e5e0; color: #797b76; font-size: 10px; margin-top: 35px; padding-top: 12px; }
    </style>
</head>
<body>
    <div class="top"><div class="right muted">CONSTRUCCIÓN · REFORMAS<br>MANTENIMIENTO PROFESIONAL</div><div class="brand">HORUS<span>.</span></div><div class="muted">La Plata, Buenos Aires · Argentina</div></div>
    <p class="meta">REPORTE DE PAGOS · PRESUPUESTO #{{ str_pad((string) $quote->id, 5, '0', STR_PAD_LEFT) }}</p>
    <h1>{{ $quote->project_name }}</h1>
    <div class="muted">Fecha de emisión del presupuesto: {{ $quote->issue_date->format('d/m/Y') }}</div>
    <div class="box"><span class="meta">CLIENTE</span><strong>{{ $quote->client->name }}</strong>{{ $quote->client->email }} @if ($quote->client->phone) · {{ $quote->client->phone }} @endif<br>{{ $quote->client->address }}</div>
    <h2>Detalle de pagos registrados</h2>
    @if ($quote->payments->isEmpty())
        <div class="empty">Todavía no hay pagos registrados para este presupuesto.</div>
    @else
        <table><thead><tr><th>FECHA</th><th>MEDIO DE PAGO</th><th>REFERENCIA</th><th>NOTAS</th><th>IMPORTE</th></tr></thead><tbody>
            @foreach ($quote->payments as $payment)
                <tr><td>{{ $payment->payment_date->format('d/m/Y') }}</td><td>{{ \App\Models\Payment::METHODS[$payment->method] }}</td><td>{{ $payment->reference ?: '—' }}</td><td>{{ $payment->notes ?: '—' }}</td><td>$ {{ number_format((float) $payment->amount, 2, ',', '.') }} ARS</td></tr>
            @endforeach
        </tbody></table>
    @endif
    <table class="summary"><tbody>
        <tr><td>Total del presupuesto</td><td>$ {{ number_format((float) $quote->total_amount, 2, ',', '.') }} ARS</td></tr>
        <tr><td>Total abonado</td><td>$ {{ number_format($paidAmount, 2, ',', '.') }} ARS</td></tr>
        <tr class="balance"><td>Saldo pendiente</td><td>$ {{ number_format($remainingAmount, 2, ',', '.') }} ARS</td></tr>
    </tbody></table>
    <div class="footer">Reporte generado el {{ now()->format('d/m/Y H:i') }}.<br>Horus · Calle 4 entre 614 y 615 · La Plata · CP 1900 · Argentina</div>
</body>
</html>
