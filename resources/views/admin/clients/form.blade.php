@extends('layouts.admin')

@php($editing = $client->exists)
@section('title', ($editing ? 'Editar cliente' : 'Nuevo cliente') . ' | Horus')
@section('breadcrumb', 'Clientes / ' . ($editing ? 'Editar' : 'Nuevo'))

@section('content')
<div class="page-heading"><div><a class="back-link" href="{{ route('admin.clientes.index') }}">← Volver a clientes</a><h1>{{ $editing ? 'Editar cliente' : 'Nuevo cliente' }}</h1><p>Los datos del cliente se usarán en sus presupuestos.</p></div></div>
<section class="panel form-panel">
    <form method="post" action="{{ $editing ? route('admin.clientes.update', $client) : route('admin.clientes.store') }}">
        @csrf @if ($editing) @method('PUT') @endif
        <div class="form-grid"><label>Nombre y apellido <span>*</span><input name="name" value="{{ old('name', $client->name) }}" required maxlength="120" autofocus></label><label>Correo electrónico<input name="email" type="email" value="{{ old('email', $client->email) }}" maxlength="180"></label><label>Teléfono<input name="phone" value="{{ old('phone', $client->phone) }}" maxlength="40"></label><label>Dirección<input name="address" value="{{ old('address', $client->address) }}" maxlength="255"></label><label class="field-full">Notas internas<textarea name="notes" rows="4" maxlength="2000">{{ old('notes', $client->notes) }}</textarea></label></div>
        <div class="form-actions"><a class="button button-outline" href="{{ route('admin.clientes.index') }}">Cancelar</a><button class="button button-dark" type="submit">{{ $editing ? 'Guardar cambios' : 'Crear cliente' }}</button></div>
    </form>
</section>
@endsection
