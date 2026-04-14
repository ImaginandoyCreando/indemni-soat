<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_logs_new', function (Blueprint $table) {
            if (!Schema::hasColumn('email_logs_new', 'email_id')) {
                $table->string('email_id')->nullable();
            }
            if (!Schema::hasColumn('email_logs_new', 'subject')) {
                $table->string('subject')->nullable();
            }
            if (!Schema::hasColumn('email_logs_new', 'body')) {
                $table->longText('body')->nullable();
            }
            if (!Schema::hasColumn('email_logs_new', 'from_email')) {
                $table->string('from_email')->nullable();
            }
            if (!Schema::hasColumn('email_logs_new', 'from_name')) {
                $table->string('from_name')->nullable();
            }
            if (!Schema::hasColumn('email_logs_new', 'email_date')) {
                $table->timestamp('email_date')->nullable();
            }
            if (!Schema::hasColumn('email_logs_new', 'detected_insurance')) {
                $table->string('detected_insurance')->nullable();
            }
            if (!Schema::hasColumn('email_logs_new', 'email_type')) {
                $table->string('email_type')->nullable();
            }
            if (!Schema::hasColumn('email_logs_new', 'extracted_data')) {
                $table->json('extracted_data')->nullable();
            }
            if (!Schema::hasColumn('email_logs_new', 'processed')) {
                $table->boolean('processed')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('email_logs_new', function (Blueprint $table) {
            $table->dropColumn([
                'email_id', 'subject', 'body', 'from_email', 'from_name',
                'email_date', 'detected_insurance', 'email_type', 'extracted_data', 'processed'
            ]);
        });
    }
};