<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CreatorController extends Controller
{
    /**
     * Menampilkan daftar Creator.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));

        $creators = Creator::query()
            ->where('user_id', Auth::id())
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return Inertia::render('creators/Index', [
            'creators' => $creators,
            'search' => $search,
        ]);
    }

    /**
     * Menampilkan form tambah Creator.
     */
    public function create()
    {
        return Inertia::render('creators/Create');
    }

    /**
     * Menyimpan Creator baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:100'],
            'platform' => ['required', 'string', 'max:50'],
            'category' => ['required', 'string', 'max:100'],
            'followers' => ['required', 'integer', 'min:0'],
            'audience_gender' => ['nullable', 'array'],
            'audience_age' => ['nullable', 'array'],
            'audience_location' => ['nullable', 'array'],
            'profile_link' => ['nullable', 'string', 'max:500'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request
                ->file('profile_image')
                ->store('creator-profiles', 'public');
        }

        $validated['user_id'] = Auth::id();
        
        Creator::create($validated);

        return redirect()
            ->route('creators.index')
            ->with('success', 'Creator berhasil ditambahkan.');}
    public function show(Creator $creator)
    {
        abort_unless($creator->user_id === Auth::id(), 404);

        $contents = $creator->contents()
            ->latest('content_date')
            ->latest('id')
            ->limit(7)
            ->get();

        $rateCards = $creator->rateCards()
            ->latest('id')
            ->get();

        return Inertia::render('creators/Show', [
            'creator' => $creator,
            'contents' => $contents,
            'rateCards' => $rateCards,
        ]);
    }

    public function edit(Creator $creator)
    {
        abort_unless($creator->user_id === Auth::id(), 404);

        return Inertia::render('creators/Edit', [
            'creator' => $creator,
        ]);
    }

    public function update(Request $request, Creator $creator)
    {
        abort_unless($creator->user_id === Auth::id(), 404);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:100'],
            'platform' => ['required', 'string', 'max:50'],
            'category' => ['required', 'string', 'max:100'],
            'followers' => ['required', 'integer', 'min:0'],
            'audience_gender' => ['nullable', 'string'],
            'audience_age' => ['nullable', 'string'],
            'audience_location' => ['nullable', 'string'],
            'profile_link' => ['nullable', 'string', 'max:500'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['audience_gender'] = $validated['audience_gender']
            ? [$validated['audience_gender']]
            : null;

        $validated['audience_age'] = $validated['audience_age']
            ? [$validated['audience_age']]
            : null;

        $validated['audience_location'] = $validated['audience_location']
            ? [$validated['audience_location']]
            : null;
        if ($request->hasFile('profile_image')) {
        if ($creator->profile_image) {
            Storage::disk('public')->delete($creator->profile_image);
        }

        $validated['profile_image'] = $request
            ->file('profile_image')
            ->store('creator-profiles', 'public');
    }
        $creator->update($validated);

        return redirect()
            ->route('creators.index')
            ->with('success', 'Creator berhasil diperbarui.');
    }

    public function destroy(Creator $creator)
    {   
        abort_unless($creator->user_id === Auth::id(), 404);

        $creator->delete();

        return redirect()
            ->route('creators.index')
            ->with('success', 'Creator berhasil dihapus.');
    }
}