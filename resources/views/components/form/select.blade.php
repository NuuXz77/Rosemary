@props([
    'label' => '',
    'name' => '',
    'placeholder' => 'Pilih...',
    'icon' => null,
    'required' => false,
    'wireModel' => '',
    'wireModelModifier' => '',
    'value' => '',
    'disabled' => false,
    'options' => [],
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'validatorMessage' => null,
    'hint' => null,
    'containerClass' => '',
])

<fieldset class="{{ $containerClass }}">
    @if($label)
        <legend class="fieldset-legend">{{ $label }}</legend>
    @endif
    
    <div class="relative">
        @if($icon)
            <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none z-10">
                <x-dynamic-component :component="$icon" class="w-4 h-4 opacity-70" />
            </div>
        @endif

        <select
            name="{{ $name }}"
            @if($wireModel && $wireModelModifier)
                wire:model.{{ $wireModelModifier }}="{{ $wireModel }}"
            @elseif($wireModel)
                wire:model="{{ $wireModel }}"
            @endif
            @if($required)
                required
            @endif
            @if($disabled)
                disabled
            @endif
            {{ $attributes->merge(['class' => 'select select-bordered w-full' . ($icon ? ' pl-10' : '')]) }}
            @error($name) class="select-error" @enderror
        >
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif

            {{-- Slot untuk custom options --}}
            @if($slot->isEmpty())
                {{-- Gunakan options dari props --}}
                @foreach($options as $option)
                    <option value="{{ is_array($option) || is_object($option) ? data_get($option, $optionValue) : $option }}"
                        {{ $value == (is_array($option) || is_object($option) ? data_get($option, $optionValue) : $option) ? 'selected' : '' }}>
                        {{ is_array($option) || is_object($option) ? data_get($option, $optionLabel) : $option }}
                    </option>
                @endforeach
            @else
                {{-- Gunakan custom options dari slot --}}
                {{ $slot }}
            @endif
        </select>
    </div>

    {{-- Validator Message --}}
    @if($validatorMessage)
        <p class="validator-hint @error($name) @else hidden @enderror">
            {{ $validatorMessage }}
        </p>
    @endif

    {{-- Laravel Error Message --}}
    @error($name)
        <p class="text-error text-xs mt-1">{{ $message }}</p>
    @enderror

    {{-- Hint/Helper Text --}}
    @if($hint)
        <p class="text-xs text-gray-500 mt-1">{{ $hint }}</p>
    @endif
</fieldset>
