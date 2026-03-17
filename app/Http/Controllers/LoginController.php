<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // ── Mostrar formulario de login ───────────────────────────────────────────

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('casos.index');
        }

        return view('auth.login');
    }

    // ── Procesar login ────────────────────────────────────────────────────────

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'El correo es obligatorio.',
            'email.email'       => 'Ingresa un correo válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $credenciales = $request->only('email', 'password');
        $recordar     = $request->boolean('recordar');

        if (Auth::attempt($credenciales, $recordar)) {
            $request->session()->regenerate();
            return redirect()->intended(route('casos.index'))
                ->with('success', 'Bienvenido, ' . Auth::user()->name . '.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Las credenciales no son correctas.']);
    }

    // ── Cerrar sesión ─────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Sesión cerrada correctamente.');
    }
}