<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function index(): View
    {
        return view('home');
    }

    public function storeInquiry(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'service' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        Inquiry::create($validated);

        return redirect()->to(route('home') . '#consulta')->with('success', '¡Gracias! Recibimos tu consulta y nos pondremos en contacto.');
    }
}
