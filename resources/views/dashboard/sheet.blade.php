<x-app-layout title="Log Sheet - SugarSync">
    <div class="px-[16px] pt-[50px] pb-[100px] flex flex-col gap-[14px]">
        
        <!-- Header -->
        <header class="flex flex-col gap-1">
            <h1 class="text-[22px] font-bold text-[#12201C] tracking-tight leading-snug">
                {{ __('app.log_sheet_title') }}
            </h1>
            <div class="flex items-center justify-between text-[12px] text-[#4B5F59]">
                <span class="font-medium">{{ $patient->name }} &bull; {{ $monthDate->format('F Y') }}</span>
                <span class="text-[11px] font-semibold">Target: {{ $patient->target_low }}–{{ $patient->target_high }} mg/dL</span>
            </div>
        </header>

        <!-- List / Sheet Toggle (Sheet active) -->
        <div class="w-full">
            <div class="bg-[#E3EBE8] p-[4px] rounded-[22px] flex items-center w-full">
                <a 
                    href="{{ route('patients.history', $patient) }}" 
                    class="flex-1 h-[40px] rounded-[18px] text-[13px] font-semibold text-[#12201C] hover:text-[#0F766E] flex items-center justify-center transition-colors"
                >
                    {{ __('app.list_view') }}
                </a>
                <span class="flex-1 h-[40px] rounded-[18px] text-[13px] font-semibold bg-[#0F766E] text-white flex items-center justify-center shadow-xs">
                    {{ __('app.sheet_view') }}
                </span>
            </div>
        </div>

        <!-- Month & Date Filter Controls (Card) -->
        <div class="bg-white rounded-[16px] p-3 shadow-2xs border border-[#CBD8D4]/50 flex flex-col gap-2.5">
            <!-- Month Navigator with Next / Prev buttons -->
            <div class="flex items-center justify-between gap-2">
                <!-- Previous Month -->
                <a 
                    href="{{ route('patients.sheet', ['patient' => $patient->id, 'month' => $prevMonthDate->month, 'year' => $prevMonthDate->year]) }}" 
                    class="h-[38px] px-3 bg-[#F4F7F6] border border-[#CBD8D4] rounded-[19px] text-[12px] font-semibold text-[#12201C] hover:bg-[#E3EBE8] transition-colors flex items-center gap-1 cursor-pointer"
                    title="Previous month ({{ $prevMonthDate->format('M Y') }})"
                >
                    <svg class="w-4 h-4 text-[#4B5F59]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span class="hidden sm:inline">{{ $prevMonthDate->format('M') }}</span>
                </a>

                <!-- Month & Year Selector Dropdown -->
                <form method="GET" action="{{ route('patients.sheet', $patient) }}" class="flex items-center gap-1.5 flex-1 justify-center">
                    <select 
                        name="month" 
                        onchange="this.form.submit()" 
                        class="h-[38px] bg-[#DDF0EC] text-[#0B5C55] font-bold text-[13px] rounded-[19px] px-3 border border-[#0F766E]/30 focus:outline-none cursor-pointer"
                    >
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>

                    <select 
                        name="year" 
                        onchange="this.form.submit()" 
                        class="h-[38px] bg-[#DDF0EC] text-[#0B5C55] font-bold text-[13px] rounded-[19px] px-3 border border-[#0F766E]/30 focus:outline-none cursor-pointer"
                    >
                        @for($y = 2024; $y <= 2028; $y++)
                            <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </form>

                <!-- Next Month -->
                <a 
                    href="{{ route('patients.sheet', ['patient' => $patient->id, 'month' => $nextMonthDate->month, 'year' => $nextMonthDate->year]) }}" 
                    class="h-[38px] px-3 bg-[#F4F7F6] border border-[#CBD8D4] rounded-[19px] text-[12px] font-semibold text-[#12201C] hover:bg-[#E3EBE8] transition-colors flex items-center gap-1 cursor-pointer"
                    title="Next month ({{ $nextMonthDate->format('M Y') }})"
                >
                    <span class="hidden sm:inline">{{ $nextMonthDate->format('M') }}</span>
                    <svg class="w-4 h-4 text-[#4B5F59]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <!-- Specific Date Filter Row -->
            <form method="GET" action="{{ route('patients.sheet', $patient) }}" class="flex items-center gap-2 pt-2 border-t border-[#E3EBE8]">
                <div class="flex items-center gap-1.5 flex-1">
                    <label for="sheet_date" class="text-[12px] font-semibold text-[#4B5F59] whitespace-nowrap">Filter Date:</label>
                    <input 
                        type="date" 
                        id="sheet_date" 
                        name="date" 
                        value="{{ $selectedDate ?? '' }}" 
                        onchange="this.form.submit()"
                        class="h-[34px] flex-1 bg-[#F4F7F6] border border-[#CBD8D4] rounded-[10px] px-2.5 text-[12px] font-medium text-[#12201C] focus:outline-none focus:border-[#0F766E]"
                    >
                </div>

                @if($selectedDate)
                    <a 
                        href="{{ route('patients.sheet', ['patient' => $patient->id, 'month' => $currentMonth, 'year' => $currentYear]) }}" 
                        class="h-[34px] px-2.5 rounded-[10px] bg-[#E3EBE8] text-[#12201C] text-[11px] font-bold hover:bg-[#CBD8D4] flex items-center justify-center whitespace-nowrap"
                        title="Clear specific date filter"
                    >
                        &times; Clear Date
                    </a>
                @endif
            </form>

            <!-- Quick Recorded Months Pills -->
            @if($availableMonths->isNotEmpty())
                <div class="flex items-center gap-1.5 overflow-x-auto pt-1 scrollbar-none">
                    <span class="text-[11px] text-[#4B5F59] font-medium whitespace-nowrap">Logged Months:</span>
                    @foreach($availableMonths as $am)
                        @php $isActiveMonth = ($currentMonth == $am['month'] && $currentYear == $am['year']); @endphp
                        <a 
                            href="{{ route('patients.sheet', ['patient' => $patient->id, 'month' => $am['month'], 'year' => $am['year']]) }}"
                            class="px-2.5 py-1 rounded-[14px] text-[11px] font-semibold whitespace-nowrap transition-colors {{ $isActiveMonth ? 'bg-[#0F766E] text-white' : 'bg-[#E3EBE8] text-[#12201C] hover:bg-[#CBD8D4]' }}"
                        >
                            {{ $am['label'] }} ({{ $am['count'] }})
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        @if($selectedDate)
            <div class="px-3 py-2 rounded-[12px] bg-[#DDF0EC] text-[#0B5C55] text-[12px] font-semibold flex items-center justify-between">
                <span>Filtering on specific date: <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('j M Y') }}</strong> ({{ count($logs) }} log found)</span>
                <a href="{{ route('patients.sheet', ['patient' => $patient->id, 'month' => $currentMonth, 'year' => $currentYear]) }}" class="underline font-bold text-[11px]">Show all month</a>
            </div>
        @endif

        <!-- Log Sheet Table Container (Mirrors the paper sheet) -->
        <div class="bg-white rounded-[16px] overflow-hidden shadow-2xs border border-[#CBD8D4]/40">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[370px]">
                    <!-- Table Header: Ink background, white 10px text -->
                    <thead>
                        <tr class="bg-[#12201C] text-white text-[10px] font-bold uppercase tracking-wider h-[38px]">
                            <th class="w-[46px] px-2 text-center">DATE</th>
                            <th class="w-[62px] px-1 text-center">BREAKFAST</th>
                            <th class="w-[54px] px-1 text-center">INS. AM</th>
                            <th class="w-[52px] px-1 text-center">LUNCH</th>
                            <th class="w-[52px] px-1 text-center">DINNER</th>
                            <th class="w-[54px] px-1 text-center">INS. PM</th>
                            <th class="px-2 text-left">NOTES</th>
                        </tr>
                    </thead>
                    <!-- Table Body: Rows 36px high with 1px track divider -->
                    <tbody class="divide-y divide-[#E3EBE8] text-[13px] text-[#12201C]">
                        @forelse($logs as $log)
                            @php
                                $d = $log->log_date;
                                $targetHigh = $patient->target_high ?? 180;
                                $isNashtaHigh = $log->nashta_sugar !== null && $log->nashta_sugar > $targetHigh;
                                $isLunchHigh = $log->lunch_sugar !== null && $log->lunch_sugar > $targetHigh;
                                $isRaatHigh = $log->dinner_sugar !== null && $log->dinner_sugar > $targetHigh;
                            @endphp
                            <tr class="h-[36px] hover:bg-[#F4F7F6]/60 transition-colors {{ $selectedDate && $d->toDateString() === $selectedDate ? 'bg-[#DDF0EC]/40' : '' }}">
                                <!-- Date (12px / 600) -->
                                <td class="px-2 text-center text-[12px] font-semibold text-[#12201C] whitespace-nowrap">
                                    <a href="{{ route('dashboard', ['patient_id' => $patient->id, 'date' => $d->toDateString()]) }}" class="hover:text-[#0F766E] hover:underline" title="View details on Home screen">
                                        {{ $d->format('j M') }}
                                    </a>
                                </td>

                                <!-- Nashta Sugar -->
                                <td class="px-1.5 text-center">
                                    @if($log->nashta_sugar !== null)
                                        @if($isNashtaHigh)
                                            <span class="text-[#9A3412] font-bold bg-[#FDEBDD] px-1.5 py-0.5 rounded-[6px] inline-block">
                                                {{ $log->nashta_sugar }}
                                            </span>
                                        @else
                                            <span class="font-medium text-[#12201C]">
                                                {{ $log->nashta_sugar }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-[#4B5F59]">-</span>
                                    @endif
                                </td>

                                <!-- Insulin Morning -->
                                <td class="px-1.5 text-center">
                                    @if($log->needs_check && $log->insulin_morning === null)
                                        <span class="inline-block border border-dashed border-[#C2410C] text-[#C2410C] font-bold px-1.5 py-0.5 rounded-[4px] text-[11px]" title="Unclear value">
                                            ?
                                        </span>
                                    @elseif($log->insulin_morning !== null)
                                        <span class="font-medium">{{ $log->insulin_morning }}</span>
                                    @else
                                        <span class="text-[#4B5F59]">-</span>
                                    @endif
                                </td>

                                <!-- Dopahar Sugar -->
                                <td class="px-1.5 text-center">
                                    @if($log->lunch_sugar !== null)
                                        @if($isLunchHigh)
                                            <span class="text-[#9A3412] font-bold bg-[#FDEBDD] px-1.5 py-0.5 rounded-[6px] inline-block">
                                                {{ $log->lunch_sugar }}
                                            </span>
                                        @else
                                            <span class="font-medium text-[#12201C]">
                                                {{ $log->lunch_sugar }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-[#4B5F59]">-</span>
                                    @endif
                                </td>

                                <!-- Raat Sugar -->
                                <td class="px-1.5 text-center">
                                    @if($log->dinner_sugar !== null)
                                        @if($isRaatHigh)
                                            <span class="text-[#9A3412] font-bold bg-[#FDEBDD] px-1.5 py-0.5 rounded-[6px] inline-block">
                                                {{ $log->dinner_sugar }}
                                            </span>
                                        @else
                                            <span class="font-medium text-[#12201C]">
                                                {{ $log->dinner_sugar }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-[#4B5F59]">-</span>
                                    @endif
                                </td>

                                <!-- Insulin Night -->
                                <td class="px-1.5 text-center">
                                    @if($log->needs_check && $log->insulin_night == 6)
                                        <span class="inline-block border border-dashed border-[#C2410C] text-[#C2410C] font-bold px-1.5 py-0.5 rounded-[4px] text-[11px]" title="Unclear value 6u">
                                            6?
                                        </span>
                                    @elseif($log->insulin_night !== null)
                                        <span class="font-medium">{{ $log->insulin_night }}</span>
                                    @else
                                        <span class="text-[#4B5F59]">-</span>
                                    @endif
                                </td>

                                <!-- Notes -->
                                <td class="px-2 text-left text-[11px] text-[#4B5F59] truncate max-w-[120px]">
                                    @if($log->needs_check)
                                        <span class="font-bold text-[#9A3412]">Check</span> &bull; {{ $log->notes }}
                                    @else
                                        {{ $log->notes ?: '-' }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-[#4B5F59] text-[13px]">
                                    No records available for {{ $monthDate->format('F Y') }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Legend Text -->
        <div class="flex items-center gap-4 text-[11px] text-[#4B5F59] px-1">
            <div class="flex items-center gap-1.5">
                <span class="px-1.5 py-0.5 rounded-[4px] bg-[#FDEBDD] text-[#9A3412] font-bold text-[10px]">191</span>
                <span>Above high limit (&gt;{{ $patient->target_high }})</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="border border-dashed border-[#C2410C] text-[#C2410C] px-1 rounded-[4px] font-bold text-[10px]">?</span>
                <span>Unclear entry</span>
            </div>
        </div>

        <!-- Summary Cards: Avg Nashta and Avg Raat -->
        <div class="grid grid-cols-2 gap-3 mt-1">
            <x-stat 
                :label="__('app.avg_nashta')" 
                :value="$avgNashta ? $avgNashta . ' mg/dL' : '--'" 
            />
            <x-stat 
                :label="__('app.avg_raat')" 
                :value="$avgRaat ? $avgRaat . ' mg/dL' : '--'" 
            />
        </div>

        <!-- Export PDF Button -->
        <div class="pt-2">
            <a 
                href="{{ route('patients.export.pdf', ['patient' => $patient->id, 'month' => $currentMonth, 'year' => $currentYear]) }}" 
                class="w-full h-[52px] bg-[#12201C] text-white rounded-[26px] text-[16px] font-bold hover:bg-[#12201C]/90 active:scale-[0.99] transition-all flex items-center justify-center gap-2 shadow-md cursor-pointer"
            >
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>{{ __('app.export_pdf') }} ({{ $monthDate->format('M Y') }})</span>
            </a>
        </div>

        <!-- Bottom Tab Bar -->
        <x-bottom-nav active="reports" :patientId="$patient->id" />
    </div>
</x-app-layout>
