<!DOCTYPE html>
<html lang="ja">
<head>
  <meta name="google-site-verification" content="1pgC7iMFMxnzzI8MUK2UikB6X55R3UlWFSPGZDMLkZA" />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#3e4a5c">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', config('app.name') . ' | 現在地から探す・実際の費用がわかる葬儀社マップ')</title>
  <meta name="description" content="@yield('description', '全国の葬儀社・葬儀会館を地図から探せる投稿型マップです。現在地から近い会館をすぐ見つけられ、実際にかかった費用の口コミ・写真付き口コミをリアルタイムで確認できます。')">
  @php
      // url()->current() はクエリを落とすため、2ページ目以降が1ページ目を
      // 正規URLとして申告してしまう。内容が変わる page だけを残す。
      $canonicalQuery = array_filter(request()->only(['page']), fn ($value) => $value !== null && $value !== '' && $value !== '1');
      $canonicalUrl = url()->current() . ($canonicalQuery ? '?' . http_build_query($canonicalQuery) : '');
  @endphp
  <link rel="canonical" href="{{ $canonicalUrl }}">

  <meta property="og:site_name" content="{{ config('app.name') }}">
  <meta property="og:type" content="website">
  <meta property="og:title" content="@yield('title', config('app.name') . ' | 現在地から探す・実際の費用がわかる葬儀社マップ')">
  <meta property="og:description" content="@yield('description', '全国の葬儀社・葬儀会館を地図から探せる投稿型マップです。現在地から近い会館をすぐ見つけられ、実際にかかった費用の口コミ・写真付き口コミをリアルタイムで確認できます。')">
  <meta property="og:url" content="{{ $canonicalUrl }}">
  <meta property="og:locale" content="ja_JP">

  <meta name="twitter:card" content="summary">
  <meta name="twitter:title" content="@yield('title', config('app.name') . ' | 現在地から探す・実際の費用がわかる葬儀社マップ')">
  <meta name="twitter:description" content="@yield('description', '全国の葬儀社・葬儀会館を地図から探せる投稿型マップです。現在地から近い会館をすぐ見つけられ、実際にかかった費用の口コミ・写真付き口コミをリアルタイムで確認できます。')">

  <link rel="icon" href="/favicon.ico" sizes="any">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body { background-color: #f7f7f8; font-family: system-ui, -apple-system, sans-serif; }
    .btn { min-height: 44px; }
    .btn-line { background: #06c755; color: #fff; border: none; }
    .btn-line:hover { background: #05a848; color: #fff; }
    .btn-funeral { background: #3e4a5c; color: #fff; border: none; }
    .btn-funeral:hover { background: #2f3947; color: #fff; }
  </style>
  @yield('styles')

  @stack('structured-data')
  @if(config('services.ga4.id'))
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.ga4.id') }}"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ config('services.ga4.id') }}');
  </script>
  @endif
</head>
<body>
  <nav class="navbar navbar-dark p-2" style="background-color:#2b333e;">
    <div class="container-fluid">
      <a href="{{ route('venues.index') }}" class="navbar-brand text-white text-decoration-none">🕯️ {{ config('app.name') }}</a>
      <a href="{{ route('about') }}" class="text-white small text-decoration-none">サイトについて</a>
    </div>
  </nav>

  @yield('content')

  {{-- 掲載データは OpenStreetMap から取り込んでいる。ODbL 1.0 は出典表示を求めている。 --}}
  <footer class="border-top mt-5 py-4">
    <div class="container small text-muted">
      <p class="mb-1">
        掲載している会館・葬儀社の情報の一部は
        <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>
        の提供データ（&copy; OpenStreetMap contributors, ODbL 1.0）を利用しています。
      </p>
      <p class="mb-0">最新の対応内容・料金は各社の公式情報をご確認ください。</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @yield('scripts')
</body>
</html>
