<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Iniciar sesión — INDEMNI SOAT</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{
    font-family:Arial,Helvetica,sans-serif;
    background:linear-gradient(135deg,#1f2937 0%,#172033 100%);
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
}
.card{
    background:#fff;
    border-radius:16px;
    padding:40px;
    width:100%;
    max-width:420px;
    box-shadow:0 25px 80px rgba(0,0,0,.35);
}
.logo{
    font-size:32px;
    font-weight:bold;
    color:#1f2937;
    line-height:1.1;
    margin-bottom:6px;
}
.logo span{color:#2563eb}
.subtitle{
    font-size:13px;
    color:#6b7280;
    margin-bottom:32px;
}
label{
    display:block;
    font-size:13px;
    font-weight:bold;
    color:#374151;
    margin-bottom:6px;
}
input[type="email"],
input[type="password"]{
    width:100%;
    padding:12px 14px;
    border:1.5px solid #d1d5db;
    border-radius:8px;
    font-size:14px;
    font-family:inherit;
    transition:border-color .2s, box-shadow .2s;
    margin-bottom:18px;
}
input:focus{
    outline:none;
    border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,.12);
}
input.error-field{
    border-color:#dc3545;
}
.check-row{
    display:flex;
    align-items:center;
    gap:8px;
    margin-bottom:24px;
    font-size:13px;
    color:#374151;
    cursor:pointer;
}
.check-row input[type="checkbox"]{
    width:16px;height:16px;
    accent-color:#2563eb;
    margin-bottom:0;
    cursor:pointer;
}
.btn-login{
    width:100%;
    padding:13px;
    background:#2563eb;
    color:#fff;
    border:none;
    border-radius:8px;
    font-size:15px;
    font-weight:bold;
    cursor:pointer;
    transition:background .2s;
}
.btn-login:hover{background:#1d4ed8}
.alert-error{
    background:#fef2f2;
    border:1px solid #fecaca;
    border-radius:8px;
    padding:12px 14px;
    margin-bottom:20px;
    font-size:13px;
    color:#dc2626;
}
.alert-success{
    background:#f0fdf4;
    border:1px solid #bbf7d0;
    border-radius:8px;
    padding:12px 14px;
    margin-bottom:20px;
    font-size:13px;
    color:#16a34a;
}
.field-error{
    font-size:12px;
    color:#dc2626;
    margin-top:-14px;
    margin-bottom:14px;
    display:block;
}
</style>
</head>
<body>

<div class="card">
    <div class="logo">INDEMNI<span>SOAT</span></div>
    <div class="subtitle">Sistema de gestión jurídica — Inicia sesión para continuar</div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->has('email') && !$errors->has('email'))
        {{-- handled inline --}}
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf

        <label for="email">Correo electrónico</label>
        <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email') }}"
            placeholder="tu@correo.com"
            autocomplete="email"
            autofocus
            class="{{ $errors->has('email') ? 'error-field' : '' }}"
        >
        @error('email')
            <span class="field-error">{{ $message }}</span>
        @enderror

        <label for="password">Contraseña</label>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="••••••••"
            autocomplete="current-password"
            class="{{ $errors->has('password') ? 'error-field' : '' }}"
        >
        @error('password')
            <span class="field-error">{{ $message }}</span>
        @enderror

        <label class="check-row">
            <input type="checkbox" name="recordar" value="1" {{ old('recordar') ? 'checked' : '' }}>
            Recordarme en este dispositivo
        </label>

        <button type="submit" class="btn-login">Iniciar sesión</button>
    </form>
</div>

</body>
</html>