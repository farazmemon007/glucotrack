<x-app-layout title="History - SugarSync">
    <div class="px-[20px] pt-[56px] pb-[100px] flex flex-col gap-[16px]">
        
        <!-- Header -->
        <header class="flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-[12px] text-[#4B5F59] font-normal tracking-wide">
                    {{ $patient->name }} &bull; {{ $days == 7 ? 'Last 7 days' : 'Last 30 days' }}
                </span>
                <h1 class="text-[24px] font-bold text-[#12201C] tracking-tight">
                    {{ __('app.history') }}
                </h1>
            </div>
            
            <a 
                href="{{ route('patients.export.pdf', ['patient' => $patient->id, 'month' => 7, 'year' => 2026]) }}" 
                class="h-[38px] px-3 bg-white border border-[#CBD8D4] rounded-[19px] text-[12px] font-semibold text-[#0F766E] hover:bg-[#DDF0EC]/50 transition-colors flex items-center gap-1.5 shadow-2xs"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>PDF</span>
            </a>
        </header>

        <!-- 7-day Bar Chart Card -->
        <x-card class="p-4 flex flex-col gap-2">
            <h2 class="text-[14px] font-bold text-[#12201C]">
                Daily Sugar Average (mg/dL)
            </h2>
            <x-sugar-chart 
                type="bar" 
                :data="$stats['bar_chart']" 
                :targetLow="$patient->target_low ?? 80" 
                :targetHigh="$patient->target_high ?? 180" 
            />
        </x-card>

        <!-- Chips: 7 days, 30 days, Export PDF -->
        <div class="flex items-center gap-2 overflow-x-auto py-1 scrollbar-none">
            <a 
                href="{{ route('patients.history', ['patient' => $patient->id, 'days' => 7]) }}" 
                class="px-4 py-2 rounded-[22px] text-[13px] font-semibold transition-all whitespace-nowrap {{ $days == 7 ? 'bg-[#12201C] text-white shadow-xs' : 'bg-white border border-[#CBD8D4] text-[#12201C] hover:bg-[#E3EBE8]/50' }}"
            >
                {{ __('app.seven_days') }}
            </a>
            <a 
                href="{{ route('patients.history', ['patient' => $patient->id, 'days' => 30]) }}" 
                class="px-4 py-2 rounded-[22px] text-[13px] font-semibold transition-all whitespace-nowrap {{ $days == 30 ? 'bg-[#12201C] text-white shadow-xs' : 'bg-white border border-[#CBD8D4] text-[#12201C] hover:bg-[#E3EBE8]/50' }}"
            >
                {{ __('app.thirty_days') }}
            </a>
            <a 
                href="{{ route('patients.sheet', $patient) }}" 
                class="px-4 py-2 rounded-[22px] text-[13px] font-semibold bg-white border border-[#CBD8D4] text-[#0F766E] hover:bg-[#DDF0EC]/50 transition-all whitespace-nowrap flex items-center gap-1.5"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Log sheet</span>
            </a>
        </div>

        <!-- Entries Grouped by Date -->
        <div class="flex flex-col gap-3 mt-1">
            @forelse($logs as $log)
                <div class="flex flex-col gap-1.5">
                    <!-- Small uppercase muted date header -->
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#4B5F59] px-1">
                        {{ $log->log_date->format('l, j M Y') }}
                    </span>

                    <x-card class="p-0 overflow-hidden divide-y divide-[#E3EBE8]">
                        @php $readings = $log->readings; @endphp
                        @if(empty($readings))
                            <div class="p-3 text-[13px] text-[#4B5F59]">No sugar readings logged for this date.</div>
                        @else
                            @foreach($readings as $r)
                                <div class="p-3 flex items-center justify-between hover:bg-[#F4F7F6]/50 transition-colors">
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[15px] font-bold text-[#12201C]">{{ $r['sugar'] }} mg/dL</span>
                                            <x-status-badge :status="$r['status']" />
                                        </div>
                                        <span class="text-[12px] text-[#4B5F59] mt-0.5">
                                            {{ $r['label'] }} ({{ $r['timing'] === 'before' ? 'Before meal' : 'After meal' }})
                                        </span>
                                    </div>

                                    <div class="text-right flex items-center gap-3">
                                        <span class="text-[14px] font-semibold text-[#12201C]">
                                            {{ $r['insulin'] ? $r['insulin'] . ' u' : '--' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        @if($log->notes || $log->needs_check)
                            <div class="px-3 py-2 bg-[#F4F7F6]/60 text-[12px] text-[#4B5F59] flex items-center justify-between">
                                <span class="italic">{{ $log->notes ?: 'Marked for verification' }}</span>
                                @if($log->needs_check)
                                    <x-status-badge status="check" />
                                @endif
                            </div>
                        @endif
                    </x-card>
                </div>
            @empty
                <div class="p-8 text-center text-[#4B5F59]">
                    <p class="text-[14px]">No historical logs found for the selected period.</p>
                </div>
            @endforelse
        </div>

        <!-- Floating Action Button: Add reading -->
        <div class="fixed bottom-[92px] left-0 right-0 z-30 pointer-events-none">
            <div class="max-w-[480px] mx-auto px-[20px] w-full pointer-events-auto">
                <a 
                    href="{{ route('patients.logs.create', $patient) }}" 
                    class="w-full h-[52px] bg-[#12201C] text-white rounded-[26px] text-[16px] font-bold hover:bg-[#12201C]/90 active:scale-[0.99] transition-all shadow-lg flex items-center justify-center gap-2 cursor-pointer"
                >
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>{{ __('app.add_reading') }}</span>
                </a>
            </div>
        </div>

        <!-- Bottom Tab Bar -->
        <x-bottom-nav active="history" :patientId="$patient->id" />
    </div>
</x-app-layout>
