    @props([
    'label' => '',
    'wireModel' => '',
    'value' => '',
    'checked' => false,
    'disabled' => false,
    'name' => '',
    'id' => '',
    'size' => 'checkbox-sm', // checkbox-xs, checkbox-sm, checkbox-md, checkbox-lg
    'color' => '', // checkbox-primary, checkbox-secondary, checkbox-accent, dll
    'containerClass' => '',
])

<label class="label cursor-pointer justify-start gap-3 {{ $containerClass }}">
    <input 
        type="checkbox" 
        @if($name)
            name="{{ $name }}"
        @endif
        @if($id)
            id="{{ $id }}"
        @endif
        @if($wireModel)
            wire:model="{{ $wireModel }}"
        @endif
        @if($value)
            value="{{ $value }}"
        @endif
        @if($checked)
            checked
        @endif
        @if($disabled)
            disabled
        @endif
        class="checkbox {{ $size }} {{ $color }}"
        {{ $attributes }}
    />
    @if($label)
        <span class="label-text">{{ $label }}</span>
    @endif
    
    {{-- Slot untuk custom label content --}}
    {{ $slot }}
</label>
