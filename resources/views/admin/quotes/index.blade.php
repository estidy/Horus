@extends('layouts.admin')

@section('title', 'Presupuestos | Horus')
@section('breadcrumb', 'Presupuestos')

@section('content')
<div class="page-heading"><div><p class="eyebrow">OBRAS Y COTIZACIONES</p><h1>Presupuestos</h1><p>Creá propuestas, actualizá su estado y descargá PDF.</p></div><a class="button button-dark" href="{{ route('admin.presupuestos.create') }}">＋ Nuevo presupuesto</a></div>
<section class="panel table-panel">
    <form class="filter-form" method="get" action="{{ route('admin.presupuestos.index') }}"><label class="search-input"><span>⌕</span><input type="search" name="search" value="{{ $search }}" placeholder="Buscar por obra o cliente"></label><select name="status" aria-label="Filtrar por estado"><option value="">Todos los estados</option>@foreach (\App\Models\Quote::STATUSES as $key => $label)<option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>@endforeach</select><button class="button button-outline" type="submit">Filtrar</button>@if ($search || $status)<a class="clear-filter" href="{{ route('admin.presupuestos.index') }}">Limpiar</a>@endif</form>
    @if ($quotes->isEmpty())<div class="empty-state">{{ $search || $status ? 'No hay presupuestos que coincidan con los filtros.' : 'Todavía no hay presupuestos registrados.' }}</div>@else
    <div class="table-scroll"><table><thead><tr><th>OBRA</th><th>CLIENTE</th><th>EMISIÓN</th><th>VENCIMIENTO</th><th>ESTADO</th><th>TOTAL</th><th>ACCIONES</th></tr></thead><tbody>
    @foreach ($quotes as $quote)<tr><td><a class="table-strong" href="{{ route('admin.presupuestos.show', $quote) }}">{{ $quote->project_name }}</a></td><td>{{ $quote->client->name }}</td><td>{{ $quote->issue_date->format('d/m/Y') }}</td><td>{{ $quote->expiry_date->format('d/m/Y') }}</td><td><span class="badge badge-{{ $quote->status }}">{{ \App\Models\Quote::STATUSES[$quote->status] }}</span></td><td class="table-strong">$ {{ number_format((float) $quote->total_amount, 2, ',', '.') }}</td><td class="row-actions"><a class="row-action" href="{{ route('admin.presupuestos.show', $quote) }}">Ver</a><a class="row-action" href="{{ route('admin.presupuestos.edit', $quote) }}">Editar</a></td></tr>@endforeach
    </tbody></table></div><div class="pagination-wrap">{{ $quotes->links() }}</div>@endif
</section>
@endsection
