<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Creator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Recalculate actual campaign performance
        // karena Cost/View, Cost/Order, dan ROI
        // bergantung pada Agreed Price.
        $campaign->load('performances');

        foreach ($campaign->performances as $performance) {
            $performance->cost_per_view = $performance->views > 0
                ? $campaign->agreed_price / $performance->views
                : 0;

            $performance->cost_per_order = $performance->orders > 0
                ? $campaign->agreed_price / $performance->orders
                : 0;

            $performance->roas = $campaign->agreed_price > 0
                ? $performance->gmv / $campaign->agreed_price
                : 0;

            $performance->roi = $campaign->agreed_price > 0
                ? (($performance->gmv - $campaign->agreed_price) / $campaign->agreed_price) * 100
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
        abort_unless($campaign->creator->user_id === Auth::id(), 404);
        $campaign->delete();

        return redirect()
            ->route('campaigns.index')
            ->with('success', 'Campaign berhasil dihapus.');
    }
}