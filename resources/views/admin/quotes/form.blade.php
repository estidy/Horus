@extends('layouts.admin')

@php
    $editing = $quote->exists;
    $savedTasks = $quote->tasks->isNotEmpty()
        ? $quote->tasks->map(fn ($task) => $task->only(['description', 'pricing_mode', 'quantity', 'unit', 'unit_price', 'labor_amount']))->all()
        : [['description' => $quote->description ?? '', 'pricing_mode' => 'fixed', 'labor_amount' => $quote->labor_amount ?? 0]];
    $taskRows = old('tasks', $savedTasks);
    $unitOptions = \App\Models\QuoteTask::UNIT_OPTIONS;
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
            <label class="field-full">Observaciones<textarea name="observations" rows="3" maxlength="5000" placeholder="Aclaraciones adicionales del presupuesto">{{ old('observations', $quote->observations) }}</textarea></label>
            <div class="field-full task-section">
                <div class="task-heading"><div><strong>Tareas y mano de obra</strong><small>Elegí un importe fijo o calculá cada tarea por cantidad y precio unitario.</small></div><button class="button button-outline" id="add-task" type="button">＋ Agregar tarea</button></div>
                <div class="task-list" id="task-list">
                    @foreach ($taskRows as $index => $task)
                    @php
                        $taskUnit = $task['unit'] ?? '';
                        $unitCode = $taskUnit === 'other'
                            ? 'other'
                            : (array_key_exists($taskUnit, $unitOptions) ? $taskUnit : array_search($taskUnit, $unitOptions, true));
                        $unitCode = $unitCode === false ? ($taskUnit === '' ? 'm2' : 'other') : $unitCode;
                        $customUnit = $unitCode === 'other' ? ($task['custom_unit'] ?? ($taskUnit === 'other' ? '' : $taskUnit)) : '';
                        $pricingMode = $task['pricing_mode'] ?? 'fixed';
                    @endphp
                    <div class="task-row" data-task-row>
                        <label class="task-description">Tarea / descripción <span>*</span><textarea data-task-field="description" name="tasks[{{ $index }}][description]" rows="2" required maxlength="2000" placeholder="Ej. Retiro y colocación de revestimientos">{{ $task['description'] ?? '' }}</textarea></label>
                        <button class="remove-task" type="button" aria-label="Quitar tarea">×</button>
                        <div class="task-calculation">
                            <label>Tipo de cálculo<select class="task-pricing-mode" data-task-field="pricing_mode" name="tasks[{{ $index }}][pricing_mode]"><option value="fixed" @selected($pricingMode === 'fixed')>Importe fijo</option><option value="quantity" @selected($pricingMode === 'quantity')>Por cantidad</option></select></label>
                            <label data-fixed-fields @if ($pricingMode !== 'fixed') hidden @endif>Mano de obra (ARS)<input class="task-labor" data-task-field="labor_amount" name="tasks[{{ $index }}][labor_amount]" type="number" min="0" max="9999999999.99" step="0.01" value="{{ $task['labor_amount'] ?? '0.00' }}" placeholder="0.00"><small>Importe fijo para esta tarea.</small></label>
                            <div class="task-quantity-fields" data-quantity-fields @if ($pricingMode !== 'quantity') hidden @endif>
                                <label>Cantidad<input class="task-quantity" data-task-field="quantity" name="tasks[{{ $index }}][quantity]" type="number" min="0.001" max="999999999.999" step="0.001" value="{{ $task['quantity'] ?? '' }}" placeholder="0.000"></label>
                                <label>Unidad<select class="task-unit" data-task-field="unit" name="tasks[{{ $index }}][unit]">@foreach ($unitOptions as $value => $label)<option value="{{ $value }}" @selected($unitCode === $value)>{{ $label }}</option>@endforeach<option value="other" @selected($unitCode === 'other')>Otra...</option></select></label>
                                <label data-custom-unit-field @if ($unitCode !== 'other') hidden @endif>Otra unidad<input class="task-custom-unit" data-task-field="custom_unit" name="tasks[{{ $index }}][custom_unit]" type="text" maxlength="30" value="{{ $customUnit }}" placeholder="Ej. m³"></label>
                                <label>Precio unitario (ARS)<input class="task-unit-price" data-task-field="unit_price" name="tasks[{{ $index }}][unit_price]" type="number" min="0" max="9999999999.99" step="0.01" value="{{ $task['unit_price'] ?? '' }}" placeholder="0.00"></label>
                                <small class="task-calculated-amount"></small>
                            </div>
                        </div>
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
            <label class="task-description">Tarea / descripción <span>*</span><textarea data-task-field="description" rows="2" required maxlength="2000" placeholder="Ej. Retiro y colocación de revestimientos"></textarea></label>
            <button class="remove-task" type="button" aria-label="Quitar tarea">×</button>
            <div class="task-calculation">
                <label>Tipo de cálculo<select class="task-pricing-mode" data-task-field="pricing_mode"><option value="fixed">Importe fijo</option><option value="quantity">Por cantidad</option></select></label>
                <label data-fixed-fields>Mano de obra (ARS)<input class="task-labor" data-task-field="labor_amount" type="number" min="0" max="9999999999.99" step="0.01" value="0.00" placeholder="0.00"><small>Importe fijo para esta tarea.</small></label>
                <div class="task-quantity-fields" data-quantity-fields hidden>
                    <label>Cantidad<input class="task-quantity" data-task-field="quantity" type="number" min="0.001" max="999999999.999" step="0.001" placeholder="0.000"></label>
                    <label>Unidad<select class="task-unit" data-task-field="unit">@foreach ($unitOptions as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach<option value="other">Otra...</option></select></label>
                    <label data-custom-unit-field hidden>Otra unidad<input class="task-custom-unit" data-task-field="custom_unit" type="text" maxlength="30" placeholder="Ej. m³"></label>
                    <label>Precio unitario (ARS)<input class="task-unit-price" data-task-field="unit_price" type="number" min="0" max="9999999999.99" step="0.01" placeholder="0.00"></label>
                    <small class="task-calculated-amount"></small>
                </div>
            </div>
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
    const configureRow = row => {
        const quantityMode = row.querySelector('.task-pricing-mode').value === 'quantity';
        const quantityFields = row.querySelector('[data-quantity-fields]');
        const unit = row.querySelector('.task-unit');
        const customUnit = row.querySelector('.task-custom-unit');
        row.querySelector('[data-fixed-fields]').hidden = quantityMode;
        quantityFields.hidden = !quantityMode;
        row.querySelector('[data-custom-unit-field]').hidden = unit.value !== 'other';
        row.querySelector('.task-quantity').required = quantityMode;
        row.querySelector('.task-unit').required = quantityMode;
        row.querySelector('.task-unit-price').required = quantityMode;
        customUnit.required = quantityMode && unit.value === 'other';
    };
    const taskLabor = row => {
        configureRow(row);
        if (row.querySelector('.task-pricing-mode').value === 'quantity') {
            const amount = (Number(row.querySelector('.task-quantity').value) || 0)
                * (Number(row.querySelector('.task-unit-price').value) || 0);
            row.querySelector('.task-calculated-amount').textContent = `Subtotal: ${currency.format(amount)}`;
            return amount;
        }
        return Number(row.querySelector('.task-labor').value) || 0;
    };
    const updateTotal = () => {
        if (!list || !materials || !total) return;
        const laborTotal = [...list.querySelectorAll('[data-task-row]')].reduce((sum, row) => sum + taskLabor(row), 0);
        total.textContent = currency.format(laborTotal + (Number(materials.value) || 0));
    };
    const updateExpiry = () => {
        if (!issue?.value || !expiry) return;
        const date = new Date(`${issue.value}T12:00:00`);
        date.setDate(date.getDate() + 21);
        expiry.value = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    };
    list?.addEventListener('input', updateTotal);
    list?.addEventListener('change', updateTotal);
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
        row.querySelectorAll('[data-task-field]').forEach(field => {
            field.name = `tasks[${index}][${field.dataset.taskField}]`;
        });
        list.append(row);
        updateTotal();
        row.querySelector('textarea').focus();
    });
    materials?.addEventListener('input', updateTotal);
    issue?.addEventListener('change', updateExpiry);
    updateTotal();
})();
</script>
@endsection
