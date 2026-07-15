<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Support\ContentModeration;
use Illuminate\Http\Request;

class CostReportController extends Controller
{
    public function store(Request $request, Venue $venue)
    {
        if (! empty($request->input('website'))) {
            return back()->with('success', '投稿を受け付けました。');
        }

        $validated = $request->validate([
            'funeral_type' => 'nullable|string|max:20',
            'attendee_count' => 'nullable|integer|min:0|max:9999',
            'total_cost' => 'required|integer|min:1000|max:100000000',
            'comment' => 'nullable|string|max:1000',
            'nickname' => 'nullable|string|max:30',
        ]);

        if (! empty($validated['comment']) && ContentModeration::containsNgWord($validated['comment'])) {
            return back()->withErrors(['comment' => '投稿内容に使用できない文字列が含まれています。'])->withInput();
        }

        $ipHash = ContentModeration::clientIpHash($request);
        if (ContentModeration::isTooSoon("cost-report:{$venue->id}:{$ipHash}", 30)) {
            return back()->withErrors(['total_cost' => '投稿間隔が短すぎます。しばらく待ってから再度お試しください。'])->withInput();
        }

        $venue->costReports()->create([
            'funeral_type' => $validated['funeral_type'] ?? null,
            'attendee_count' => $validated['attendee_count'] ?? null,
            'total_cost' => $validated['total_cost'],
            'comment' => $validated['comment'] ?? null,
            'nickname' => ($validated['nickname'] ?? '') !== '' ? $validated['nickname'] : '匿名',
            'ip_hash' => $ipHash,
        ]);

        return back()->with('success', '費用の口コミを投稿しました。ありがとうございます。');
    }
}
