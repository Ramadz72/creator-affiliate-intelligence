<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Creator;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CampaignController extends Controller
{
    /**
     * Menampilkan daftar campaign.
     */
    public function index()
    {
        $campaigns = Campaign::with('creator')
            ->latest('id')
            ->get();

        return Inertia::render('campaigns/Index', [
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Form tambah campaign.
     */
    public function create()
    {
        $creators = Creator::where('status', 'active')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'username',
                'platform',
            ]);

        return Inertia::render('campaigns/Create', [
            'creators' => $creators,
        ]);
    }

    /**
     * Menyimpan campaign baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'creator_id' => ['required', 'exists:creators,id'],
            'campaign_name' => ['required', 'string', 'max:150'],
            'product_name' => ['required', 'string', 'max:150'],
            'platform' => ['required', 'string', 'max:50'],
            'deliverable' => ['required', 'string', 'max:100'],
            'agreed_price' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:planned,running,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['created_at'] = now();

        Campaign::create($validated);

        return redirect()
            ->route('campaigns.index')
            ->with('success', 'Campaign berhasil dibuat.');
    }

    /**
     * Menampilkan detail campaign.
     */
    public function show(Campaign $campaign)
    {
        $campaign->load('creator', 'performances');

        return Inertia::render('campaigns/Show', [
            'campaign' => $campaign,
        ]);
    }

    /**
     * Form edit campaign.
     */
    public function edit(Campaign $campaign)
    {
        $creators = Creator::where('status', 'active')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'username',
                'platform',
            ]);

        return Inertia::render('campaigns/Edit', [
            'campaign' => $campaign,
            'creators' => $creators,
        ]);
    }

    /**
     * Memperbarui campaign.
     */
    public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'creator_id' => ['required', 'exists:creators,id'],
            'campaign_name' => ['required', 'string', 'max:150'],
            'product_name' => ['required', 'string', 'max:150'],
            'platform' => ['required', 'string', 'max:50'],
            'deliverable' => ['required', 'string', 'max:100'],
            'agreed_price' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:planned,running,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        $campaign->update($validated);

        // Recalculate actual campaign performance
        // karena Cost/View, Cost/Order, dan ROAS
        // bergantung pada Agreed Price.
        $campaign->load('performances');

        foreach ($campaign->performances as $performance) {
            $performance->cost_per_view = $performance->views > 0
                ? $campaign->agreed_price / $performance->views
                : 0;

            $performance->cost_per_order = $performance->orders > 0
                ? $campaign->agreed_price / $performance->orders
                : 0;

            $performance->roi = $campaign->agreed_price > 0
                ? $performance->gmv / $campaign->agreed_price
                : 0;

            $performance->save();
        }

        return redirect()
            ->route('campaigns.show', $campaign)
            ->with('success', 'Campaign berhasil diperbarui.');
    }

    /**
     * Menghapus campaign.
     */
    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return redirect()
            ->route('campaigns.index')
            ->with('success', 'Campaign berhasil dihapus.');
    }
}