<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Expediente del Caso - INDEMNI SOAT</title>
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
    max-width:1200px;
    margin:auto;
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 8px 24px rgba(0,0,0,.06);
}
h1,h2,h3{
    margin-top:0;
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
.section{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:10px;
    padding:18px;
    margin-bottom:22px;
}
.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:16px;
}
.full{
    grid-column:1 / -1;
}
label{
    display:block;
    font-weight:bold;
    margin-bottom:6px;
}
input,select{
    width:100%;
    padding:10px;
    margin-top:5px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:6px;
    font-family:inherit;
    font-size:14px;
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
.actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}
.table-wrap{
    overflow:auto;
    border:1px solid #d7dce3;
    border-radius:10px;
}
table{
    width:100%;
    border-collapse:collapse;
    background:#fff;
}
th,td{
    padding:12px;
    border-bottom:1px solid #ddd;
    text-align:left;
    vertical-align:top;
}
th{
    background:#eef2f7;
    white-space:nowrap;
}
form.inline{
    display:inline;
}
.link-file{
    color:#2563eb;
    text-decoration:none;
    font-weight:600;
}
.link-file:hover{
    text-decoration:underline;
}
.empty-state{
    padding:20px;
    text-align:center;
    color:#64748b;
}
.small{
    color:#6b7280;
    font-size:13px;
    line-height:1.4;
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
    <div class="topbar">
        <div>
            <h1>Expediente Digital</h1>
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
        <h3>Subir documento al expediente</h3>
        <p class="small">Puedes cargar soportes como cédula, historia clínica, dictámenes, tutelas, reclamaciones, comprobantes y demás anexos del caso.</p>

        <form method="POST" action="{{ route('casos.documentos.store', $caso) }}" enctype="multipart/form-data">
            @csrf

            <div class="grid">
                <div>
                    <label for="tipo_documento">Tipo de documento</label>
                    <select name="tipo_documento" id="tipo_documento" required>
                        <option value="">Seleccione...</option>
                        @foreach($tipos as $tipo)
                            <option value="{{ $tipo }}" {{ old('tipo_documento') == $tipo ? 'selected' : '' }}>
                                {{ $tipo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="archivo">Archivo</label>
                    <input type="file" name="archivo" id="archivo" required>
                    <div class="small">Formatos permitidos: PDF, JPG, JPEG, PNG, DOC, DOCX. Máximo 10 MB.</div>
                </div>

                <div class="full actions">
                    <button type="submit">Subir documento</button>
                </div>
            </div>
        </form>
    </div>

    <div class="section">
        <h3>Documentos cargados</h3>

        @if($documentos->count())
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Archivo</th>
                            <th>Fecha de carga</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documentos as $documento)
                            <tr>
                                <td>{{ $documento->id }}</td>
                                <td>{{ $documento->tipo_documento }}</td>
                                <td>
                                    <a href="{{ asset('storage/' . $documento->archivo) }}" target="_blank" class="link-file">
                                        Ver archivo
                                    </a>
                                </td>
                                <td>{{ optional($documento->created_at)->format('Y-m-d H:i') }}</td>
                                <td>
                                    <form class="inline" method="POST" action="{{ route('casos.documentos.destroy', [$caso, $documento]) }}" onsubmit="return confirm('¿Eliminar este documento?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                No hay documentos cargados en este expediente.
            </div>
        @endif
    </div>
</div>
</body>
</html>