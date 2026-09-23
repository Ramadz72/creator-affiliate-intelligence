<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CreatorCampaignPerformance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CreatorCampaignPerformanceController
{
    public function create(Campaign $campaign)
    {
        $campaign->load('creator');

        abort_unless($campaign->creator->user_id === Auth::id(), 404);

        return Inertia::render('campaigns/PerformanceCreate', [
            'campaign' => $campaign,
        ]);
    }

    public function store(Request $request, Campaign $campaign)
    {
        $campaign->load('creator');

        abort_unless($campaign->creator->user_id === Auth::id(), 404);

        $validated = $request->validate([
            'views' => ['required', 'integer', 'min:0'],
            'likes' => ['required', 'integer', 'min:0'],
            'comments' => ['required', 'integer', 'min:0'],
            'shares' => ['required', 'integer', 'min:0'],
            'saves' => ['required', 'integer', 'min:0'],
            'clicks' => ['required', 'integer', 'min:0'],
            'orders' => ['required', 'integer', 'min:0'],
            'buyers' => ['required', 'integer', 'min:0'],
            'gmv' => ['required', 'numeric', 'min:0'],
        ]);

        $views = $validated['views'];
        $likes = $validated['likes'];
        $comments = $validated['comments'];
        $shares = $validated['shares'];
        $clicks = $validated['clicks'];
        $orders = $validated['orders'];
        $gmv = $validated['gmv'];

        // Engagement Rate
        $engagementRate = $views > 0
            ? (($likes + $comments + $shares) / $views) * 100
            : 0;

        // Conversion Rate
        $conversionRate = $clicks > 0
            ? ($orders / $clicks) * 100
            : 0;

        // Cost per View
        $costPerView = $views > 0
            ? $campaign->agreed_price / $views
            : 0;

        // Cost per Order
        $costPerOrder = $orders > 0
            ? $campaign->agreed_price / $orders
            : 0;

        // ROI
        $roi = $campaign->agreed_price > 0
            ? ($gmv / $campaign->agreed_price)
            : 0;

        CreatorCampaignPerformance::create([
            'campaign_id' => $campaign->id,
            'views' => $views,
            'likes' => $likes,
            'comments' => $comments,
            'shares' => $shares,
            'saves' => $validated['saves'],
            'clicks' => $clicks,
            'orders' => $orders,
            'buyers' => $validated['buyers'],
            'gmv' => $gmv,
            'engagement_rate' => $engagementRate,
            'conversion_rate' => $conversionRate,
            'cost_per_view' => $costPerView,
            'cost_per_order' => $costPerOrder,
            'roi' => $roi,
        ]);

        return redirect()
            ->route('campaigns.show', $campaign)
            ->with('success', 'Actual campaign performance berhasil disimpan.');
    }

    public function edit(Campaign $campaign, CreatorCampaignPerformance $performance)
    {
        abort_unless($performance->campaign_id === $campaign->id, 404);

        $campaign->load('creator');

        abort_unless($campaign->creator->user_id === Auth::id(), 404);

        return Inertia::render('campaigns/PerformanceEdit', [
            'campaign' => $campaign,
            'performance' => $performance,
        ]);
    }

    public function update(
    Request $request,
    Campaign $campaign,
    CreatorCampaignPerformance $performance
    ) {
        abort_unless($performance->campaign_id === $campaign->id, 404);

        $campaign->load('creator');
        
        abort_unless($campaign->creator->user_id === Auth::id(), 404);

        $validated = $request->validate([
            'views' => ['required', 'integer', 'min:0'],
            'likes' => ['required', 'integer', 'min:0'],
            'comments' => ['required', 'integer', 'min:0'],
            'shares' => ['required', 'integer', 'min:0'],
            'saves' => ['required', 'integer', 'min:0'],
            'clicks' => ['required', 'integer', 'min:0'],
            'orders' => ['required', 'integer', 'min:0'],
            'buyers' => ['required', 'integer', 'min:0'],
            'gmv' => ['required', 'numeric', 'min:0'],
        ]);

        $views = $validated['views'];
        $likes = $validated['likes'];
        $comments = $validated['comments'];
        $shares = $validated['shares'];
        $clicks = $validated['clicks'];
        $orders = $validated['orders'];
        $gmv = $validated['gmv'];

        // Engagement Rate
        $engagementRate = $views > 0
            ? (($likes + $comments + $shares) / $views) * 100
            : 0;

        // Conversion Rate
        $conversionRate = $clicks > 0
            ? ($orders / $clicks) * 100
            : 0;

        // Cost per View
        $costPerView = $views > 0
            ? $campaign->agreed_price / $views
            : 0;

        // Cost per Order
        $costPerOrder = $orders > 0
            ? $campaign->agreed_price / $orders
            : 0;

        // ROAS
        $roas = $campaign->agreed_price > 0
            ? $gmv / $campaign->agreed_price
            : 0;

        $performance->update([
            'views' => $views,
            'likes' => $likes,
            'comments' => $comments,
            'shares' => $shares,
            'saves' => $validated['saves'],
            'clicks' => $clicks,
            'orders' => $orders,
            'buyers' => $validated['buyers'],
            'gmv' => $gmv,
            'engagement_rate' => $engagementRate,
            'conversion_rate' => $conversionRate,
            'cost_per_view' => $costPerView,
            'cost_per_order' => $costPerOrder,
            'roi' => $roas,
        ]);

        return redirect()
            ->route('campaigns.show', $campaign)
            ->with('success', 'Actual campaign performance berhasil diperbarui.');
    }
}