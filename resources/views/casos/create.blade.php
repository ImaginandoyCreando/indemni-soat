<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nuevo Caso - INDEMNI SOAT</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body{
    font-family:Arial,Helvetica,sans-serif;
    background:#f4f6f9;
    padding:30px;
    margin:0;
    color:#111827;
}
.container{
    max-width:980px;
    margin:auto;
    background:white;
    padding:25px;
    border-radius:10px;
    box-shadow:0 8px 24px rgba(0,0,0,.06);
}
.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
}
input,select,textarea{
    width:100%;
    padding:10px;
    margin-top:5px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:6px;
    box-sizing:border-box;
    font-family:inherit;
    font-size:14px;
}
textarea{
    min-height:120px;
    resize:vertical;
}
button{
    background:#0d6efd;
    color:white;
    border:none;
    padding:12px 20px;
    border-radius:6px;
    cursor:pointer;
    font-size:14px;
}
a{
    text-decoration:none;
}
h2,h3{
    margin-top:0;
}
.full{
    grid-column:1 / -1;
}
.section{
    background:#f8fafc;
    padding:16px;
    border-radius:8px;
    border:1px solid #e2e8f0;
}
.helper{
    display:block;
    margin-top:-8px;
    margin-bottom:12px;
    font-size:12px;
    color:#64748b;
    line-height:1.4;
}
.actions{
    margin-top:20px;
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}
.btn-secondary{
    background:#6c757d;
    color:#fff;
    padding:12px 20px;
    border-radius:6px;
    display:inline-block;
}
.alert-error{
    background:#f8d7da;
    color:#842029;
    border:1px solid #f5c2c7;
    padding:12px 14px;
    border-radius:8px;
    margin-bottom:18px;
}
.alert-error ul{
    margin:0;
    padding-left:18px;
}
.checkbox-box{
    display:flex;
    align-items:center;
    gap:10px;
    margin-top:5px;
    margin-bottom:15px;
    padding:10px 12px;
    border:1px solid #ccc;
    border-radius:6px;
    background:#fff;
}
.checkbox-box input[type="checkbox"]{
    width:auto;
    margin:0;
    transform:scale(1.1);
}
@media (max-width:900px){
    .grid{
        grid-template-columns:1fr;
    }
    body{
        padding:18px;
    }
}
</style>

</head>
<body>

<div class="container">

<h2>Registrar Nuevo Caso</h2>

@if($errors->any())
<div class="alert-error">
<strong>Corrige los siguientes errores:</strong>
<ul>
@foreach($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

<form method="POST" action="{{ route('casos.store') }}">
@csrf

<div class="grid">

<div class="full section">
<h3>1. Información general de la víctima</h3>
</div>

<div>
<label>Nombres</label>
<input type="text" name="nombres" value="{{ old('nombres') }}" required>
</div>

<div>
<label>Apellidos</label>
<input type="text" name="apellidos" value="{{ old('apellidos') }}" required>
</div>

<div>
<label>Cédula</label>
<input type="text" name="cedula" value="{{ old('cedula') }}" required>
</div>

<div>
<label>Teléfono</label>
<input type="text" name="telefono" value="{{ old('telefono') }}">
</div>

<div>
<label>Correo</label>
<input type="email" name="correo" value="{{ old('correo') }}">
</div>

<div>
<label>Departamento</label>
<input type="text" name="departamento" value="{{ old('departamento') }}">
</div>

<div>
<label>Ciudad</label>
<input type="text" name="ciudad" value="{{ old('ciudad') }}">
</div>

<div>
<label>Dirección</label>
<input type="text" name="direccion" value="{{ old('direccion') }}">
</div>

<div>
<label>Fecha del accidente</label>
<input type="date" name="fecha_accidente" value="{{ old('fecha_accidente') }}">
</div>

<div>
<label>Aseguradora</label>
<select name="aseguradora" required>
<option value="">Seleccionar</option>
@foreach($aseguradoras as $aseguradora)
<option value="{{ $aseguradora }}" {{ old('aseguradora') == $aseguradora ? 'selected' : '' }}>
{{ $aseguradora }}
</option>
@endforeach
</select>
</div>

<div>
<label>Junta asignada</label>
<select name="junta_asignada">
<option value="">Seleccionar</option>
@foreach($juntas as $junta)
<option value="{{ $junta }}" {{ old('junta_asignada') == $junta ? 'selected' : '' }}>
{{ $junta }}
</option>
@endforeach
</select>
<span class="helper">Puedes dejarla vacía al inicio si aún no aplica.</span>
</div>

<div class="full section">
<h3>2. Documentación inicial</h3>
</div>

<div>
<label>¿La víctima ya entregó poder firmado?</label>
<div class="checkbox-box">
<input type="checkbox" name="tiene_poder" value="1" {{ old('tiene_poder') ? 'checked' : '' }}>
<span>Sí, ya entregó el poder firmado</span>
</div>
<span class="helper">Marca esta opción solo cuando el poder ya esté efectivamente firmado y recibido.</span>
</div>

<div>
<label>Fecha en que se entregó el poder</label>
<input type="date" name="fecha_entrega_poder" value="{{ old('fecha_entrega_poder') }}">
<span class="helper">Úsala cuando le entregas el poder a la víctima para firma o cuando inicia ese pendiente.</span>
</div>

<div>
<label>Fecha poder firmado</label>
<input type="date" name="fecha_poder_firmado" value="{{ old('fecha_poder_firmado') }}">
<span class="helper">Fecha real en que la víctima devolvió el poder firmado.</span>
</div>

<div>
<label>¿La víctima ya entregó poder firmado?</label>
<div class="checkbox-box">
<input type="checkbox" name="tiene_contrato" value="1" {{ old('tiene_contrato') ? 'checked' : '' }}>
<span>Sí, ya entregó el poder firmado</span>
</div>
<span class="helper">Marca esta opción solo cuando el poder ya esté firmado y en poder del equipo.</span>
</div>

<div>
<label>Fecha en que se entregó el contrato</label>
<input type="date" name="fecha_entrega_contrato" value="{{ old('fecha_entrega_contrato') }}">
<span class="helper">Úsala cuando se le entrega el contrato a la víctima o cuando queda pendiente de firma.</span>
</div>

<div>
<label>Fecha contrato firmado</label>
<input type="date" name="fecha_contrato_firmado" value="{{ old('fecha_contrato_firmado') }}">
<span class="helper">Fecha real en que la víctima devolvió el contrato firmado.</span>
</div>

<div class="full section">
<h3>3. Estado inicial del proceso</h3>
</div>

<div class="full">
<label>Estado</label>
<select name="estado" required>
@foreach($estados as $estado)
<option value="{{ $estado }}" {{ old('estado') == $estado ? 'selected' : '' }}>
{{ $estado }}
</option>
@endforeach
</select>

<span class="helper">
Lo normal al crear un caso nuevo suele ser
<strong>Nuevo</strong> o
<strong>Solicitud de calificación enviada</strong>.
</span>

</div>

<div class="full">
<label>Observaciones</label>
<textarea name="observaciones">{{ old('observaciones') }}</textarea>
</div>

</div>

<div class="actions">
<button type="submit">Guardar Caso</button>
<a href="{{ route('casos.index') }}" class="btn-secondary">← Volver</a>
</div>

</form>

</div>

</body>
</html>