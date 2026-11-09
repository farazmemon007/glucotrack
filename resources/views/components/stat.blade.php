@props([
    'label',
    'value',
    'subtext' => null,
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-[14px] p-3 flex flex-col justify-between ' . $class]) }}>
    <span class="text-[12px] font-normal text-[#4B5F59] leading-tight block truncate">{{ $label }}</span>
    <div class="mt-1">
        <span class="text-[22px] font-bold text-[#12201C] tracking-tight leading-tight block">{{ $value }}</span>
        @if($subtext)
            <span class="text-[11px] text-[#4B5F59] block mt-0.5 truncate">{{ $subtext }}</span>
        @endif
    </div>
</div>
