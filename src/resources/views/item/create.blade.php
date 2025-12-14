@extends('layouts.app')

@section('title', '商品の出品')

@section('css')
<link rel="stylesheet" href="{{ asset('css/item/create.css') }}">
@endsection

@section('content')
<div class="item-create-page">
    <div class="item-create-container">
        <h1 class="page-title">商品の出品</h1>

        <form method="POST" action="{{ route('item.store') }}" enctype="multipart/form-data" class="item-create-form">
            @csrf

            <div class="form-section">
                <h3 class="section-header">商品画像</h3>
                <div id="image-upload-area" class="image-upload-area">
                    <div id="image-preview" class="image-preview"></div>

                    <label for="item_image" class="upload-label" id="upload-label-button">
                        画像を選択する
                        <input type="file" name="item_image" id="item_image" class="hidden-file-input" accept="image/*">
                    </label>
                </div>
                @error('item_image')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <h3 class="section-title">商品の詳細</h3>

            <div class="form-section">
                <label class="input-label">カテゴリー</label>
                <div class="category-group">
                    @foreach ($categories as $category)
                        <label class="category-item">
                            <input type="checkbox"
                                name="categories[]"
                                value="{{ $category->id }}"
                                class="category-radio"
                                @if(in_array($category->id, old('categories', []))) checked @endif
                            >
                            <span class="category-tag">{{ $category->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('categories')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-section">
                <label for="condition_id" class="input-label">商品の状態</label>
                <div class="select-wrapper">
                    <select name="condition_id" id="condition_id" class="form-select">
                        <option value="" hidden>選択してください</option>
                        @foreach ($conditions as $condition)
                            <option value="{{ $condition->id }}" @if(old('condition_id') == $condition->id) selected @endif>
                                {{ $condition->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('condition_id')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <h3 class="section-title">商品名と説明</h3>

            <div class="form-section">
                <label for="name" class="input-label">商品名</label>
                <input type="text" name="name" id="name" class="form-input" value="{{ old('name') }}">
                @error('name')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-section">
                <label for="brand_name" class="input-label">ブランド名</label>
                <input type="text" name="brand_name" id="brand_name" class="form-input" value="{{ old('brand_name') }}">
                @error('brand_name')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-section">
                <label for="description" class="input-label">商品の説明</label>
                <textarea name="description" id="description" rows="5" class="form-textarea">{{ old('description') }}</textarea>
                @error('description')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-section">
                <label for="price" class="input-label">販売価格</label>
                <div class="price-input-wrapper">
                    <span class="currency-symbol">¥</span>
                    <input type="number" name="price" id="price" class="form-input price-input" value="{{ old('price') }}">
                </div>
                @error('price')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-action-area">
                <button type="submit" class="submit-button">出品する</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('item_image');
        const uploadArea = document.getElementById('image-upload-area');
        const uploadButton = document.getElementById('upload-label-button');
        const previewArea = document.getElementById('image-preview');

        fileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    previewArea.innerHTML = `<img src="${e.target.result}" class="preview-img">`;

                    if (uploadButton) {
                        uploadButton.style.display = 'none';
                    }

                    previewArea.style.cursor = 'pointer';
                    previewArea.onclick = function() {
                        fileInput.click();
                    };

                    if (uploadArea) {
                        uploadArea.classList.add('has-image');
                    }
                }

                reader.readAsDataURL(file);
            } else {
                if (uploadArea) {
                    uploadArea.classList.remove('has-image');
                }
                if (uploadButton) {
                    uploadButton.style.display = 'inline-block';
                }
                previewArea.innerHTML = '';
            }
        });
    });
</script>
@endsection