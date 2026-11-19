@php($type = $type ?? 'text')
<div class="{{ $class ?? '' }}">
    <label for="{{ $name }}" class="label">{{ $label }}</label>
    @if ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows ?? 4 }}" {{ ($required ?? false) ? 'required' : '' }}
                  class="textarea">{{ old($name, $value ?? '') }}</textarea>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value ?? '') }}"
               {{ ($required ?? false) ? 'required' : '' }} placeholder="{{ $placeholder ?? '' }}" autocomplete="off" class="input">
    @endif
    @isset($help)<p class="help">{{ $help }}</p>@endisset
    @error($name)<p class="error">{{ $message }}</p>@enderror
</div>
