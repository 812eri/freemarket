@props([
    'name',
    'label' => null,
    'value' => ''
    ])

<div class="form-group">
    @if ($label)
        <label for="{{ $name }}">{{ $label }}</label>
    @endif

    <textarea
        {{ $attributes->merge([
            'id' => $name,
            'name' => $name,
            'class' => 'form-textarea'
            ]) }}>
        {{ old($name, $value) }}</textarea>

        @error($name)
            <p class="error-message">{{ $message }}</p>
        @enderror
</div>