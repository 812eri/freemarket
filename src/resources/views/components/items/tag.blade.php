@props(['type' => 'default'])

<span
    {{ $attributes->merge(['class' => "item-tag item-tag-{$type}"]) }}
>
    {{ $slot }}
</span>