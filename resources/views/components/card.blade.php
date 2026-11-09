@props([
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-[16px] p-4 ' . $class]) }}>
    {{ $slot }}
</div>
