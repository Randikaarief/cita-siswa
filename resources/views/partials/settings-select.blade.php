<div class="{{ $class ?? '' }}">
    <label for="{{ $name }}" class="label">{{ $label }}</label>
    <select id="{{ $name }}" name="{{ $name }}" class="select">
        <option value="">Pilih</option>
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected(old($name, $value ?? '') === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
</div>
