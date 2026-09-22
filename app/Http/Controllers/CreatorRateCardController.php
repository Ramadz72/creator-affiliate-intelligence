<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use App\Models\CreatorRateCard;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreatorRateCardController extends Controller
{
    public function create(Creator $creator)
    {
        return Inertia::render('creators/rate-cards/Create', [
            'creator' => $creator,
        ]);
    }

    public function store(Request $request, Creator $creator)
    {
        $validated = $request->validate([
            'platform' => ['required', 'string', 'max:50'],
            'deliverable' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration' => ['nullable', 'string', 'max:50'],
            'revision' => ['required', 'integer', 'min:0'],
            'usage_rights' => ['nullable', 'string', 'max:255'],
            'valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $creator->rateCards()->create($validated);

        return redirect()
            ->route('creators.show', $creator)
            ->with('success', 'Rate card berhasil ditambahkan.');
    }

    public function destroy(
        Creator $creator,
        CreatorRateCard $rateCard
    ) {
        if ($rateCard->creator_id !== $creator->id) {
            abort(404);
        }

        $rateCard->delete();

        return redirect()
            ->route('creators.show', $creator)
            ->with('success', 'Rate card berhasil dihapus.');
    }
}