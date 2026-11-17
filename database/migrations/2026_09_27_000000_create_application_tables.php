<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('creators')) {
            Schema::create('creators', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')
                    ->constrained('users', 'id', 'fk_creators_user')
                    ->cascadeOnDelete();
                $table->string('name', 150);
                $table->string('username', 100);
                $table->string('platform', 50);
                $table->string('category', 100);
                $table->unsignedBigInteger('followers')->default(0);
                $table->json('audience_gender')->nullable();
                $table->json('audience_age')->nullable();
                $table->json('audience_location')->nullable();
                $table->string('profile_link', 500)->nullable();
                $table->string('profile_image', 500)->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->text('notes')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->index('username', 'idx_creators_username');
                $table->index('user_id', 'idx_creators_user_id');
            });
        }

        if (! Schema::hasTable('campaigns')) {
            Schema::create('campaigns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('creator_id')
                    ->constrained('creators', 'id', 'fk_campaigns_creator')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
                $table->string('campaign_name', 150);
                $table->string('product_name', 150);
                $table->string('platform', 50);
                $table->string('deliverable', 100);
                $table->decimal('agreed_price', 15, 2)->default(0);
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->enum('status', ['planned', 'running', 'completed', 'cancelled'])->default('planned');
                $table->text('notes')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index('creator_id', 'idx_campaigns_creator_id');
            });
        }

        if (! Schema::hasTable('creator_contents')) {
            Schema::create('creator_contents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('creator_id')
                    ->constrained('creators', 'id', 'fk_creator_contents_creator')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
                $table->date('content_date');
                $table->string('content_url', 500)->nullable();
                $table->string('content_type', 50)->nullable();
                $table->unsignedBigInteger('views')->default(0);
                $table->unsignedBigInteger('likes')->default(0);
                $table->unsignedBigInteger('comments')->default(0);
                $table->unsignedBigInteger('shares')->default(0);
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->index('creator_id', 'idx_creator_contents_creator_id');
                $table->index('content_date', 'idx_creator_contents_date');
            });
        }

        if (! Schema::hasTable('creator_rate_cards')) {
            Schema::create('creator_rate_cards', function (Blueprint $table) {
                $table->id();
                $table->foreignId('creator_id')
                    ->constrained('creators', 'id', 'fk_creator_rate_cards_creator')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
                $table->string('platform', 50);
                $table->string('deliverable', 100);
                $table->decimal('price', 15, 2)->default(0);
                $table->string('duration', 50)->nullable();
                $table->unsignedInteger('revision')->default(0);
                $table->string('usage_rights', 255)->nullable();
                $table->date('valid_until')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->index('creator_id', 'idx_rate_cards_creator_id');
            });
        }

        if (! Schema::hasTable('creator_scores')) {
            Schema::create('creator_scores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('creator_id')
                    ->constrained('creators', 'id', 'fk_creator_scores_creator')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('performance_score', 5, 2)->default(0);
                $table->decimal('engagement_score', 5, 2)->default(0);
                $table->decimal('audience_fit_score', 5, 2)->default(0);
                $table->decimal('historical_score', 5, 2)->default(0);
                $table->decimal('deal_value_score', 5, 2)->default(0);
                $table->decimal('overall_score', 5, 2)->default(0);
                $table->enum('recommendation', ['highly_recommended', 'recommended', 'negotiate', 'not_recommended'])->default('not_recommended');

                $table->index('creator_id', 'idx_creator_scores_creator_id');
            });
        }

        if (! Schema::hasTable('creator_analysis_snapshots')) {
            Schema::create('creator_analysis_snapshots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('creator_score_id')
                    ->constrained('creator_scores', 'id', 'fk_snapshot_creator_score')
                    ->cascadeOnDelete();
                $table->unsignedInteger('content_count')->default(0);
                $table->unsignedBigInteger('average_views')->default(0);
                $table->decimal('engagement_rate', 8, 4)->default(0);
                $table->string('rate_card_platform', 50)->nullable();
                $table->string('rate_card_deliverable', 100)->nullable();
                $table->decimal('rate_card_price', 15, 2)->nullable();
                $table->decimal('cost_per_view', 15, 8)->nullable();
                $table->json('insights')->nullable();
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->decimal('average_roas', 12, 2)->nullable();

                $table->unique('creator_score_id', 'unique_creator_score_snapshot');
            });
        }

        if (! Schema::hasTable('affiliates')) {
            Schema::create('affiliates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')
                    ->constrained('users', 'id', 'fk_affiliates_user')
                    ->cascadeOnDelete();
                $table->string('name', 150);
                $table->string('username', 100);
                $table->string('platform', 50);
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->index('username', 'idx_affiliates_username');
                $table->index('user_id', 'idx_affiliates_user_id');
            });
        }

        if (! Schema::hasTable('affiliate_performances')) {
            Schema::create('affiliate_performances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('affiliate_id')
                    ->constrained('affiliates', 'id', 'fk_affiliate_performances_affiliate')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
                $table->foreignId('import_batch_id')
                    ->constrained('import_batches', 'id', 'fk_affiliate_performances_batch')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();
                $table->decimal('gmv', 15, 2)->default(0);
                $table->decimal('gmv_live', 15, 2)->default(0);
                $table->decimal('gmv_video', 15, 2)->default(0);
                $table->decimal('gmv_product_card', 15, 2)->default(0);
                $table->decimal('refund', 15, 2)->default(0);
                $table->unsignedBigInteger('attributed_orders')->default(0);
                $table->unsignedBigInteger('products_sold')->default(0);
                $table->decimal('aov', 15, 2)->default(0);
                $table->decimal('ctr', 5, 2)->default(0);
                $table->decimal('ctor', 5, 2)->default(0);
                $table->unsignedBigInteger('impressions')->default(0);
                $table->unsignedBigInteger('video_views')->default(0);
                $table->unsignedBigInteger('buyers')->default(0);
                $table->decimal('commission', 15, 2)->default(0);
                $table->unsignedInteger('live_count')->default(0);
                $table->unsignedInteger('video_count')->default(0);
                $table->unsignedInteger('showcase_products')->default(0);
                $table->unsignedInteger('content_samples')->default(0);
                $table->unsignedInteger('samples_sent')->default(0);
                $table->unsignedBigInteger('products_returned')->default(0);
                $table->text('notes')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->unique(['affiliate_id', 'import_batch_id'], 'uq_affiliate_batch');
                $table->index('affiliate_id', 'idx_affiliate_performances_affiliate_id');
                $table->index('import_batch_id', 'idx_affiliate_performances_batch_id');
                $table->index(['import_batch_id', 'affiliate_id'], 'idx_affiliate_performances_batch_affiliate');
            });
        }

        if (! Schema::hasTable('affiliate_scores')) {
            Schema::create('affiliate_scores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('affiliate_id')
                    ->constrained('affiliates', 'id', 'fk_affiliate_scores_affiliate')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
                $table->foreignId('import_batch_id')
                    ->constrained('import_batches', 'id', 'fk_affiliate_scores_batch')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();
                $table->decimal('performance_score', 5, 2)->default(0);
                $table->decimal('growth_score', 5, 2)->default(0);
                $table->decimal('consistency_score', 5, 2)->default(0);
                $table->decimal('opportunity_score', 5, 2)->default(0);
                $table->decimal('overall_score', 5, 2)->default(0);
                $table->enum('action', ['CHASE', 'SUPPORT', 'MONITOR', 'DEPRIORITIZE'])->default('MONITOR');
                $table->timestamp('generated_at')->useCurrent();

                $table->unique(['affiliate_id', 'import_batch_id'], 'uq_affiliate_score_batch');
                $table->index('affiliate_id', 'idx_affiliate_scores_affiliate_id');
                $table->index('import_batch_id', 'idx_affiliate_scores_batch_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_scores');
        Schema::dropIfExists('affiliate_performances');
        Schema::dropIfExists('affiliates');
        Schema::dropIfExists('creator_analysis_snapshots');
        Schema::dropIfExists('creator_scores');
        Schema::dropIfExists('creator_rate_cards');
        Schema::dropIfExists('creator_contents');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('creators');
    }
};
