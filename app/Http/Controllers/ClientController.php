<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $clients = Client::query()
            ->withCount('quotes')
            ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.clients.index', compact('clients', 'search'));
    }

    public function create(): View
    {
        return view('admin.clients.form', ['client' => new Client()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Client::create($this->validated($request));

        return redirect()->route('admin.clientes.index')->with('success', 'Cliente creado correctamente.');
    }

    public function edit(Client $client): View
    {
        return view('admin.clients.form', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $client->update($this->validated($request));

        return redirect()->route('admin.clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        if ($client->quotes()->exists()) {
            throw ValidationException::withMessages([
                'client' => 'No se puede eliminar un cliente que tiene presupuestos asociados.',
            ]);
        }

        $client->delete();

        return redirect()->route('admin.clientes.index')->with('success', 'Cliente eliminado correctamente.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
