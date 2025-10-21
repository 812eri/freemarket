@props(['name', 'label', 'options' => []])

<div class="form-group">
    <label for="{{ $name }}">{{ $label }}</label>

    <select
        {{ $attributds->merge([
            'id' => $name,
            'name' => $name,
            'class' => 'form-select'
            ]) }}
    >
            @foreach ($options as $value => $text)
                <option value="{{ $value }}" {{ old($name) == $value ? 'selected' : '' }}>
                    {{ $text }}
                </option>
            @endforeach
    </select>

    @error($name)
            <p class="error-message">{{ $message }}</p>
    @enderror
</div>