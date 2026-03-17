<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CasoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;

// ── Rutas públicas (sin autenticación) ───────────────────────────────────────

Route::get('/login',  [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');

// ── Redirect raíz ─────────────────────────────────────────────────────────────

Route::get('/', function () {
    return redirect()->route('casos.index');
});

// ── Rutas protegidas (requieren login) ────────────────────────────────────────

Route::middleware(['auth'])->group(function () {

    // ── Dashboard ─────────────────────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/exportar-excel', [ReporteController::class, 'exportarExcel'])
        ->middleware('role:admin,abogado')
        ->name('dashboard.exportarExcel');
    Route::get('/dashboard/exportar-pdf', [ReporteController::class, 'exportarPdf'])
        ->middleware('role:admin,abogado')
        ->name('dashboard.exportarPdf');

    // ── Casos — lectura (todos los roles) ────────────────────────────────────
    Route::get('/casos',          [CasoController::class, 'index'])->name('casos.index');
    Route::get('/casos/{caso}',   [CasoController::class, 'show'])->name('casos.show');

    // ── Casos — escritura (admin + abogado) ──────────────────────────────────
    Route::middleware('role:admin,abogado')->group(function () {
        Route::get('/casos/create',       [CasoController::class, 'create'])->name('casos.create');
        Route::post('/casos',             [CasoController::class, 'store'])->name('casos.store');
        Route::get('/casos/{caso}/edit',  [CasoController::class, 'edit'])->name('casos.edit');
        Route::put('/casos/{caso}',       [CasoController::class, 'update'])->name('casos.update');
        Route::patch('/casos/{caso}',     [CasoController::class, 'update']);
    });

    // ── Casos — eliminar (solo admin) ────────────────────────────────────────
    Route::delete('/casos/{caso}', [CasoController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('casos.destroy');

    // ── Acciones rápidas del flujo jurídico (admin + abogado) ────────────────
    Route::middleware('role:admin,abogado')->prefix('casos/{caso}')->group(function () {
        Route::post('/marcar-solicitud-aseguradora',   [CasoController::class, 'marcarSolicitudAseguradora'])->name('casos.marcarSolicitudAseguradora');
        Route::post('/marcar-respuesta-aseguradora',   [CasoController::class, 'marcarRespuestaAseguradora'])->name('casos.marcarRespuestaAseguradora');
        Route::post('/marcar-apelacion',               [CasoController::class, 'marcarApelacion'])->name('casos.marcarApelacion');
        Route::post('/marcar-tutela',                  [CasoController::class, 'marcarTutela'])->name('casos.marcarTutela');
        Route::post('/marcar-fallo-tutela',            [CasoController::class, 'marcarFalloTutela'])->name('casos.marcarFalloTutela');
        Route::post('/marcar-cumplimiento-tutela',     [CasoController::class, 'marcarCumplimientoTutela'])->name('casos.marcarCumplimientoTutela');
        Route::post('/marcar-incidente-desacato',      [CasoController::class, 'marcarIncidenteDesacato'])->name('casos.marcarIncidenteDesacato');
        Route::post('/marcar-impugnacion',             [CasoController::class, 'marcarImpugnacion'])->name('casos.marcarImpugnacion');
        Route::post('/marcar-fallo-segunda-instancia', [CasoController::class, 'marcarFalloSegundaInstancia'])->name('casos.marcarFalloSegundaInstancia');
        Route::post('/marcar-pago-honorarios',         [CasoController::class, 'marcarPagoHonorarios'])->name('casos.marcarPagoHonorarios');
        Route::post('/marcar-alta-ortopedia',          [CasoController::class, 'marcarAltaOrtopedia'])->name('casos.marcarAltaOrtopedia');
        Route::post('/marcar-solicitud-junta',         [CasoController::class, 'marcarSolicitudJunta'])->name('casos.marcarSolicitudJunta');
        Route::post('/marcar-dictamen-junta',          [CasoController::class, 'marcarDictamenJunta'])->name('casos.marcarDictamenJunta');
        Route::post('/marcar-furpen',                  [CasoController::class, 'marcarFurpen'])->name('casos.marcarFurpen');
        Route::post('/marcar-reclamacion',             [CasoController::class, 'marcarReclamacion'])->name('casos.marcarReclamacion');
        Route::post('/marcar-pago',                    [CasoController::class, 'marcarPago'])->name('casos.marcarPago');
    });

    // ── Documentos (todos ven, solo admin+abogado suben/eliminan) ────────────
    Route::prefix('casos/{caso}')->group(function () {
        Route::get('/documentos', [DocumentoController::class, 'index'])->name('casos.documentos.index');

        Route::middleware('role:admin,abogado')->group(function () {
            Route::post('/documentos',               [DocumentoController::class, 'store'])->name('casos.documentos.store');
            Route::delete('/documentos/{documento}', [DocumentoController::class, 'destroy'])->name('casos.documentos.destroy');
        });

        // ── Bitácora (todos ven, solo admin+abogado agregan/eliminan) ────────
        Route::get('/bitacoras', [BitacoraController::class, 'index'])->name('casos.bitacoras.index');

        Route::middleware('role:admin,abogado')->group(function () {
            Route::post('/bitacoras',              [BitacoraController::class, 'store'])->name('casos.bitacoras.store');
            Route::delete('/bitacoras/{bitacora}', [BitacoraController::class, 'destroy'])->name('casos.bitacoras.destroy');
        });
    });

    // ── Gestión de usuarios (solo admin) ─────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('/usuarios',               [UserController::class, 'index'])->name('users.index');
        Route::get('/usuarios/crear',         [UserController::class, 'create'])->name('users.create');
        Route::post('/usuarios',              [UserController::class, 'store'])->name('users.store');
        Route::get('/usuarios/{user}/editar', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/usuarios/{user}',        [UserController::class, 'update'])->name('users.update');
        Route::delete('/usuarios/{user}',     [UserController::class, 'destroy'])->name('users.destroy');
    });

});