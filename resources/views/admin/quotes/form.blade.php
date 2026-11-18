@extends('layouts.admin')

@php
    $editing = $quote->exists;
    $savedTasks = $quote->tasks->isNotEmpty()
        ? $quote->tasks->map(fn ($task) => $task->only(['description', 'labor_amount']))->all()
        : [['description' => $quote->description ?? '', 'labor_amount' => $quote->labor_amount ?? 0]];
    $taskRows = old('tasks', $savedTasks);
@endphp
@section('title', ($editing ? 'Editar presupuesto' : 'Nuevo presupuesto') . ' | Horus')
@section('breadcrumb', 'Presupuestos / ' . ($editing ? 'Editar' : 'Nuevo'))

@section('content')
<div class="page-heading"><div><a class="back-link" href="{{ route('admin.presupuestos.index') }}">← Volver a presupuestos</a><h1>{{ $editing ? 'Editar presupuesto' : 'Nuevo presupuesto' }}</h1><p>El vencimiento se calcula automáticamente a 21 días de la emisión.</p></div></div>
<section class="panel form-panel">
    @if ($clients->isEmpty())<div class="empty-state">Primero necesitás <a href="{{ route('admin.clientes.create') }}">crear un cliente</a> para emitir un presupuesto.</div>@else
    <form method="post" action="{{ $editing ? route('admin.presupuestos.update', $quote) : route('admin.presupuestos.store') }}" id="quote-form">
        @csrf @if ($editing) @method('PUT') @endif
        <div class="form-grid">
            <label>Cliente <span>*</span><select name="client_id" required><option value="">Seleccionar cliente</option>@foreach ($clients as $client)<option value="{{ $client->id }}" @selected((string) old('client_id', $quote->client_id) === (string) $client->id)>{{ $client->name }}</option>@endforeach</select></label>
            <label>Nombre de la obra <span>*</span><input name="project_name" value="{{ old('project_name', $quote->project_name) }}" required maxlength="180" placeholder="Ej. Reforma de cocina"></label>
            <label>Fecha de emisión <span>*</span><input id="issue-date" name="issue_date" type="date" value="{{ old('issue_date', $quote->issue_date?->format('Y-m-d')) }}" required></label>
            <label>Vencimiento del presupuesto<input id="expiry-date" type="date" value="{{ old('issue_date', $quote->issue_date?->format('Y-m-d')) ? \Carbon\Carbon::parse(old('issue_date', $quote->issue_date?->format('Y-m-d')))->addDays(21)->format('Y-m-d') : '' }}" readonly><small>21 días después de la fecha de emisión</small></label>
            <div class="field-full task-section">
                <div class="task-heading"><div><strong>Tareas y mano de obra</strong><small>Agregá una línea por tarea o conjunto de tareas. El precio es opcional; se suman automáticamente los importes ingresados.</small></div><button class="button button-outline" id="add-task" type="button">＋ Agregar tarea</button></div>
                <div class="task-list" id="task-list">
                    @foreach ($taskRows as $index => $task)
                    <div class="task-row" data-task-row>
                        <label>Tarea / descripción <span>*</span><textarea name="tasks[{{ $index }}][description]" rows="2" required maxlength="2000" placeholder="Ej. Retiro y colocación de revestimientos">{{ $task['description'] ?? '' }}</textarea></label>
                        <label>Mano de obra (ARS)<input class="task-labor" name="tasks[{{ $index }}][labor_amount]" type="number" min="0" max="9999999999.99" step="0.01" value="{{ $task['labor_amount'] ?? '0.00' }}" placeholder="0.00"><small>Dejá 0 si no aplica.</small></label>
                        <button class="remove-task" type="button" aria-label="Quitar tarea">×</button>
                    </div>
                    @endforeach
                </div>
            </div>
            <label>Materiales (ARS)<input name="materials_amount" type="number" min="0" max="9999999999.99" step="0.01" value="{{ old('materials_amount', $quote->materials_amount ?? '0.00') }}"><small>Dejá 0 si no aplica.</small></label>
            <label>Estado <span>*</span><select name="status" required>@foreach (\App\Models\Quote::STATUSES as $key => $label)<option value="{{ $key }}" @selected(old('status', $quote->status ?? 'pending') === $key)>{{ $label }}</option>@endforeach</select></label>
        </div>
        <div class="total-preview"><span>Total del presupuesto <small>Mano de obra de las tareas + materiales</small></span><strong id="total-preview">$ 0,00</strong></div>
        <div class="form-actions"><a class="button button-outline" href="{{ route('admin.presupuestos.index') }}">Cancelar</a><button class="button button-dark" type="submit">{{ $editing ? 'Guardar cambios' : 'Crear presupuesto' }}</button></div>
    </form>
    <template id="task-template">
        <div class="task-row" data-task-row>
            <label>Tarea / descripción <span>*</span><textarea rows="2" required maxlength="2000" placeholder="Ej. Retiro y colocación de revestimientos"></textarea></label>
            <label>Mano de obra (ARS)<input class="task-labor" type="number" min="0" max="9999999999.99" step="0.01" value="0.00" placeholder="0.00"><small>Dejá 0 si no aplica.</small></label>
            <button class="remove-task" type="button" aria-label="Quitar tarea">×</button>
        </div>
    </template>
    @endif
</section>
<script>
(() => {
    const list = document.getElementById('task-list');
    const template = document.getElementById('task-template');
    const materials = document.querySelector('[name="materials_amount"]');
    const total = document.getElementById('total-preview');
    const issue = document.getElementById('issue-date');
    const expiry = document.getElementById('expiry-date');
    let nextTaskIndex = list?.querySelectorAll('[data-task-row]').length || 0;
    const currency = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' });
    const updateTotal = () => {
        if (!list || !materials || !total) return;
        const laborTotal = [...list.querySelectorAll('.task-labor')].reduce((sum, input) => sum + (Number(input.value) || 0), 0);
        total.textContent = currency.format(laborTotal + (Number(materials.value) || 0));
    };
    const updateExpiry = () => {
        if (!issue?.value || !expiry) return;
        const date = new Date(`${issue.value}T12:00:00`);
        date.setDate(date.getDate() + 21);
        expiry.value = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    };
    list?.addEventListener('input', updateTotal);
    list?.addEventListener('click', event => {
        const button = event.target.closest('.remove-task');
        if (button) {
            button.closest('[data-task-row]').remove();
            updateTotal();
        }
    });
    document.getElementById('add-task')?.addEventListener('click', () => {
        const row = template.content.firstElementChild.cloneNode(true);
        const index = nextTaskIndex++;
        row.querySelector('textarea').name = `tasks[${index}][description]`;
        row.querySelector('.task-labor').name = `tasks[${index}][labor_amount]`;
        list.append(row);
        row.querySelector('textarea').focus();
    });
    materials?.addEventListener('input', updateTotal);
    issue?.addEventListener('change', updateExpiry);
    updateTotal();
})();
</script>
@endsection
