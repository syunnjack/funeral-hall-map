@extends('layouts.plain')

@php
    // 都道府県・施設種別で絞ったページは、それぞれ違う見出しと説明にする。
    $pageHeading = $area
        ? $area . 'の' . ($type ?: '葬儀社・斎場')
        : '葬儀社マップ';
    $pageTitle = $area
        ? $pageHeading . number_format($total) . '件｜' . config('app.name')
        : config('app.name') . ' | 現在地から探す・実際の費用がわかる葬儀社マップ';
    $pageDescription = $area
        ? $pageHeading . number_format($total) . '件を地図と一覧から探せます。実際にかかった費用の口コミは利用者の投稿です。'
        : '全国' . number_format($total) . '件の葬儀社・斎場を地図から検索できます。現在地から近い会館をワンタップで見つけられ、実際にかかった費用の口コミを確認できます。';
@endphp

@section('title', $pageTitle)
@section('description', $pageDescription)

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
  '@@context' => 'https://schema.org',
  '@type' => 'WebSite',
  'name' => config('app.name'),
  'url' => url('/'),
  'description' => '全国の葬儀社・葬儀会館を地図から検索できる投稿型マップ。実際の費用の口コミ・写真付き口コミを確認できる。',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
{{-- 投稿が0件のときは itemListElement が空になる。空のItemListはGoogleに
     無効な項目として扱われるため、1件以上あるときだけ出力する。 --}}
@if ($venues->isNotEmpty())
<script type="application/ld+json">
{!! json_encode([
  '@@context' => 'https://schema.org',
  '@type' => 'ItemList',
  'itemListElement' => $venues->take(50)->values()->map(function ($venue, $i) {
      return [
          '@type' => 'ListItem',
          'position' => $i + 1,
          'url' => url("/venues/{$venue->id}"),
          'name' => $venue->name,
      ];
  })->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
@endpush

@section('content')
<div class="container my-4">
  <div class="text-center mb-4">
    <h1 class="fw-bold h3">🕯️ {{ $pageHeading }}</h1>
    <p class="text-muted">
      {{ $area ? $pageHeading . 'を地図と一覧から探せます（' . number_format($total) . '件）' : '現在地から近い会館をすぐ見つける・実際の費用がわかる地図' }}
    </p>
    <a href="{{ route('venues.create') }}" class="btn btn-funeral shadow-sm px-4">➕ 葬儀社・葬儀会館を投稿</a>
  </div>

  <div class="d-flex justify-content-center mb-3">
    <button id="locateButton" class="btn btn-outline-primary">📍 現在地から近い順に探す</button>
  </div>
  <p id="locateMessage" class="text-center text-muted small mb-3"></p>

  @php
      $mapVenues = $venues->getCollection()->map(fn ($v) => [
          'id' => $v->id, 'name' => $v->name, 'area' => $v->area, 'lat' => $v->lat, 'lng' => $v->lng,
      ])->values();
  @endphp
  <div id="map" data-venues="{{ $mapVenues->toJson() }}" style="height: 360px;" class="rounded shadow-sm border mb-4"></div>

  @if($area)
    <nav aria-label="パンくず" class="small mb-3">
      <a href="{{ route('venues.index') }}">葬儀社マップ</a>
      <span class="text-muted mx-1">/</span>
      @if($type)
        <a href="{{ route('venues.area', $areaSlug) }}">{{ $area }}</a>
        <span class="text-muted mx-1">/</span><span class="text-muted">{{ $type }}</span>
      @else
        <span class="text-muted">{{ $area }}</span>
      @endif
    </nav>

    @if($typeCounts->isNotEmpty())
      <p class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('venues.area', $areaSlug) }}"
           class="btn btn-sm {{ $typeSlug ? 'btn-outline-secondary' : 'btn-primary' }}">すべて</a>
        @foreach($typeCounts as $row)
          <a href="{{ route('venues.area.type', [$areaSlug, $row['slug']]) }}"
             class="btn btn-sm {{ $typeSlug === $row['slug'] ? 'btn-primary' : 'btn-outline-secondary' }}">
            {{ $row['type'] }} <span class="text-muted">{{ number_format($row['total']) }}</span>
          </a>
        @endforeach
      </p>
    @endif
  @endif

  @if($areaCounts->isNotEmpty())
    <h2 class="h6">都道府県から探す</h2>
    <p class="d-flex flex-wrap gap-2 mb-4">
      @foreach($areaCounts as $row)
        <a href="{{ route('venues.area', $row['slug']) }}"
           class="btn btn-sm {{ $areaSlug === $row['slug'] ? 'btn-primary' : 'btn-outline-secondary' }}">
          {{ $row['area'] }} <span class="text-muted">{{ number_format($row['total']) }}</span>
        </a>
      @endforeach
    </p>
  @endif

  <p class="text-muted small">
    {{ number_format($total) }}件のうち
    {{ number_format($venues->firstItem() ?? 0) }}〜{{ number_format($venues->lastItem() ?? 0) }}件目を表示しています。
  </p>

  <div class="row" id="venueList">
    @forelse($venues as $venue)
      <div class="col-md-6 col-lg-4 mb-3" data-venue-card data-lat="{{ $venue->lat }}" data-lng="{{ $venue->lng }}">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h2 class="h6 card-title">
              <a href="{{ route('venues.show', $venue) }}" class="text-decoration-none">{{ $venue->name }}</a>
              <span class="badge bg-secondary float-end">{{ $venue->area ?? '未設定' }}</span>
            </h2>
            <p class="card-text text-muted small">{{ $venue->description }}</p>
            <small class="text-muted d-block">
              @if($venue->cost_reports_avg_total_cost)
                平均費用：約{{ number_format((int) round($venue->cost_reports_avg_total_cost)) }}円
              @else
                費用の口コミ：まだありません
              @endif
            </small>
            <small class="text-muted d-block distance-label"></small>
          </div>
        </div>
      </div>
    @empty
      <p class="text-muted">該当する葬儀社・葬儀会館がありません。</p>
    @endforelse
  </div>

  <div class="d-flex justify-content-center my-3">
    {{ $venues->onEachSide(1)->links() }}
  </div>

  <p class="text-muted small">
    施設の名称・場所・電話番号は OpenStreetMap のデータ（&copy; OpenStreetMap contributors、ODbL 1.0）をもとにしています。
    取り扱う葬儀の形式や料金は葬儀社によって異なります。表示している平均費用は利用者が投稿した実費で、
    当サイトでは内容を確認しておらず、見積り金額を示すものではありません。依頼の前に必ず各社へ直接ご確認ください。
  </p>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const mapEl = document.getElementById('map');
    const venues = JSON.parse(mapEl.dataset.venues || '[]');

    const map = L.map('map').setView([35.6812, 139.7671], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    venues.forEach(function (v) {
      L.marker([v.lat, v.lng]).addTo(map)
        .bindPopup('<a href="/venues/' + v.id + '">' + v.name + '</a><br><small>' + (v.area || '') + '</small>');
    });

    function haversineKm(lat1, lng1, lat2, lng2) {
      const R = 6371;
      const dLat = (lat2 - lat1) * Math.PI / 180;
      const dLng = (lng2 - lng1) * Math.PI / 180;
      const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
      return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    const locateButton = document.getElementById('locateButton');
    const locateMessage = document.getElementById('locateMessage');

    locateButton.addEventListener('click', function () {
      if (!navigator.geolocation) {
        locateMessage.textContent = 'このブラウザは現在地取得に対応していません。';
        return;
      }

      locateMessage.textContent = '現在地を取得しています…';

      navigator.geolocation.getCurrentPosition(function (position) {
        const userLat = position.coords.latitude;
        const userLng = position.coords.longitude;

        map.setView([userLat, userLng], 11);
        L.marker([userLat, userLng], { title: '現在地' })
          .addTo(map)
          .bindPopup('現在地')
          .openPopup();

        const cards = Array.from(document.querySelectorAll('[data-venue-card]'));
        cards.forEach(function (card) {
          const lat = parseFloat(card.dataset.lat);
          const lng = parseFloat(card.dataset.lng);
          const distance = haversineKm(userLat, userLng, lat, lng);
          card.dataset.distance = distance;
          const label = card.querySelector('.distance-label');
          if (label) label.textContent = '現在地から約' + distance.toFixed(1) + 'km';
        });

        cards.sort(function (a, b) {
          return parseFloat(a.dataset.distance) - parseFloat(b.dataset.distance);
        });

        const list = document.getElementById('venueList');
        cards.forEach(function (card) { list.appendChild(card); });

        locateMessage.textContent = '現在地から近い順に並び替えました。';
      }, function () {
        locateMessage.textContent = '現在地を取得できませんでした。ブラウザの位置情報許可をご確認ください。';
      });
    });
  });
</script>
@endsection
