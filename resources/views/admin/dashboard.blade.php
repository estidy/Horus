@extends('layouts.admin')

@section('title', 'Resumen | Horus')
@section('breadcrumb', 'Resumen')

@section('content')
<div class="page-heading"><div><p class="eyebrow">PANEL DE CONTROL</p><h1>Hola, {{ auth()->user()->name }}.</h1><p>Un vistazo a la actividad de Horus.</p></div><a class="button button-dark" href="{{ route('admin.presupuestos.create') }}">＋ Nuevo presupuesto</a></div>

<div class="stats-grid">
    <article class="stat-card"><span>CLIENTES</span><strong>{{ $stats['clients'] }}</strong><small>en tu cartera</small><span class="stat-icon">♙</span></article>
    <article class="stat-card"><span>PRESUPUESTOS</span><strong>{{ $stats['quotes'] }}</strong><small>emitidos en total</small><span class="stat-icon">▤</span></article>
    <article class="stat-card"><span>EN ESPERA</span><strong>{{ $stats['pending'] }}</strong><small>requieren seguimiento</small><span class="stat-icon">◷</span></article>
    <article class="stat-card"><span>ACEPTADOS</span><strong class="stat-currency">$ {{ number_format((float) $stats['accepted_total'], 0, ',', '.') }}</strong><small>monto acumulado</small><span class="stat-icon">↗</span></article>
</div>

<div class="dashboard-grid">
    <section class="panel chart-panel"><div class="panel-heading"><div><h2>Actividad de presupuestos</h2><p>Emisiones por mes · últimos 6 meses</p></div><span class="panel-icon">⌁</span></div>
        <div class="bar-chart">@foreach ($monthlyQuotes as $month)<div class="bar-column"><span class="bar-value">{{ $month['count'] }}</span><div class="bar-track"><span style="height: {{ max(6, ($month['count'] / $maxMonthly) * 100) }}%"></span></div><small>{{ $month['label'] }}</small></div>@endforeach</div>
    </section>
    <section class="panel status-panel"><div class="panel-heading"><div><h2>Estado de presupuestos</h2><p>Distribución actual</p></div></div>
        @foreach (\App\Models\Quote::STATUSES as $key => $label)<div class="status-row"><span class="status-dot status-{{ $key }}"></span><span>{{ $label }}</span><strong>{{ $statusCounts[$key] }}</strong></div>@endforeach
        <a class="panel-link" href="{{ route('admin.presupuestos.index') }}">Ver todos los presupuestos <span>↗</span></a>
    </section>
</div>

<section class="panel table-panel"><div class="panel-heading"><div><h2>Presupuestos recientes</h2><p>Los últimos proyectos registrados</p></div><a class="panel-link" href="{{ route('admin.presupuestos.index') }}">Ver todos <span>↗</span></a></div>
    @if ($recentQuotes->isEmpty())<div class="empty-state">Todavía no hay presupuestos. Creá el primero para comenzar.</div>@else
    <div class="table-scroll"><table><thead><tr><th>OBRA</th><th>CLIENTE</th><th>EMISIÓN</th><th>ESTADO</th><th>TOTAL</th><th></th></tr></thead><tbody>
    @foreach ($recentQuotes as $quote)<tr><td><a class="table-strong" href="{{ route('admin.presupuestos.show', $quote) }}">{{ $quote->project_name }}</a></td><td>{{ $quote->client->name }}</td><td>{{ $quote->issue_date->format('d/m/Y') }}</td><td><span class="badge badge-{{ $quote->status }}">{{ \App\Models\Quote::STATUSES[$quote->status] }}</span></td><td class="table-strong">$ {{ number_format((float) $quote->total_amount, 2, ',', '.') }}</td><td><a class="row-action" href="{{ route('admin.presupuestos.show', $quote) }}">Ver →</a></td></tr>@endforeach
    </tbody></table></div>@endif
</section>

<section class="panel table-panel inquiry-panel"><div class="panel-heading"><div><h2>Consultas recibidas</h2><p>Solicitudes desde el sitio web</p></div></div>
    @if ($recentInquiries->isEmpty())<div class="empty-state">Aún no se recibieron consultas.</div>@else
    <div class="inquiry-list">@foreach ($recentInquiries as $inquiry)<article><span class="inquiry-initial">{{ mb_substr($inquiry->name, 0, 1) }}</span><div><strong>{{ $inquiry->name }} <small>· {{ $inquiry->service }}</small></strong><p>{{ $inquiry->message }}</p><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></div><time>{{ $inquiry->created_at->format('d/m/Y') }}</time></article>@endforeach</div>@endif
</section>
@endsection
