<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use Inertia\Inertia;

class CreatorController extends Controller
{
    public function index()
    {
        $creators = Creator::latest()->get();

        return Inertia::render('creators/Index', [
            'creators' => $creators,
        ]);
    }
}