@props([
    'status' => 'in_range',
    'label' => null,
])

@php
    $normalized = strtolower(str_replace(' ', '_', $status));
    
    $styles = match ($normalized) {
        'in_range', 'in range' => [
            'bg' => 'bg-[#DDF0EC]',
            'text' => 'text-[#0B5C55]',
            'label' => 'In range',
        ],
        'high' => [
            'bg' => 'bg-[#FDEBDD]',
            'text' => 'text-[#9A3412]',
            'label' => 'High',
        ],
        'low' => [
            'bg' => 'bg-[#DBEAFE]',
            'text' => 'text-[#1D4ED8]',
            'label' => 'Low',
        ],
        'check' => [
            'bg' => 'bg-[#FDEBDD] border border-dashed border-[#C2410C]',
            'text' => 'text-[#9A3412]',
            'label' => 'Check',
        ],
        'missing' => [
            'bg' => 'bg-[#E3EBE8]',
            'text' => 'text-[#4B5F59]',
            'label' => 'Missing',
        ],
        default => [
            'bg' => 'bg-[#E3EBE8]',
            'text' => 'text-[#4B5F59]',
            'label' => ucfirst($status),
        ],
    };

    $displayLabel = $label ?? $styles['label'];
@endphp

<span class="inline-flex items-center justify-center text-[12px] font-bold px-[10px] py-[4px] rounded-[12px] leading-tight {{ $styles['bg'] }} {{ $styles['text'] }}">
    {{ $displayLabel }}
</span>
