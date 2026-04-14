<?php

// Ruta para verificar tablas en producción
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Route::get('/check-tables', function () {
    try {
        // Obtener todas las tablas
        $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
        
        $tableNames = [];
        foreach ($tables as $table) {
            $tableNames[] = $table->table_name;
        }
        
        // Buscar tablas de correo
        $emailTables = array_filter($tableNames, function($table) {
            return strpos($table, 'email') !== false;
        });
        
        return response()->json([
            'success' => true,
            'total_tables' => count($tableNames),
            'email_tables' => array_values($emailTables),
            'all_tables' => $tableNames
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error verificando tablas: ' . $e->getMessage()
        ]);
    }
});
