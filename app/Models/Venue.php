<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    /** 都道府県ページのURLに使うローマ字。 */
    public const AREA_SLUGS = [
        '北海道' => 'hokkaido', '青森県' => 'aomori', '岩手県' => 'iwate', '宮城県' => 'miyagi',
        '秋田県' => 'akita', '山形県' => 'yamagata', '福島県' => 'fukushima', '茨城県' => 'ibaraki',
        '栃木県' => 'tochigi', '群馬県' => 'gunma', '埼玉県' => 'saitama', '千葉県' => 'chiba',
        '東京都' => 'tokyo', '神奈川県' => 'kanagawa', '新潟県' => 'niigata', '富山県' => 'toyama',
        '石川県' => 'ishikawa', '福井県' => 'fukui', '山梨県' => 'yamanashi', '長野県' => 'nagano',
        '岐阜県' => 'gifu', '静岡県' => 'shizuoka', '愛知県' => 'aichi', '三重県' => 'mie',
        '滋賀県' => 'shiga', '京都府' => 'kyoto', '大阪府' => 'osaka', '兵庫県' => 'hyogo',
        '奈良県' => 'nara', '和歌山県' => 'wakayama', '鳥取県' => 'tottori', '島根県' => 'shimane',
        '岡山県' => 'okayama', '広島県' => 'hiroshima', '山口県' => 'yamaguchi', '徳島県' => 'tokushima',
        '香川県' => 'kagawa', '愛媛県' => 'ehime', '高知県' => 'kochi', '福岡県' => 'fukuoka',
        '佐賀県' => 'saga', '長崎県' => 'nagasaki', '熊本県' => 'kumamoto', '大分県' => 'oita',
        '宮崎県' => 'miyazaki', '鹿児島県' => 'kagoshima', '沖縄県' => 'okinawa',
    ];

    /** 施設種別ページのURLに使うローマ字。 */
    public const TYPE_SLUGS = [
        '葬儀社' => 'sougisha',
        '斎場・火葬場' => 'saijo',
    ];

    public static function slugForArea(?string $area): ?string
    {
        return $area === null ? null : (self::AREA_SLUGS[$area] ?? null);
    }

    public static function areaForSlug(string $slug): ?string
    {
        return array_search($slug, self::AREA_SLUGS, true) ?: null;
    }

    public static function slugForType(?string $type): ?string
    {
        return $type === null ? null : (self::TYPE_SLUGS[$type] ?? null);
    }

    public static function typeForSlug(string $slug): ?string
    {
        return array_search($slug, self::TYPE_SLUGS, true) ?: null;
    }

    public function getAreaSlugAttribute(): ?string
    {
        return self::slugForArea($this->area);
    }

    public function getTypeSlugAttribute(): ?string
    {
        return self::slugForType($this->facility_type);
    }

    /** OpenStreetMap から取り込んだ施設か（＝利用者の投稿ではないか）。 */
    public function getIsFromOsmAttribute(): bool
    {
        return $this->source === 'openstreetmap';
    }

    protected $fillable = [
        'name',
        'description',
        'facility_type',
        'area',
        'address',
        'phone',
        'website',
        'opening_hours',
        'lat',
        'lng',
        'likes_count',
        'source',
        'source_ref',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
        ];
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function documentRequests()
    {
        return $this->hasMany(DocumentRequest::class);
    }

    public function costReports()
    {
        return $this->hasMany(CostReport::class);
    }
}
