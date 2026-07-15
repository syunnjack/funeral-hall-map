<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Support\ContentModeration;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function index(Request $request)
    {
        $query = Venue::query()->withAvg('costReports', 'total_cost');

        if ($request->filled('area')) {
            $query->where('area', $request->input('area'));
        }

        $venues = $query->latest()->get();
        $areas = Venue::query()->whereNotNull('area')->distinct()->pluck('area');

        return view('venues.index', compact('venues', 'areas'));
    }

    public function create()
    {
        return view('venues.create');
    }

    public function store(Request $request)
    {
        if (! empty($request->input('website'))) {
            return redirect()->route('venues.thanks');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'area' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        if (ContentModeration::containsNgWord($validated['name'] . ' ' . ($validated['description'] ?? ''))) {
            return back()->withErrors(['name' => '投稿内容に使用できない文字列が含まれています。'])->withInput();
        }

        $ipHash = ContentModeration::clientIpHash($request);
        if (ContentModeration::isTooSoon("venue-create:{$ipHash}", 30)) {
            return back()->withErrors(['name' => '投稿間隔が短すぎます。しばらく待ってから再度お試しください。'])->withInput();
        }

        Venue::create($validated);

        return redirect()->route('venues.thanks');
    }

    public function show(Venue $venue)
    {
        $venue->load(['reviews' => fn ($q) => $q->latest()]);
        $venue->load(['costReports' => fn ($q) => $q->latest()]);

        $isWatching = session('line_user_local_id')
            ? $venue->favorites()->where('line_user_id', session('line_user_local_id'))->exists()
            : false;

        $hasRequestedDocument = session('line_user_local_id')
            ? $venue->documentRequests()->where('line_user_id', session('line_user_local_id'))->exists()
            : false;

        $averageCost = $venue->costReports->isNotEmpty()
            ? (int) round($venue->costReports->avg('total_cost'))
            : null;

        return view('venues.show', compact('venue', 'isWatching', 'hasRequestedDocument', 'averageCost'));
    }

    public function like(Request $request, Venue $venue)
    {
        $ipHash = ContentModeration::clientIpHash($request);
        if (ContentModeration::isTooSoon("like:{$venue->id}:{$ipHash}", 60)) {
            return response()->json(['error' => 'いいね！は少し時間を空けてから再度お試しください。'], 429);
        }

        $venue->increment('likes_count');
        $venue->refresh();

        return response()->json(['likes_count' => $venue->likes_count]);
    }

    public function sitemap()
    {
        $venues = Venue::select('id', 'updated_at')->get();
        $xml = view('sitemap', compact('venues'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
