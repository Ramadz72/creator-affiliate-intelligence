<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Creator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CampaignController extends Controller
{
    /**
     * Menampilkan daftar campaign.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $status = trim((string) $request->input('status', ''));
        $platform = trim((string) $request->input('platform', ''));
        $sort = (string) $request->input('sort', 'latest');

        $allowedStatuses = [
            'planned',
            'running',
            'completed',
            'cancelled',
        ];

        $allowedSorts = [
            'latest',
            'oldest',
            'price_high',
            'price_low',
            'name',
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'latest';
        }

        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        */

        $baseQuery = Campaign::query()
            ->whereHas('creator', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('campaign_name', 'like', "%{$search}%")
                        ->orWhere('product_name', 'like', "%{$search}%")
                        ->orWhere('platform', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('creator', function ($query) use ($search) {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($platform !== '', function ($query) use ($platform) {
                $query->where('platform', $platform);
            });

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statsQuery = clone $baseQuery;

        $stats = [
            'total' => (clone $statsQuery)->count(),

            'running' => (clone $statsQuery)
                ->where('status', 'running')
                ->count(),

            'planned' => (clone $statsQuery)
                ->where('status', 'planned')
                ->count(),

            'completed' => (clone $statsQuery)
                ->where('status', 'completed')
                ->count(),

            'cancelled' => (clone $statsQuery)
                ->where('status', 'cancelled')
                ->count(),

            'total_deal_value' => (float) (clone $statsQuery)
                ->sum('agreed_price'),
        ];

        /*
        |--------------------------------------------------------------------------
        | Platform Options
        |--------------------------------------------------------------------------
        */

        $platforms = Campaign::query()
            ->whereHas('creator', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->whereNotNull('platform')
            ->where('platform', '!=', '')
            ->distinct()
            ->orderBy('platform')
            ->pluck('platform')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $campaignQuery = $baseQuery
            ->with('creator:id,name,username,profile_image');

        switch ($sort) {
            case 'oldest':
                $campaignQuery
                    ->orderBy('id')
                    ->orderBy('start_date');
                break;

            case 'price_high':
                $campaignQuery
                    ->orderByDesc('agreed_price')
                    ->orderByDesc('id');
                break;

            case 'price_low':
                $campaignQuery
                    ->orderBy('agreed_price')
                    ->orderByDesc('id');
                break;

            case 'name':
                $campaignQuery
                    ->orderBy('campaign_name')
                    ->orderByDesc('id');
                break;

            default:
                $campaignQuery
                    ->latest('id');
                break;
        }

        $campaigns = $campaignQuery
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('campaigns/Index', [
            'campaigns' => $campaigns,
            'search' => $search,
            'status' => $status,
            'platform' => $platform,
            'sort' => $sort,
            'platforms' => $platforms,
            'stats' => $stats,
        ]);
    }

    /**
     * Form tambah campaign.
     */
    public function create()
    {
        $creators = Creator::where('user_id', Auth::id())
            ->where('status', 'active')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'username',
                'platform',
                'profile_image',
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
            'creator_id' => [
                'required',
                'exists:creators,id,user_id,' . Auth::id(),
            ],
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
        abort_unless($campaign->creator->user_id === Auth::id(), 404);

        $campaign->load([
            'creator',
            'performances',
            'performanceHistories' => function ($query) {
                $query
                    ->orderBy('performance_date')
                    ->orderBy('id');
            },
        ]);

        return Inertia::render('campaigns/Show', [
            'campaign' => $campaign,
        ]);
    }

    /**
     * Form edit campaign.
     */
    public function edit(Campaign $campaign)
    {
        abort_unless($campaign->creator->user_id === Auth::id(), 404);
       
        $creators = Creator::where('user_id', Auth::id())
            ->where('status', 'active')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'username',
                'platform',
                'profile_image'
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

        abort_unless($campaign->creator->user_id === Auth::id(), 404);

        $validated = $request->validate([
            'creator_id' => [
                'required',
                'exists:creators,id,user_id,' . Auth::id(),
            ],
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


        // Recalculate current performance and all historical snapshots
        // because Cost/View, Cost/Order, ROAS, and ROI depend on agreed price.
        DB::transaction(function () use ($campaign) {
        $campaign->load('performances');

        foreach ($campaign->performances as $performance) {
            $agreedPrice = (float) $campaign->agreed_price;
            $views = (int) $performance->views;
            $orders = (int) $performance->orders;
            $gmv = (float) $performance->gmv;

            $performance->cost_per_view = $views > 0
                ? $agreedPrice / $views
                : 0;

            $performance->cost_per_order = $orders > 0
                ? $agreedPrice / $orders
                : 0;

            $performance->roas = $agreedPrice > 0
                ? $gmv / $agreedPrice
                : 0;

            $performance->roi = $agreedPrice > 0
                ? (($gmv - $agreedPrice) / $agreedPrice) * 100
                : 0;

            $performance->save();
        }

        $histories = \App\Models\CreatorCampaignPerformanceHistory::query()
            ->where('campaign_id', $campaign->id)
            ->get();

        foreach ($histories as $history) {
            $agreedPrice = (float) $campaign->agreed_price;
            $views = (int) $history->views;
            $orders = (int) $history->orders;
            $gmv = (float) $history->gmv;

            $history->cost_per_view = $views > 0
                ? $agreedPrice / $views
                : 0;

            $history->cost_per_order = $orders > 0
                ? $agreedPrice / $orders
                : 0;

            $history->roas = $agreedPrice > 0
                ? $gmv / $agreedPrice
                : 0;

            $history->roi = $agreedPrice > 0
                ? (($gmv - $agreedPrice) / $agreedPrice) * 100
                : 0;

            $history->save();
        }
    });

        return redirect()
            ->route('campaigns.show', $campaign)
            ->with('success', 'Campaign berhasil diperbarui.');
    }

    /**
     * Menghapus campaign.
     */
    public function destroy(Campaign $campaign)
    {
        abort_unless($campaign->creator->user_id === Auth::id(), 404);
        $campaign->delete();

        return redirect()
            ->route('campaigns.index')
            ->with('success', 'Campaign berhasil dihapus.');
    }
}