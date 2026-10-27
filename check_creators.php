<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Creator;
use App\Models\CreatorScore;

$user = User::where("email", "ramadaniilham72@gmail.com")->first();
if ($user) {
    echo "User: " . $user->name . PHP_EOL;
    echo "Creators: " . Creator::where("user_id", $user->id)->count() . PHP_EOL;
    $scores = CreatorScore::whereHas("creator", function($q) use ($user) {
        $q->where("user_id", $user->id);
    })->get();
    echo "Scores: " . $scores->count() . PHP_EOL;
    foreach ($scores as $s) {
        echo " - id:" . $s->id . " c:" . $s->creator_id . " " . $s->period_start . " to " . $s->period_end . " score:" . $s->overall_score . PHP_EOL;
    }
}
