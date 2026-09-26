<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('import_batches', function (Blueprint $table) {
        $table->unsignedInteger('processed_rows')->default(0)->after('total_rows');
        $table->unsignedInteger('successful_rows')->default(0)->after('processed_rows');
        $table->unsignedInteger('skipped_rows')->default(0)->after('successful_rows');
        $table->unsignedInteger('error_rows')->default(0)->after('skipped_rows');
        $table->unsignedTinyInteger('progress')->default(0)->after('error_rows');
        $table->timestamp('started_at')->nullable()->after('uploaded_at');
        $table->timestamp('completed_at')->nullable()->after('started_at');
        $table->text('error_message')->nullable()->after('completed_at');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_batches', function (Blueprint $table) {
            $table->dropColumn([
                'processed_rows',
                'successful_rows',
                'skipped_rows',
                'error_rows',
                'progress',
                'started_at',
                'completed_at',
                'error_message',
            ]);
        });
    }
};
