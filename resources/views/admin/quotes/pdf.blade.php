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
        h1 { font-size: 25px; margin: 25px 0 5px; }
        .meta { color: #6b6d67; font-size: 11px; letter-spacing: 1px; margin-top: 20px; }
        .box { background: #f5f4f0; margin-top: 18px; padding: 16px; }
        .box strong { display: block; font-size: 16px; margin: 5px 0; }
        .observations p { margin-bottom: 0; white-space: pre-line; }
        .description { min-height: 90px; }
        table { border-collapse: collapse; margin-top: 25px; width: 100%; }
        th { border-bottom: 1px solid #d9d8d1; color: #696b64; font-size: 10px; letter-spacing: .5px; text-align: left; }
        th, td { padding: 12px 8px; }
        td { border-bottom: 1px solid #e6e5e0; }
        td:last-child, th:last-child { text-align: right; white-space: nowrap; }
        .task-text { white-space: pre-line; }
        .total td { border: 0; color: #20231f; font-size: 18px; font-weight: bold; padding-top: 18px; }
        .footer { border-top: 1px solid #e6e5e0; color: #797b76; font-size: 11px; margin-top: 45px; padding-top: 12px; }
    </style>
</head>
<body>
    <div class="top"><div class="right muted">CONSTRUCCIÓN · REFORMAS<br>MANTENIMIENTO PROFESIONAL</div><div class="brand">HORUS<span>.</span></div><div class="muted">La Plata, Buenos Aires · Argentina</div></div>
    <p class="meta">PRESUPUESTO #{{ str_pad((string) $quote->id, 5, '0', STR_PAD_LEFT) }}</p>
    <h1>{{ $quote->project_name }}</h1>
    <div class="muted">Emitido el {{ $quote->issue_date->format('d/m/Y') }} · Válido hasta el {{ $quote->expiry_date->format('d/m/Y') }}</div>
    <div class="box"><span class="meta">CLIENTE</span><strong>{{ $quote->client->name }}</strong>{{ $quote->client->email }} @if ($quote->client->phone) · {{ $quote->client->phone }} @endif<br>{{ $quote->client->address }}</div>
    @if ($quote->observations)
        <div class="box observations"><span class="meta">OBSERVACIONES</span><p>{{ $quote->observations }}</p></div>
    @endif
    <div class="box description"><span class="meta">TAREAS INCLUIDAS</span>
        <table><thead><tr><th>TAREA / DESCRIPCIÓN</th><th>MANO DE OBRA</th></tr></thead><tbody>
            @forelse ($quote->tasks as $task)
                <tr><td class="task-text">{{ $task->description }} @if ($task->pricing_mode === 'quantity')<br><span class="muted">{{ number_format((float) $task->quantity, 3, ',', '.') }} {{ $task->unit }} × $ {{ number_format((float) $task->unit_price, 2, ',', '.') }} por {{ $task->unit }}</span>@endif</td><td>$ {{ number_format((float) $task->labor_amount, 2, ',', '.') }} ARS</td></tr>
            @empty
                <tr><td class="task-text">{{ $quote->description }}</td><td>$ {{ number_format((float) $quote->labor_amount, 2, ',', '.') }} ARS</td></tr>
            @endforelse
        </tbody></table>
    </div>
    <table><tbody><tr><td>Mano de obra (suma de las tareas)</td><td>$ {{ number_format((float) $quote->labor_amount, 2, ',', '.') }} ARS</td></tr><tr><td>Materiales</td><td>$ {{ number_format((float) $quote->materials_amount, 2, ',', '.') }} ARS</td></tr><tr class="total"><td>TOTAL</td><td>$ {{ number_format((float) $quote->total_amount, 2, ',', '.') }} ARS</td></tr></tbody></table>
    <div class="footer">Este presupuesto tiene una validez de 21 días desde su fecha de emisión.<br>Horus · Calle 4 entre 614 y 615 · La Plata · CP 1900 · Argentina</div>
</body>
</html>
