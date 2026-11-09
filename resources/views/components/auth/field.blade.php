@props(['id', 'label', 'error' => null, 'hint' => null])

<div>
    <label for="{{ $id }}" class="block text-sm font-semibold text-olive-800 mb-1.5">{{ $label }}</label>
    <input id="{{ $id }}"
        {{ $attributes->merge(['type' => 'text', 'class' => 'w-full px-4 py-2.5 rounded-xl border border-petal-200 bg-cream text-olive-900 placeholder:text-stone-600 focus:outline-none focus:ring-2 focus:ring-petal-400 focus:border-transparent text-sm']) }}
        @if ($error) aria-describedby="{{ $id }}-error" @endif>
    @if ($hint)
        <p class="text-xs text-stone-700 mt-1">{{ $hint }}</p>
    @endif
    @if ($error)
        @error($error) <span id="{{ $id }}-error" class="text-xs text-rose-700 mt-1 block">{{ $message }}</span> @enderror
    @endif
</div>
