<label class="flex cursor-pointer items-start gap-2.5 text-sm">
    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked ?? false))
           class="mt-0.5 size-4 rounded border-line text-brand-500">
    <span class="font-semibold">{{ $label }}@isset($help)<span class="help block font-normal">{{ $help }}</span>@endisset</span>
</label>
