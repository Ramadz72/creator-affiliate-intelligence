<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('creator_campaign_performances', function (Blueprint $table) {
            $table->date('performance_date')
                ->nullable()
                ->after('campaign_id');
        });
    }

    public function down(): void
    {
        Schema::table('creator_campaign_performances', function (Blueprint $table) {
            $table->dropColumn('performance_date');
        });
    }
};