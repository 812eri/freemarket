@extends('layouts.app')

@section('title', '商品詳細 - ' . $item->name)

@section('content')
<div class="item-detail-page">
    <div class="item-detail-main">
        <div class="item-image-area">
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="item-main-image">
        </div>

    <div class="item-info-area">
        <h1 class="item-name">{{ $item->name }}</h1>
        <p class="item-brand">ブランド名: {{ $item->brand_name }}</p>
        <p class="item-price">¥{{ number_format($item->price) }} <span class="tax-info">(税込)</span></p>

        <div class="item-reactions">
            <span class="favorite-count">☆ {{ $item->favorites_count }}</span>
            <span class="comment-count">💬 {{ $item->comment_count }}</span>
        </div>

        <div class="buy-button-wrapper">
            <x-forms.button
                type="submit"
                variant="primary"
                size="large"
            >
                購入手続きへ
            </x-forms.button>
        </div>

        <h2 class="section-title">商品説明</h2>
        <div class="item-description-body">
            <p>{{ $item->description }}</p>
        </div>

        <h2 class="section-title">商品の情報</h2>
        <div class="item-metadata">
            <p>カテゴリー
                <x-items.tag type="category">{{ $item->category_main }}</x-items.tag>
                <x-items.tag type="category">{{ $item->category_sub }}</x-items.tag>
            </p>
            <p>商品の状態
                <x-items.tag type="condition">{{ $item->condition }}</x-items.tag>
            </p>
        </div>
    </div>
    </div>

    <div class="comment-section">
        <h2 class="section-title">コメント({{ $item->comments_count }})</h2>

        <div class="existing-comment">
            <p class="comment-user">admin</p>
            <p class="comment-body">こちらにコメントが入ります。</p>
        </div>

        <h3 class="section-subtitle">商品へのコメント</h3>
        <form method="post" action="{{ route('item.comment', $item) }}" class="comment-form">
            @csrf

            <x-forms.textarea
                name="comment_body"
                rows="5"
                placeholder="コメントを入力してください。"
            />
            <div class="comment-action-area">
                <x-forms.button
                    type="submit"
                    variant="primary"
                    size="medium"
                >
                    コメントを送信する
                </x-forms.button>
            </div>
        </form>
    </div>
</div>
@endsection