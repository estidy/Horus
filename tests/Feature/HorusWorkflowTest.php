<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HorusWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_displays_company_information_and_contact_form(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Construimos espacios.')
            ->assertSee('Calle 4 entre 614 y 615');
    }

    public function test_public_visitor_can_send_a_project_inquiry(): void
    {
        $this->post(route('inquiries.store'), [
            'name' => 'Ana Pérez',
            'email' => 'ana@example.com',
            'phone' => '2215550101',
            'service' => 'Reforma',
            'message' => 'Necesito renovar un baño.',
        ])->assertRedirect(route('home') . '#consulta');

        $this->assertDatabaseHas('inquiries', ['email' => 'ana@example.com', 'service' => 'Reforma']);
        $this->assertSame(1, Inquiry::count());
    }

    public function test_admin_routes_require_authentication(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.clientes.index'))->assertRedirect(route('login'));
    }

    public function test_login_and_admin_forms_render(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Bienvenido.');

        $this->actingAs(User::factory()->create())
            ->get(route('admin.clientes.create'))
            ->assertOk()
            ->assertSee('Nuevo cliente');

        $this->get(route('admin.presupuestos.create'))
            ->assertOk()
            ->assertSee('Nuevo presupuesto');
    }

    public function test_admin_dashboard_displays_statistics_and_recent_activity(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Actividad de presupuestos')
            ->assertSee('Consultas recibidas');
    }

    public function test_admin_can_create_update_and_delete_a_client(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.clientes.store'), [
            'name' => 'Lucas Fernández',
            'email' => 'lucas@example.com',
            'phone' => '2211234567',
            'address' => 'La Plata',
            'notes' => 'Cliente nuevo',
        ])->assertRedirect(route('admin.clientes.index'));

        $client = Client::firstOrFail();
        $this->put(route('admin.clientes.update', $client), [
            'name' => 'Lucas Fernández',
            'email' => 'lucas@example.com',
            'phone' => '2217654321',
            'address' => 'La Plata',
            'notes' => '',
        ])->assertRedirect(route('admin.clientes.index'));
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'phone' => '2217654321']);

        $this->delete(route('admin.clientes.destroy', $client))
            ->assertRedirect(route('admin.clientes.index'));
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_admin_can_create_and_download_a_valid_quote(): void
    {
        $admin = User::factory()->create();
        $client = Client::create(['name' => 'Ana Pérez', 'email' => 'ana@example.com']);

        $response = $this->actingAs($admin)->post(route('admin.presupuestos.store'), [
            'client_id' => $client->id,
            'project_name' => 'Reforma de baño',
            'issue_date' => '2026-04-10',
            'observations' => 'Coordinar corte de agua con el cliente.',
            'tasks' => [
                ['description' => 'Retiro de revestimientos', 'labor_amount' => '200000.00'],
                ['description' => 'Colocación de cerámicos', 'labor_amount' => '150000.00'],
            ],
            'materials_amount' => '125000.00',
            'status' => 'accepted',
        ]);

        $quote = Quote::firstOrFail();
        $response->assertRedirect(route('admin.presupuestos.show', $quote));
        $this->assertSame('2026-05-01', $quote->expiry_date->format('Y-m-d'));
        $this->assertSame('Coordinar corte de agua con el cliente.', $quote->observations);
        $this->assertSame('350000.00', $quote->labor_amount);
        $this->assertSame('475000.00', $quote->total_amount);
        $this->assertDatabaseCount('quote_tasks', 2);

        $this->actingAs($admin)
            ->get(route('admin.presupuestos.edit', $quote))
            ->assertOk()
            ->assertSee('Retiro de revestimientos')
            ->assertSee('Colocación de cerámicos')
            ->assertSee('Coordinar corte de agua con el cliente.');

        $pdfHtml = view('admin.quotes.pdf', ['quote' => $quote->load('client', 'tasks')])->render();
        $this->assertStringNotContainsString('Aceptado', $pdfHtml);
        $this->assertStringContainsString('Retiro de revestimientos', $pdfHtml);
        $this->assertStringContainsString('Coordinar corte de agua con el cliente.', $pdfHtml);

        $this->get(route('admin.presupuestos.show', $quote))
            ->assertOk()
            ->assertSee('OBSERVACIONES')
            ->assertSee('Coordinar corte de agua con el cliente.');

        $this->actingAs($admin)
            ->get(route('admin.presupuestos.pdf', $quote))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->put(route('admin.presupuestos.update', $quote), [
            'client_id' => $client->id,
            'project_name' => 'Reforma de baño',
            'issue_date' => '2026-04-15',
            'observations' => 'Trabajo sujeto a inspección.',
            'tasks' => [
                ['description' => 'Reforma completa del baño', 'labor_amount' => '360000.00'],
                ['description' => 'Tareas sin cargo adicional', 'labor_amount' => '0'],
            ],
            'materials_amount' => '125000.00',
            'status' => 'rejected',
        ])->assertRedirect(route('admin.presupuestos.show', $quote));

        $this->assertSame('2026-05-06', $quote->fresh()->expiry_date->format('Y-m-d'));
        $this->assertSame('Trabajo sujeto a inspección.', $quote->fresh()->observations);
        $this->assertSame('rejected', $quote->fresh()->status);
        $this->assertSame('360000.00', $quote->fresh()->labor_amount);
        $this->assertSame('485000.00', $quote->fresh()->total_amount);
        $this->assertDatabaseCount('quote_tasks', 2);
    }

    public function test_quote_task_labor_can_be_calculated_from_quantity_and_unit_price(): void
    {
        $admin = User::factory()->create();
        $client = Client::create(['name' => 'Ana Pérez']);

        $this->actingAs($admin)->post(route('admin.presupuestos.store'), [
            'client_id' => $client->id,
            'project_name' => 'Colocación de cerámicos',
            'issue_date' => '2026-04-10',
            'tasks' => [
                [
                    'description' => 'Colocación de piso',
                    'pricing_mode' => 'quantity',
                    'quantity' => '12.5',
                    'unit' => 'm2',
                    'unit_price' => '850.00',
                    'labor_amount' => '999999.00',
                ],
                [
                    'description' => 'Colocación de zócalos',
                    'pricing_mode' => 'quantity',
                    'quantity' => '2.5',
                    'unit' => 'other',
                    'custom_unit' => 'paño',
                    'unit_price' => '700.00',
                ],
                [
                    'description' => 'Preparación',
                    'pricing_mode' => 'fixed',
                    'labor_amount' => '5000.00',
                ],
            ],
            'materials_amount' => '3000.00',
            'status' => 'pending',
        ])->assertRedirect();

        $quote = Quote::with('tasks')->firstOrFail();
        $this->assertSame('10625.00', $quote->tasks[0]->labor_amount);
        $this->assertSame('m²', $quote->tasks[0]->unit);
        $this->assertSame('1750.00', $quote->tasks[1]->labor_amount);
        $this->assertSame('paño', $quote->tasks[1]->unit);
        $this->assertSame('5000.00', $quote->tasks[2]->labor_amount);
        $this->assertSame('17375.00', $quote->labor_amount);
        $this->assertSame('20375.00', $quote->total_amount);

        $this->get(route('admin.presupuestos.edit', $quote))
            ->assertOk()
            ->assertSee('Por cantidad')
            ->assertSee('paño')
            ->assertSee('name="tasks[0][pricing_mode]"', false)
            ->assertSee('name="tasks[0][quantity]"', false)
            ->assertSee('name="tasks[0][unit_price]"', false);

        $this->get(route('admin.presupuestos.show', $quote))
            ->assertOk()
            ->assertSee('12,500 m²')
            ->assertSee('paño');

        $pdfHtml = view('admin.quotes.pdf', [
            'quote' => $quote->load('client', 'tasks'),
        ])->render();
        $this->assertStringContainsString('12,500 m²', $pdfHtml);
        $this->assertStringContainsString('paño', $pdfHtml);
    }

    public function test_quote_search_matches_project_or_client_and_status(): void
    {
        $admin = User::factory()->create();
        $client = Client::create(['name' => 'María Gómez']);
        Quote::create([
            'client_id' => $client->id,
            'project_name' => 'Pintura exterior',
            'issue_date' => '2026-04-10',
            'expiry_date' => '2026-05-01',
            'description' => 'Pintura.',
            'labor_amount' => 100,
            'materials_amount' => 50,
            'status' => 'accepted',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.presupuestos.index', ['search' => 'María', 'status' => 'accepted']))
            ->assertOk()
            ->assertSee('Pintura exterior');
    }

    public function test_admin_can_track_partial_payments_for_an_accepted_quote(): void
    {
        $admin = User::factory()->create();
        $client = Client::create(['name' => 'Ana Pérez']);
        $quote = Quote::create([
            'client_id' => $client->id,
            'project_name' => 'Reforma aceptada',
            'issue_date' => '2026-04-10',
            'expiry_date' => '2026-05-01',
            'description' => 'Reforma.',
            'labor_amount' => 800,
            'materials_amount' => 200,
            'status' => 'accepted',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.presupuestos.show', $quote))
            ->assertOk()
            ->assertSee('Pagos del presupuesto')
            ->assertSee('Saldo pendiente');

        $this->post(route('admin.presupuestos.pagos.store', $quote), [
            'amount' => '300.00',
            'payment_date' => '2026-04-12',
            'method' => 'transfer',
            'reference' => 'TX-123',
            'notes' => 'Anticipo de obra',
        ])->assertRedirect(route('admin.presupuestos.show', $quote));

        $payment = Payment::firstOrFail();
        $this->assertSame((string) $quote->id, (string) $payment->quote_id);
        $this->assertSame('300.00', $payment->amount);

        $this->actingAs($admin)
            ->get(route('admin.presupuestos.show', $quote))
            ->assertOk()
            ->assertSee('$ 300,00')
            ->assertSee('$ 500,00')
            ->assertSee('TX-123')
            ->assertSee('Descargar reporte');

        $reportHtml = view('admin.quotes.payments-pdf', [
            'quote' => $quote->load('client', 'payments'),
            'paidAmount' => 300.0,
            'remainingAmount' => 500.0,
        ])->render();
        $this->assertStringContainsString('REPORTE DE PAGOS', $reportHtml);
        $this->assertStringContainsString('TX-123', $reportHtml);
        $this->assertStringContainsString('$ 500,00 ARS', $reportHtml);

        $this->actingAs($admin)
            ->get(route('admin.presupuestos.pagos.pdf', $quote))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->from(route('admin.presupuestos.show', $quote))
            ->post(route('admin.presupuestos.pagos.store', $quote), [
                'amount' => '500.01',
                'payment_date' => '2026-04-12',
                'method' => 'transfer',
            ])
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('payments', 1);

        $this->delete(route('admin.presupuestos.pagos.destroy', [$quote, $payment]))
            ->assertRedirect(route('admin.presupuestos.show', $quote));
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }

    public function test_payments_are_only_allowed_for_accepted_quotes(): void
    {
        $admin = User::factory()->create();
        $client = Client::create(['name' => 'Cliente en espera']);
        $quote = Quote::create([
            'client_id' => $client->id,
            'project_name' => 'Presupuesto pendiente',
            'issue_date' => '2026-04-10',
            'expiry_date' => '2026-05-01',
            'description' => 'Trabajo.',
            'labor_amount' => 1000,
            'materials_amount' => 0,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.presupuestos.pagos.store', $quote), [
                'amount' => '100.00',
                'payment_date' => '2026-04-12',
                'method' => 'cash',
            ])
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('payments', 0);

        $this->get(route('admin.presupuestos.pagos.pdf', $quote))->assertNotFound();
    }

    public function test_quote_with_payments_cannot_be_rejected_or_reduced_below_the_paid_amount(): void
    {
        $admin = User::factory()->create();
        $client = Client::create(['name' => 'Cliente con anticipo']);
        $quote = Quote::create([
            'client_id' => $client->id,
            'project_name' => 'Obra con anticipo',
            'issue_date' => '2026-04-10',
            'expiry_date' => '2026-05-01',
            'description' => 'Trabajo.',
            'labor_amount' => 800,
            'materials_amount' => 200,
            'status' => 'accepted',
        ]);
        $quote->payments()->create([
            'amount' => 500,
            'payment_date' => '2026-04-12',
            'method' => 'cash',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.presupuestos.update', $quote), [
                'client_id' => $client->id,
                'project_name' => 'Obra con anticipo',
                'issue_date' => '2026-04-10',
                'tasks' => [['description' => 'Trabajo.', 'labor_amount' => 800]],
                'materials_amount' => 200,
                'status' => 'rejected',
            ])
            ->assertSessionHasErrors('status');

        $this->put(route('admin.presupuestos.update', $quote), [
            'client_id' => $client->id,
            'project_name' => 'Obra con anticipo',
            'issue_date' => '2026-04-10',
            'tasks' => [['description' => 'Trabajo reducido.', 'labor_amount' => 300]],
            'materials_amount' => 100,
            'status' => 'accepted',
        ])->assertSessionHasErrors('materials_amount');

        $this->assertSame('accepted', $quote->fresh()->status);
        $this->assertSame('1000.00', $quote->fresh()->total_amount);
        $this->assertDatabaseCount('payments', 1);
    }
}
