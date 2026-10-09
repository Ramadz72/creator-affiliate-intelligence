<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('creator_campaign_performances')) {
            return;
        }

        Schema::create('creator_campaign_performances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campaign_id')
                ->constrained('campaigns')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->date('performance_date')->nullable();

            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('likes')->default(0);
            $table->unsignedBigInteger('comments')->default(0);
            $table->unsignedBigInteger('shares')->default(0);
            $table->unsignedBigInteger('saves')->default(0);

            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('orders')->default(0);
            $table->unsignedBigInteger('buyers')->default(0);

            $table->decimal('gmv', 15, 2)->default(0);
            $table->decimal('engagement_rate', 5, 2)->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            $table->decimal('cost_per_view', 15, 2)->default(0);
            $table->decimal('cost_per_order', 15, 2)->default(0);
            $table->decimal('roi', 8, 2)->default(0);
            $table->decimal('roas', 12, 4)->default(0);

            $table->timestamp('created_at')->useCurrent();

            $table->index(
                'campaign_id',
                'idx_campaign_performances_campaign_id'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creator_campaign_performances');
    }
};
