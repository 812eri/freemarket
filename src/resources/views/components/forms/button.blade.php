@props([
    'type' => 'submit',
    'variant' => 'primary',
    'size' => 'medium'
    ])

<button
    {{ $attributes->merge([
        'type' => $type,
        'class' => "btn btn-{$variant} btn-{$size}"
        ]) }}
>
    {{ $slot }}
</button>