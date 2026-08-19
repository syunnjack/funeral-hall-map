<?php

namespace Tests\Feature;

use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AreaPageTest extends TestCase
{
    use RefreshDatabase;

    private function makeVenue(string $area, string $type, string $name): Venue
    {
        return Venue::create([
            'name' => $name,
            'area' => $area,
            'facility_type' => $type,
            'lat' => 35.68,
            'lng' => 139.76,
            'source' => 'openstreetmap',
            'source_ref' => 'node/'.random_int(1, 99999999),
        ]);
    }

    public function test_都道府県ページが掲載件数と施設を表示する(): void
    {
        $this->makeVenue('東京都', '葬儀社', 'テスト葬儀社');

        $this->get('/area/tokyo')
            ->assertOk()
            ->assertSee('テスト葬儀社')
            ->assertSee('東京都の葬儀社・斎場');
    }

    public function test_施設種別ページはその種別だけを表示する(): void
    {
        $this->makeVenue('東京都', '葬儀社', 'テスト葬儀社');
        $this->makeVenue('東京都', '斎場・火葬場', 'テスト斎場');

        $this->get('/area/tokyo/saijo')
            ->assertOk()
            ->assertSee('テスト斎場')
            ->assertDontSee('テスト葬儀社');
    }

    public function test_掲載の無いページと知らないURLは404になる(): void
    {
        $this->makeVenue('東京都', '葬儀社', 'テスト葬儀社');

        $this->get('/area/okinawa')->assertNotFound();
        $this->get('/area/tokyo/saijo')->assertNotFound();
        $this->get('/area/nowhere')->assertNotFound();
        $this->get('/area/tokyo/nowhere')->assertNotFound();
    }

    public function test_旧エリア検索は都道府県ページへ転送される(): void
    {
        $this->makeVenue('東京都', '葬儀社', 'テスト葬儀社');

        $this->get('/?area='.urlencode('東京都'))
            ->assertRedirect(route('venues.area', 'tokyo'));
    }

    public function test_2ページ目は自分自身を正規URLとして申告する(): void
    {
        foreach (range(1, 61) as $i) {
            $this->makeVenue('東京都', '葬儀社', "テスト葬儀社{$i}");
        }

        $this->get('/area/tokyo?page=2')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('venues.area', 'tokyo').'?page=2">', false);
    }

    public function test_トップページは60件ずつに区切って表示する(): void
    {
        foreach (range(1, 61) as $i) {
            $this->makeVenue('東京都', '葬儀社', "テスト葬儀社{$i}");
        }

        $response = $this->get('/');

        $response->assertOk();
        $this->assertSame(60, substr_count($response->getContent(), "data-venue-card data-lat"));
    }

    public function test_サイトマップに都道府県ページと施設種別ページが載る(): void
    {
        $this->makeVenue('東京都', '斎場・火葬場', 'テスト斎場');

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('venues.area', 'tokyo'), false)
            ->assertSee(route('venues.area.type', ['tokyo', 'saijo']), false);
    }

    public function test_施設ページに出典と営業時間が載る(): void
    {
        $venue = $this->makeVenue('東京都', '斎場・火葬場', 'テスト斎場');
        $venue->update(['opening_hours' => 'Mo-Su 10:00-24:00', 'website' => 'https://example.com']);

        $this->get("/venues/{$venue->id}")
            ->assertOk()
            ->assertSee('OpenStreetMap contributors', false)
            ->assertSee('Mo-Su 10:00-24:00')
            ->assertSee('https://example.com', false);
    }
}
