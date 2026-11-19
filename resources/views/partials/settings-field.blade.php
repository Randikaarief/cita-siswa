<div class="{{ $class ?? '' }}">
    <label for="{{ $name }}" class="label">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type ?? 'text' }}" value="{{ old($name, $value ?? '') }}"
           placeholder="{{ $placeholder ?? '' }}" autocomplete="{{ $autocomplete ?? 'off' }}" class="input">
    @isset($help)<p class="help">{{ $help }}</p>@endisset
</div>
