public function up(): void
{
    Schema::table('email_logs_new', function (Blueprint $table) {
        // Solo agrega si no existe
        if (!Schema::hasColumn('email_logs_new', 'caso_id')) {
            $table->unsignedBigInteger('caso_id')->nullable()->after('id');
            $table->foreign('caso_id')->references('id')->on('casos')->onDelete('set null');
        }
    });
}

public function down(): void
{
    Schema::table('email_logs_new', function (Blueprint $table) {
        $table->dropForeign(['caso_id']);
        $table->dropColumn('caso_id');
    });
}
