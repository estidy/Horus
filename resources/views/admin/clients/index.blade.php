@extends('layouts.admin')

@section('title', 'Clientes | Horus')
@section('breadcrumb', 'Clientes')

@section('content')
<div class="page-heading"><div><p class="eyebrow">RELACIONES</p><h1>Clientes</h1><p>Administrá los contactos de tus proyectos.</p></div><a class="button button-dark" href="{{ route('admin.clientes.create') }}">＋ Nuevo cliente</a></div>
<section class="panel table-panel">
    <form class="filter-form" method="get" action="{{ route('admin.clientes.index') }}"><label class="search-input"><span>⌕</span><input type="search" name="search" value="{{ $search }}" placeholder="Buscar por nombre, correo o teléfono"></label><button class="button button-outline" type="submit">Buscar</button>@if ($search)<a class="clear-filter" href="{{ route('admin.clientes.index') }}">Limpiar</a>@endif</form>
    @if ($clients->isEmpty())<div class="empty-state">{{ $search ? 'No encontramos clientes con esa búsqueda.' : 'Todavía no hay clientes registrados.' }}</div>@else
    <div class="table-scroll"><table><thead><tr><th>CLIENTE</th><th>CONTACTO</th><th>DIRECCIÓN</th><th>PRESUPUESTOS</th><th></th></tr></thead><tbody>
    @foreach ($clients as $client)<tr><td><span class="client-cell"><span class="client-initial">{{ mb_substr($client->name, 0, 1) }}</span><span class="table-strong">{{ $client->name }}</span></span></td><td>{{ $client->email ?: '—' }}<small class="cell-sub">{{ $client->phone }}</small></td><td>{{ $client->address ?: '—' }}</td><td>{{ $client->quotes_count }}</td><td class="row-actions"><a class="row-action" href="{{ route('admin.clientes.edit', $client) }}">Editar</a><form method="post" action="{{ route('admin.clientes.destroy', $client) }}" onsubmit="return confirm('¿Eliminar este cliente?')">@csrf @method('DELETE')<button class="row-action row-danger" type="submit">Eliminar</button></form></td></tr>@endforeach
    </tbody></table></div><div class="pagination-wrap">{{ $clients->links() }}</div>@endif
</section>
@endsection
