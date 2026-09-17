<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use App\Models\CreatorContent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreatorContentController extends Controller
{
    public function create(Creator $creator)
    {
        return Inertia::render('creators/contents/Create', [
            'creator' => $creator,
        ]);
    }
    public function store(Request $request, Creator $creator)
    {
        $validated = $request->validate([
            'content_date' => ['required', 'date'],
            'content_url' => ['nullable', 'url', 'max:500'],
            'content_type' => ['nullable', 'string', 'max:50'],
            'views' => ['required', 'integer', 'min:0'],
            'likes' => ['required', 'integer', 'min:0'],
            'comments' => ['required', 'integer', 'min:0'],
            'shares' => ['required', 'integer', 'min:0'],
        ]);

        $creator->contents()->create($validated);

        return redirect()
            ->route('creators.show', $creator)
            ->with('success', 'Konten berhasil ditambahkan.');
    }

    public function destroy(
        Creator $creator,
        CreatorContent $content
    ) {
        if ($content->creator_id !== $creator->id) {
            abort(404);
        }

        $content->delete();

        return redirect()
            ->route('creators.show', $creator)
            ->with('success', 'Konten berhasil dihapus.');
    }
}