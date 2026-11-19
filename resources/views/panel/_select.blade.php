<div class="{{ $class ?? '' }}">
    <label for="{{ $name }}" class="label">{{ $label }}</label>
    <select id="{{ $name }}" name="{{ $name }}" class="select">
        <option value="">-</option>
        @foreach ($options as $option)
            <option value="{{ $option }}" @selected(old($name, $value ?? '') === $option)>{{ $option }}</option>
        @endforeach
    </select>
    @error($name)<p class="error">{{ $message }}</p>@enderror
</div>
