@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'placeholder' => '',
    'icon' => null,
    'required' => false,
    'wireModel' => '',
    'wireModelModifier' => '',
    'value' => '',
    'disabled' => false,
    'readonly' => false,
    'maxlength' => null,
    'min' => null,
    'max' => null,
    'step' => null,
    'uppercase' => false,
    'lowercase' => false,
    'validatorMessage' => null,
    'hint' => null,
    'containerClass' => '',
])

<fieldset class="{{ $containerClass }}">
    @if($label)
        <legend class="fieldset-legend">{{ $label }}</legend>
    @endif
    
    <label class="input w-full validator input-bordered flex items-center gap-2 @error($name) input-error @enderror">
        {{-- Icon --}}
        @if($icon)
            <x-dynamic-component :component="$icon" class="w-4 h-4 opacity-70" />
        @endif

        {{-- Input Field --}}
        <input 
            type="{{ $type }}"
            name="{{ $name }}"
            @if($wireModel && $wireModelModifier)
                wire:model.{{ $wireModelModifier }}="{{ $wireModel }}"
            @elseif($wireModel)
                wire:model="{{ $wireModel }}"
            @endif
            @if($placeholder)
                placeholder="{{ $placeholder }}"
            @endif
            @if($value)
                value="{{ $value }}"
            @endif
            @if($required)
                required
            @endif
            @if($disabled)
                disabled
            @endif
            @if($readonly)
                readonly
            @endif
            @if($maxlength)
                maxlength="{{ $maxlength }}"
            @endif
            @if($min !== null)
                min="{{ $min }}"
            @endif
            @if($max !== null)
                max="{{ $max }}"
            @endif
            @if($step)
                step="{{ $step }}"
            @endif
            class="grow @if($uppercase) uppercase @endif @if($lowercase) lowercase @endif"
            {{ $attributes }}
        />

        {{-- Slot untuk custom content di dalam input (misal: button) --}}
        {{ $slot }}
    </label>

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
