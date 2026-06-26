<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\CasoController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OutlookAuthController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\PlantillaDocumentoController;
use App\Http\Controllers\DocumentoGeneradoController;

// ── Rutas públicas ────────────────────────────────────────────────────────────
Route::get('/login',  [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');

// ── Redirect raíz ─────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('casos.index'));

// ── Rutas protegidas ──────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // ── Correos ───────────────────────────────────────────────────────────────
    Route::prefix('emails')->name('emails.')->group(function () {
        Route::get('/',                [EmailController::class, 'index'])->name('index');
        Route::post('/sync',           [EmailController::class, 'sync'])->name('sync');
        Route::post('/add-account',    [EmailController::class, 'addAccount'])->name('addAccount');
        Route::post('/save-config',    [EmailController::class, 'saveConfig'])->name('saveConfig');
        Route::delete('/account/{id}', [EmailController::class, 'removeAccount'])->name('removeAccount');
        Route::get('/test-connection', [EmailController::class, 'testConnection'])->name('testConnection');
        // OAuth Device Code Flow
        Route::get('/oauth/setup',     [EmailController::class, 'oauthSetup'])->name('oauthSetup');
        Route::get('/oauth/poll',      [EmailController::class, 'oauthPoll'])->name('oauthPoll');
        Route::delete('/oauth/revoke', [EmailController::class, 'oauthRevoke'])->name('oauthRevoke');
    });

    // ── Outlook OAuth ─────────────────────────────────────────────────────────
    Route::get('/outlook/connect',       [OutlookAuthController::class, 'redirectToOutlook'])->name('outlook.connect');
    Route::get('/outlook/callback',      [OutlookAuthController::class, 'handleCallback'])->name('outlook.callback');
    Route::post('/outlook/disconnect/{id}', [OutlookAuthController::class, 'disconnect'])->name('outlook.disconnect');

    // ── Dashboard ─────────────────────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::middleware('role:admin,abogado')->group(function () {
        Route::get('/dashboard/exportar-excel', [ReporteController::class, 'exportarExcel'])->name('dashboard.exportarExcel');
        Route::get('/dashboard/exportar-pdf',   [ReporteController::class, 'exportarPdf'])->name('dashboard.exportarPdf');
    });

    // ── Casos — lectura (todos) ───────────────────────────────────────────────
    Route::get('/casos', [CasoController::class, 'index'])->name('casos.index');

    // ── Casos — escritura (admin + abogado) ──────────────────────────────────
    Route::middleware('role:admin,abogado')->group(function () {
        Route::get('/casos/create',      [CasoController::class, 'create'])->name('casos.create');
        Route::post('/casos',            [CasoController::class, 'store'])->name('casos.store');
        Route::get('/casos/{caso}/edit', [CasoController::class, 'edit'])->name('casos.edit');
        Route::put('/casos/{caso}',      [CasoController::class, 'update'])->name('casos.update');
        Route::patch('/casos/{caso}',    [CasoController::class, 'update']);

        // ── Voucher PDF ───────────────────────────────────────────────────────
        Route::get('/casos/{caso}/voucher-pdf', [CasoController::class, 'generarVoucherPdf'])->name('casos.voucher');
    });

    // ── Esta va DESPUÉS del /create ───────────────────────────────────────────
    Route::get('/casos/{caso}', [CasoController::class, 'show'])->name('casos.show');

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

    // ── Documentos ────────────────────────────────────────────────────────────
    Route::prefix('casos/{caso}')->group(function () {
        Route::get('/documentos', [DocumentoController::class, 'index'])->name('casos.documentos.index');

        Route::middleware('role:admin,abogado')->group(function () {
            Route::post('/documentos',               [DocumentoController::class, 'store'])->name('casos.documentos.store');
            Route::delete('/documentos/{documento}', [DocumentoController::class, 'destroy'])->name('casos.documentos.destroy');
        });

        // ── Bitácora ──────────────────────────────────────────────────────────
        Route::get('/bitacoras', [BitacoraController::class, 'index'])->name('casos.bitacoras.index');

        Route::middleware('role:admin,abogado')->group(function () {
            Route::post('/bitacoras',              [BitacoraController::class, 'store'])->name('casos.bitacoras.store');
            Route::delete('/bitacoras/{bitacora}', [BitacoraController::class, 'destroy'])->name('casos.bitacoras.destroy');
        });
    });

    // ── Usuarios (solo admin) ─────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('/usuarios',               [UserController::class, 'index'])->name('users.index');
        Route::get('/usuarios/crear',         [UserController::class, 'create'])->name('users.create');
        Route::post('/usuarios',              [UserController::class, 'store'])->name('users.store');
        Route::get('/usuarios/{user}/editar', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/usuarios/{user}',        [UserController::class, 'update'])->name('users.update');
        Route::delete('/usuarios/{user}',     [UserController::class, 'destroy'])->name('users.destroy');
    });

    // ── Plantillas de documentos (solo admin) ─────────────────────────────
    Route::middleware('role:admin')->prefix('plantillas')->name('plantillas.')->group(function () {
        Route::get('/',             [PlantillaDocumentoController::class, 'index'])->name('index');
        Route::post('/',            [PlantillaDocumentoController::class, 'store'])->name('store');
        Route::delete('/{plantilla}',[PlantillaDocumentoController::class, 'destroy'])->name('destroy');
    });

    // ── Generación de documentos (admin + abogado) ────────────────────────
    Route::middleware('role:admin,abogado')->prefix('casos/{caso}/generar')->name('casos.generar.')->group(function () {
        Route::get('/{tipo}',                  [DocumentoGeneradoController::class, 'form'])->name('form');
        Route::post('/{tipo}',                 [DocumentoGeneradoController::class, 'generar'])->name('generar');
        Route::get('/descarga/{documento}',    [DocumentoGeneradoController::class, 'descargar'])->name('descargar');
        Route::delete('/eliminar/{documento}', [DocumentoGeneradoController::class, 'destroy'])->name('destroy');
    });

});
