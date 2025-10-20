@props(['item'])

<a href="{{ route('item.show', $item->id) }}" class="item-card-link">
    <div {{ $attributes->merge(['class' => 'item-card']) }}>

        <div class="item-card-image-wrapper">
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="item-card-image">
        </div>

        <div class="item-card-info">
            <p class="item-card-name">{{ $item->name }}</p>
        </div>
    </div>
</a>