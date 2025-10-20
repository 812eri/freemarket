@props([
    'type' => 'text',
    'name',
    'label',
    'value' => ''
    ])

    <div class="form-group">
        <label for="{{ $name }}">{{ $label }}</label>

        <input
            {{ $attributes->merge([
                'type' => $type,
                'id' => $name,
                'name' => $name,
                'value' => old($name, $value),
                'class' => 'form-control'
                ]) }}
        />
        @error($name)
            <p class="error-message">{{ $message }}</p>
        @enderror
    </div>