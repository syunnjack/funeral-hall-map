@extends('layouts.plain')

@section('title', 'このサイトについて | ' . config('app.name'))
@section('description', config('app.name') . 'の運営方針、データの取り扱い、口コミ・LINE通知・事前相談受付の仕組みについて説明しています。')

@section('content')
<div class="container my-4" style="max-width: 720px;">
  <h1 class="h4 fw-bold mb-4">このサイトについて</h1>

  <section class="mb-4">
    <h2 class="h6">サイトの目的</h2>
    <p class="text-muted small">
      「{{ config('app.name') }}」は、葬儀社・葬儀会館の場所を地図から探せる投稿型マップです。新しい会館は誰でもログイン不要・匿名で投稿でき、
      実際に利用した方が費用の口コミや写真付き口コミを投稿することで情報が更新されていきます。
      大手ポータルでは分かりにくい「実際にいくらかかったか」が分かることが特徴です。
    </p>
  </section>

  <section class="mb-4">
    <h2 class="h6">費用の口コミについて</h2>
    <p class="text-muted small">
      掲載している費用（総額）・葬儀形式・参列者数は、実際にその会館を利用した方からの投稿によるものです。運営による事実確認は行っておらず、
      葬儀形式や地域・時期によって金額は大きく変動するため、あくまで参考情報としてご利用ください。
    </p>
  </section>

  <section class="mb-4">
    <h2 class="h6">LINE通知について</h2>
    <p class="text-muted small">
      各会館のページから「🔔 新しい費用の口コミが投稿されたらLINEで通知」を選ぶと、LINEログインのうえその会館を通知対象として登録できます。
      終活などで事前に費用相場を調べたい方にもご活用いただけます。
    </p>
  </section>

  <section class="mb-4">
    <h2 class="h6">事前相談・資料請求について</h2>
    <p class="text-muted small">
      各会館のページから「📮 LINEで事前相談・資料請求する」を選ぶと、LINEログインのうえ受け付けます。
      受付完了はLINE公式アカウントからお知らせしますが、当サイトは資料の発送や相談対応そのものは行っておりません。
      お急ぎの場合は、掲載している電話番号へ直接お問い合わせいただくか、各会館の公式サイトもあわせてご確認ください。
    </p>
  </section>

  <section class="mb-4">
    <h2 class="h6">口コミ・投稿について</h2>
    <p class="text-muted small">
      口コミ（写真を含む）や新規会館の投稿は、どなたでもログイン不要で行えます。投稿内容は運営による事前確認を行わず即時反映されますが、
      不適切な投稿を発見した場合は内容を精査のうえ削除などの対応を行います。
    </p>
  </section>

  <a href="{{ route('venues.index') }}" class="d-block text-center text-muted mt-4">トップページに戻る</a>
</div>
@endsection
