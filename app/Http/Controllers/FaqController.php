<?php

namespace App\Http\Controllers;

use App\Support\Faq;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    /**
     * Halaman FAQ publik.
     */
    public function index(): Response
    {
        return Inertia::render('FAQ/Index', [
            'faqs' => Faq::items(),
        ]);
    }
}
