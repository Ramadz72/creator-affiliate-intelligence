<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\CreatorScore;
use App\Models\ImportBatch;
use Carbon\Carbon;

$user = User::where("email", "ramadaniilham72@gmail.com")->first();
if ($user) {
    $latestBatch = ImportBatch::query()
        ->where('status', 'completed')
        ->where('uploaded_by', $user->id)
        ->whereNotNull('period_start')
        ->whereNotNull('period_end')
        ->orderByDesc('period_start')
        ->orderByDesc('id')
        ->first();
    
    $startDate = $latestBatch ? Carbon::parse($latestBatch->period_start)->startOfDay() : Carbon::now()->startOfWeek();
    $endDate = $latestBatch ? Carbon::parse($latestBatch->period_end)->startOfDay() : Carbon::now()->endOfWeek();
    
    echo "Dashboard period: " . $startDate->format('Y-m-d') . " to " . $endDate->format('Y-m-d') . PHP_EOL;
    
    $creatorLatestIds = CreatorScore::query()
        ->whereHas('creator', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('period_start', '>=', $startDate->toDateString())
        ->where('period_end', '<=', $endDate->toDateString())
        ->selectRaw('MAX(id) as id')
        ->groupBy('creator_id')
        ->pluck('id');
    
    echo "Matching scores count: " . $creatorLatestIds->count() . PHP_EOL;
    print_r($creatorLatestIds->toArray());
}
