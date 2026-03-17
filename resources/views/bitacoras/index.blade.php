<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Bitácora del Caso - INDEMNI SOAT</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
*{box-sizing:border-box}
body{
    font-family:Arial,Helvetica,sans-serif;
    background:#f4f6f9;
    padding:30px;
    margin:0;
    color:#111827;
}
.container{
    max-width:1100px;
    margin:auto;
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 8px 24px rgba(0,0,0,.06);
}
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    flex-wrap:wrap;
    margin-bottom:20px;
}
.badge{
    display:inline-block;
    padding:8px 12px;
    border-radius:999px;
    background:#e9ecef;
    color:#374151;
    font-size:13px;
    margin-top:8px;
}
.section{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:10px;
    padding:18px;
    margin-bottom:22px;
}
input,textarea{
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
    min-height:100px;
    resize:vertical;
}
button,.btn{
    display:inline-block;
    padding:10px 16px;
    background:#0d6efd;
    color:#fff;
    text-decoration:none;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-size:14px;
}
.btn-danger{background:#dc3545}
.btn-secondary{background:#6c757d}
.btn-light{
    background:#e5e7eb;
    color:#111827;
}
.alert{
    padding:12px 14px;
    background:#d1e7dd;
    color:#0f5132;
    border-radius:8px;
    margin-bottom:15px;
    border:1px solid #badbcc;
}
.alert-error{
    background:#f8d7da;
    color:#842029;
    border:1px solid #f5c2c7;
    padding:12px 14px;
    border-radius:8px;
    margin-bottom:15px;
}
.alert-error ul{
    margin:0;
    padding-left:18px;
}
.card{
    background:#f8f9fa;
    border:1px solid #ddd;
    border-radius:10px;
    padding:15px;
    margin-top:15px;
}
.top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:15px;
    flex-wrap:wrap;
}
.small{
    color:#666;
    font-size:13px;
    line-height:1.4;
}
form.inline{
    display:inline;
}
.actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}
.empty-state{
    padding:20px;
    text-align:center;
    color:#64748b;
    background:#f8fafc;
    border:1px dashed #cbd5e1;
    border-radius:10px;
}
h1,h2,h3{
    margin-top:0;
}
</style>
</head>
<body>
<div class="container">

    <div class="topbar">
        <div>
            <h1>Bitácora del Caso</h1>
            <h2 style="margin-bottom:0;">{{ $caso->numero_caso }} - {{ $caso->nombres }} {{ $caso->apellidos }}</h2>
            <div class="badge">Caso #{{ $caso->id }}</div>
        </div>

        <div class="actions">
            <a href="{{ route('casos.show', $caso) }}" class="btn btn-light">Ver caso</a>
            <a href="{{ route('casos.index') }}" class="btn btn-secondary">Volver a casos</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert">{{ session('success') }}</div>
    @endif

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

    <div class="section">
        <h3>Agregar movimiento a la bitácora</h3>
        <p class="small">Registra actuaciones, llamadas, envíos, respuestas, observaciones o cualquier gestión relevante del caso.</p>

        <form method="POST" action="{{ route('casos.bitacoras.store', $caso) }}">
            @csrf

            <label for="titulo">Título del movimiento</label>
            <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}" required>

            <label for="descripcion">Descripción</label>
            <textarea name="descripcion" id="descripcion">{{ old('descripcion') }}</textarea>

            <label for="fecha_evento">Fecha del evento</label>
            <input type="date" name="fecha_evento" id="fecha_evento" value="{{ old('fecha_evento', now()->format('Y-m-d')) }}">

            <div class="actions">
                <button type="submit">Guardar movimiento</button>
            </div>
        </form>
    </div>

    <div class="section">
        <h3>Historial del caso</h3>

        @forelse($bitacoras as $bitacora)
            <div class="card">
                <div class="top">
                    <div>
                        <strong>{{ $bitacora->titulo }}</strong><br>
                        <span class="small">
                            Fecha:
                            {{ $bitacora->fecha_evento ? \Carbon\Carbon::parse($bitacora->fecha_evento)->format('Y-m-d') : 'No registrada' }}
                        </span>
                    </div>

                    <form class="inline" method="POST" action="{{ route('casos.bitacoras.destroy', [$caso, $bitacora]) }}" onsubmit="return confirm('¿Eliminar este movimiento?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>

                <p style="margin-top:12px; margin-bottom:0;">
                    {{ $bitacora->descripcion ?: 'Sin descripción.' }}
                </p>
            </div>
        @empty
            <div class="empty-state">
                No hay movimientos registrados en este caso.
            </div>
        @endforelse
    </div>
</div>
</body>
</html>