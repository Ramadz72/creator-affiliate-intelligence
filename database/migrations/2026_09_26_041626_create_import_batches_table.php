<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('import_batches')) {
            return;
        }

        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->date('period_start');
            $table->date('period_end');

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->index(
                'uploaded_by',
                'idx_import_batches_uploaded_by'
            );

            $table->index(
                ['period_start', 'period_end'],
                'idx_import_batches_period'
            );

            $table->enum('status', [
                'queued',
                'processing',
                'scoring',
                'completed',
                'failed',
            ])->default('queued');

            $table->unsignedInteger('total_rows')->default(0);
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_batches');
    }
};
