<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CreatorCampaignPerformance;
use App\Models\CreatorCampaignPerformanceHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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

        if ($campaign->performances()->exists()) {
            return redirect()
                ->route('campaigns.show', $campaign)
                ->with('error', 'Campaign ini sudah memiliki current performance. Gunakan Edit Performance atau tambah snapshot dari Performance History.');
        }

        $validated = $request->validate([
            'performance_date' => ['required', 'date'],
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
        $saves = $validated['saves'];
        $clicks = $validated['clicks'];
        $orders = $validated['orders'];
        $buyers = $validated['buyers'];
        $gmv = (float) $validated['gmv'];
        $agreedPrice = (float) $campaign->agreed_price;
        $performanceDate = $validated['performance_date'];

        /*
        |--------------------------------------------------------------------------
        | Engagement Rate
        |--------------------------------------------------------------------------
        */

        $engagementRate = $views > 0
            ? (($likes + $comments + $shares + $saves) / $views) * 100
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Conversion Rate
        |--------------------------------------------------------------------------
        */

        $conversionRate = $clicks > 0
            ? ($orders / $clicks) * 100
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Cost Per View
        |--------------------------------------------------------------------------
        */

        $costPerView = $views > 0
            ? $agreedPrice / $views
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Cost Per Order
        |--------------------------------------------------------------------------
        */

        $costPerOrder = $orders > 0
            ? $agreedPrice / $orders
            : 0;

        /*
        |--------------------------------------------------------------------------
        | ROAS
        |--------------------------------------------------------------------------
        |
        | ROAS = GMV / Campaign Cost
        |
        */

        $roas = $agreedPrice > 0
            ? $gmv / $agreedPrice
            : 0;

        /*
        |--------------------------------------------------------------------------
        | ROI
        |--------------------------------------------------------------------------
        |
        | GMV-based ROI
        |
        | ROI = ((GMV - Campaign Cost) / Campaign Cost) × 100
        |
        */

        $roi = $agreedPrice > 0
            ? (($gmv - $agreedPrice) / $agreedPrice) * 100
            : 0;

        
        
        DB::transaction(function () use (
            $campaign,
            $performanceDate,
            $views,
            $likes,
            $comments,
            $shares,
            $saves,
            $clicks,
            $orders,
            $buyers,
            $gmv,
            $engagementRate,
            $conversionRate,
            $costPerView,
            $costPerOrder,
            $roas,
            $roi
        ) {
            $metrics = [
                'views' => $views,
                'likes' => $likes,
                'comments' => $comments,
                'shares' => $shares,
                'saves' => $saves,
                'clicks' => $clicks,
                'orders' => $orders,
                'buyers' => $buyers,
                'gmv' => $gmv,
                'engagement_rate' => $engagementRate,
                'conversion_rate' => $conversionRate,
                'cost_per_view' => $costPerView,
                'cost_per_order' => $costPerOrder,
                'roas' => $roas,
                'roi' => $roi,
            ];

            CreatorCampaignPerformance::create(array_merge([
                'campaign_id' => $campaign->id,
                'performance_date' => $performanceDate,
            ], $metrics));

            CreatorCampaignPerformanceHistory::updateOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'performance_date' => $performanceDate,
                ],
                $metrics
            );
        });



        return redirect()
            ->route('campaigns.show', $campaign)
            ->with(
                'success',
                'Actual campaign performance berhasil disimpan.'
            );
    }

    public function edit(
        Campaign $campaign,
        CreatorCampaignPerformance $performance
    ) {
        abort_unless(
            $performance->campaign_id === $campaign->id,
            404
        );

        $campaign->load('creator');

        abort_unless(
            $campaign->creator->user_id === Auth::id(),
            404
        );

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
        abort_unless(
            $performance->campaign_id === $campaign->id,
            404
        );

        $campaign->load('creator');

        abort_unless(
            $campaign->creator->user_id === Auth::id(),
            404
        );

        $validated = $request->validate([
            'performance_date' => ['required', 'date'],
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

        $performanceDate = $validated['performance_date'];

        $views = $validated['views'];
        $likes = $validated['likes'];
        $comments = $validated['comments'];
        $shares = $validated['shares'];
        $saves = $validated['saves'];
        $clicks = $validated['clicks'];
        $orders = $validated['orders'];
        $buyers = $validated['buyers'];
        $gmv = (float) $validated['gmv'];

        $agreedPrice = (float) $campaign->agreed_price;

        // Engagement Rate
        $engagementRate = $views > 0
            ? (($likes + $comments + $shares + $saves) / $views) * 100
            : 0;

        // Conversion Rate
        $conversionRate = $clicks > 0
            ? ($orders / $clicks) * 100
            : 0;

        // Cost Per View
        $costPerView = $views > 0
            ? $agreedPrice / $views
            : 0;

        // Cost Per Order
        $costPerOrder = $orders > 0
            ? $agreedPrice / $orders
            : 0;

        // ROAS
        $roas = $agreedPrice > 0
            ? $gmv / $agreedPrice
            : 0;

        // ROI
        $roi = $agreedPrice > 0
            ? (($gmv - $agreedPrice) / $agreedPrice) * 100
            : 0;

        $metrics = [
            'views' => $views,
            'likes' => $likes,
            'comments' => $comments,
            'shares' => $shares,
            'saves' => $saves,
            'clicks' => $clicks,
            'orders' => $orders,
            'buyers' => $buyers,
            'gmv' => $gmv,
            'engagement_rate' => $engagementRate,
            'conversion_rate' => $conversionRate,
            'cost_per_view' => $costPerView,
            'cost_per_order' => $costPerOrder,
            'roas' => $roas,
            'roi' => $roi,
        ];

        // Ambil tanggal lama sebelum record diperbarui.
        $oldPerformanceDate = $performance->getRawOriginal(
            'performance_date'
        );

        
        DB::transaction(function () use (
            $campaign,
            $performance,
            $performanceDate,
            $oldPerformanceDate,
            $metrics,
        ) {
            // Cek apakah sudah ada snapshot pada tanggal tujuan.
            $targetHistory = CreatorCampaignPerformanceHistory::query()
                ->where('campaign_id', $campaign->id)
                ->whereDate('performance_date', $performanceDate)
                ->lockForUpdate()
                ->first();

            // Jika tanggal berubah dan sudah dipakai snapshot lain,
            // jangan menimpa snapshot yang sudah ada.
            if (
                $oldPerformanceDate !== null
                && $oldPerformanceDate !== $performanceDate
                && $targetHistory
            ) {
                throw ValidationException::withMessages([
                    'performance_date' =>
                        'Tanggal tersebut sudah memiliki snapshot untuk campaign ini. Pilih tanggal lain.',
                ]);
            }

            // Simpan atau perbarui histori berdasarkan tanggal tujuan.
            CreatorCampaignPerformanceHistory::updateOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'performance_date' => $performanceDate,
                ],
                $metrics
            );

            // Current performance tetap diperbarui sebagai data terkini.
            $performance->update(array_merge(
                [
                    'performance_date' => $performanceDate,
                ],
                $metrics
            ));
        });


        return redirect()
            ->route('campaigns.show', $campaign)
            ->with(
                'success',
                'Actual campaign performance dan snapshot date '
                . 'berhasil diperbarui.'
            );
    }

}