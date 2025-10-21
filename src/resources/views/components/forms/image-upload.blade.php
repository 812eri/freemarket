@props(['name'])

<div {{ $attributes->merge(['class' => 'image-upload-component']) }}>
    <div class="image-preview-area">
        <p class="placeholder-text">商品画像</p>
    </div>

    <div class="upload-action-wrapper">
        <label for="{{ $name }}" class="btn-image-select">
            画像を投稿する
        </label>
        <input type="file" id="{{ $name }}" name="{{ $name }}" class="hidden-file-input">
    </div>

    @error($name)
        <p class="error-message">{{ $message }}</p>
    @enderror