<?php

namespace Database\Seeders;

use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * OpenStreetMap から取り出した施設データを取り込む。
 *
 * データは database/data/venues-osm.json に置いてある。
 * 出典は OpenStreetMap（ODbL 1.0）。表示側に「© OpenStreetMap contributors」が要る。
 *
 * 実在する施設だけを入れる。説明文や電話番号など、元データに無いものは
 * 補わずに空のままにする。
 *
 * 3万件を超えるので、1件ずつ updateOrCreate すると数十分かかる。
 * (source, source_ref) の一意制約を使って、まとめて upsert する。
 */
class OsmVenueSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/venues-osm.json');

        if (! is_file($path)) {
            $this->command?->warn("データファイルがありません: {$path}");

            return;
        }

        $rows = json_decode(file_get_contents($path), true);

        if (! is_array($rows)) {
            $this->command?->error('データファイルを読めませんでした。');

            return;
        }

        $before = Venue::where('source', 'openstreetmap')->count();
        $now = now();
        $imported = 0;

        // SQLite は1文あたりのプレースホルダ数に上限がある（古いビルドは999）。
        // 1行13列なので、余裕を見て 900 / 13 = 69 行ずつに区切る。
        $columns = 13;
        $chunkSize = max(1, intdiv(900, $columns));

        // キーは短縮してある:
        //   t=種別(node/way) i=OSMのID n=名前 f=施設種別 a=都道府県
        //   ad=住所 p=電話 w=サイト h=営業時間 lat/lng=座標
        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            $records = [];

            foreach ($chunk as $row) {
                if (empty($row['n']) || ! isset($row['lat'], $row['lng'])) {
                    continue;
                }

                $records[] = [
                    'source' => 'openstreetmap',
                    'source_ref' => "{$row['t']}/{$row['i']}",
                    'name' => $row['n'],
                    'facility_type' => $row['f'] ?? null,
                    'area' => $row['a'] ?? null,
                    'address' => $row['ad'] ?? null,
                    'phone' => $row['p'] ?? null,
                    'website' => $row['w'] ?? null,
                    'opening_hours' => $row['h'] ?? null,
                    'lat' => $row['lat'],
                    'lng' => $row['lng'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($records === []) {
                continue;
            }

            DB::transaction(function () use ($records) {
                Venue::upsert(
                    $records,
                    ['source', 'source_ref'],
                    ['name', 'facility_type', 'area', 'address', 'phone', 'website', 'opening_hours', 'lat', 'lng', 'updated_at'],
                );
            });

            $imported += count($records);
        }

        $after = Venue::where('source', 'openstreetmap')->count();

        $this->command?->info(
            "OpenStreetMap の {$imported} 件を取り込みました（施設数 {$before} → {$after}）。"
        );
    }
}
