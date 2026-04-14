<?php

// Ruta temporal para migrar en producción - ELIMINAR DESPUÉS DE USAR
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/migrate-production', function () {
    try {
        // Ejecutar migraciones
        Artisan::call('migrate', ['--force' => true]);
        
        $output = Artisan::output();
        
        return response()->json([
            'success' => true,
            'message' => 'Migraciones ejecutadas exitosamente',
            'output' => $output
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error ejecutando migraciones: ' . $e->getMessage(),
            'error' => $e->getMessage()
        ]);
    }
});
