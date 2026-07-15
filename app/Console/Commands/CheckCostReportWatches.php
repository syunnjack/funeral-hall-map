<?php

namespace App\Console\Commands;

use App\Models\CostReport;
use App\Models\Favorite;
use App\Support\LineMessaging;
use Illuminate\Console\Command;

class CheckCostReportWatches extends Command
{
    protected $signature = 'costs:check-watches';

    protected $description = 'ウォッチ登録された葬儀会館に新しい費用の口コミが投稿されていないか確認し、LINEで通知する';

    public function handle(): int
    {
        $favorites = Favorite::with('lineUser')->get();

        foreach ($favorites as $favorite) {
            if (! $favorite->lineUser) {
                continue;
            }

            $since = $favorite->last_checked_at ?? $favorite->created_at;
            $newReports = CostReport::where('venue_id', $favorite->venue_id)
                ->where('created_at', '>', $since)
                ->get();

            if ($newReports->isNotEmpty()) {
                $latest = $newReports->first();
                $favorite->loadMissing('venue');
                LineMessaging::push(
                    $favorite->lineUser->line_user_id,
                    "「{$favorite->venue->name}」の新しい費用の口コミが投稿されました: " . number_format($latest->total_cost) . '円'
                    . ($latest->funeral_type ? "（{$latest->funeral_type}）" : '')
                );

                // last_checked_atは「実際に検知した最新レポートの時刻」まで進める。
                // now()まで無条件に進めると、チェック実行と同一秒内に投稿されたレポートが
                // 次回以降も since より前の扱いとなり永久に検知漏れになるため。
                $favorite->update(['last_checked_at' => $newReports->max('created_at')]);
            }
        }

        return self::SUCCESS;
    }
}
