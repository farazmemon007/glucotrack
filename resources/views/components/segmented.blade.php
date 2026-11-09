@props([
    'name' => null,
    'options' => [], // array of ['value' => '...', 'label' => '...']
    'value' => null,
    'model' => null,
])

<div 
    @if($model)
        x-data="{ selected: {{ json_encode($value ?? array_key_first($options)) }} }"
    @else
        x-data="{ selected: '{{ old($name, $value ?? (is_array($options) ? (array_is_list($options) ? $options[0] : array_key_first($options)) : '')) }}' }"
    @endif
    class="bg-[#E3EBE8] p-[4px] rounded-[22px] flex items-center w-full"
>
    @if($name)
        <input type="hidden" name="{{ $name }}" :value="selected">
    @endif

    @foreach($options as $val => $lbl)
        @php
            $optValue = is_numeric($val) ? $lbl : $val;
            $optLabel = $lbl;
        @endphp
        <button 
            type="button" 
            @click="selected = '{{ $optValue }}'; $dispatch('change', '{{ $optValue }}')"
            :class="selected === '{{ $optValue }}' ? 'bg-[#0F766E] text-white shadow-xs' : 'bg-transparent text-[#12201C] hover:text-[#0F766E]'"
            class="flex-1 h-[40px] rounded-[18px] text-[13px] font-semibold transition-all duration-150 flex items-center justify-center cursor-pointer select-none"
        >
            {{ $optLabel }}
        </button>
    @endforeach
</div>
