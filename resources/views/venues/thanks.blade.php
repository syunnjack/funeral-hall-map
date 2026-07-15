@extends('layouts.plain')

@section('content')
<div class="container text-center mt-5">
  <h1 class="h4 mb-3">🙌 投稿ありがとうございます！</h1>
  <p class="mb-4 text-muted">葬儀社マップに反映されました。</p>
  <a href="{{ route('venues.index') }}" class="btn btn-funeral">トップへ戻る</a>
</div>
@endsection
