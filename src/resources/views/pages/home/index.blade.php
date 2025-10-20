@extends('layouts.app')

@section('title', '商品一覧')

@section('content')
<div class="item-list-page-container">

    <div class="tab-menu-wrapper">
        <a href="{{ route('home') }}" class="tab-item (($current_tab === 'recommended' ? 'is-active' : '' }}">おすすめ</a>
        <a href="{{ route('favorite') }}" class="tab-item {{current_tab === 'favorites' ? 'is-active' : '' }}">マイリスト</a>
    </div>

    <div class="item-list-grid-wrapper">

        @if ($items->isEmpty())
            <p class="no-items-message">現在、表示できるものはありません</p>
        @else
            <div class="item-list-grid">
                @foreach ($items as $item)
                    <x-items.card :item="$item" />
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection