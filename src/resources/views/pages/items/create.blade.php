@extends('layouts.app')

@section('title', '商品の出品')

@section('content')
<div class="item-create-container">
    <h2 class="page-title">商品の出品</h2>

    <form method="post" action="{{ route('item.store') }}" enctype="multipart/form-data" class="item-create-form">
        @csrf

        <h3 class="section-title">商品画像</h3>
        <x-forms.image-upload name="item_image" />

        <h3 class="section-title border-top">商品名と説明</h3>

        <x-forms.input label="商品名" name="name" type="text" />
        <x-forms.input label="ブランド名" name="brand_name" type="text" />

        <x-forms.textarea
            label="商品の説明"
            name="description"
            rouws="5"
        />

        <h3 class="section-title border-top">商品の詳細</h3>

        <div class="form-group">
            <label>カテゴリ</label>
            <div class="category-tags-list">
                @foreach($categories as $category)
                    <span class="category-tag-item">{{ $category->name }}</span>
                @endforeach
            </div>
        </div>

        <x-forms.stream_select
            label="商品の状態"
            name="condition"
            :options="['' => '選択してください', 'new' => '新品', 'good' => '良好', 'used' => '使用済み']"
        />

        <h3 class="section-title border-top">販売価格</h3>
        <x-forms.input
            label="販売価格"
            name="price"
            type="number"
        >
            <span class="price-prefix">¥</span>
        </x-forms.input>

        <div class="form-action-area mt-8">
            <x-forms.button
                type="submit"
                variant="primary"
                size="large"
                class="w-full"
            >
                出品する
            </x-forms.button>
        </div>
    </form>
</div>