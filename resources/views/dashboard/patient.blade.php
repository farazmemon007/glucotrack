<x-app-layout title="Patient Dashboard - SugarSync">
    <div class="px-[20px] pt-[56px] pb-[60px] flex flex-col gap-[16px]">
        
        <!-- Header: Dashboard, Date Range, Patient Name, Logout Button -->
        <header class="flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-[12px] text-[#4B5F59] font-normal tracking-wide">
                    {{ $dateRangeText }}
                </span>
                <h1 class="text-[24px] font-bold text-[#12201C] tracking-tight">
                    {{ $patient->name }}
                </h1>
            </div>

            <!-- Outline Pill Button: Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button 
                    type="submit" 
                    class="h-[44px] px-4 bg-white border border-[#CBD8D4] rounded-[22px] text-[13px] font-semibold text-[#12201C] hover:bg-[#E3EBE8]/50 active:scale-[0.98] transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs"
                >
                    <svg class="w-4 h-4 text-[#4B5F59]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>{{ __('app.logout') }}</span>
                </button>
            </form>
        </header>

        <!-- Teal Hero Card: Time in range, big percentage, count, split bar -->
        <div class="bg-[#0F766E] text-white rounded-[20px] p-5 flex flex-col gap-3 shadow-xs">
            <span class="text-[13px] font-medium text-white/80">
                {{ __('app.time_in_range') }}
            </span>

            <div class="flex items-baseline gap-2">
                <span class="text-[52px] font-bold leading-none tracking-tight">
                    {{ $stats['time_in_range_percent'] }}%
                </span>
                <span class="text-[14px] text-white/80 font-normal">
                    {{ $stats['in_range_count'] }} of {{ $stats['total_readings'] }} readings in target
                </span>
            </div>

            <!-- Split Bar for In range / High / Low percentages -->
            <div class="flex flex-col gap-1.5 pt-1">
                <div class="w-full h-3 bg-white/20 rounded-full overflow-hidden flex">
                    @if($stats['time_in_range_percent'] > 0)
                        <div class="bg-[#DDF0EC] h-full" style="width: {{ $stats['time_in_range_percent'] }}%" title="In range: {{ $stats['time_in_range_percent'] }}%"></div>
                    @endif
                    @if($stats['high_percent'] > 0)
                        <div class="bg-[#C2410C] h-full" style="width: {{ $stats['high_percent'] }}%" title="High: {{ $stats['high_percent'] }}%"></div>
                    @endif
                    @if($stats['low_percent'] > 0)
                        <div class="bg-[#1D4ED8] h-full" style="width: {{ $stats['low_percent'] }}%" title="Low: {{ $stats['low_percent'] }}%"></div>
                    @endif
                </div>

                <div class="flex items-center justify-between text-[11px] font-semibold text-white/90 px-0.5">
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-[#DDF0EC] inline-block"></span>
                        {{ $stats['time_in_range_percent'] }}% In range
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-[#C2410C] inline-block"></span>
                        {{ $stats['high_percent'] }}% High
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-[#1D4ED8] inline-block"></span>
                        {{ $stats['low_percent'] }}% Low
                    </span>
                </div>
            </div>
        </div>

        <!-- Three Stat Cards: Average, Highest (with date), Lowest (with date) -->
        <div class="grid grid-cols-3 gap-2.5">
            <x-stat 
                label="Average" 
                :value="$stats['average'] ? $stats['average'] . ' mg' : '--'" 
                subtext="Overall avg"
            />
            <x-stat 
                label="Highest" 
                :value="$stats['highest'] ? $stats['highest']['sugar'] : '--'" 
                :subtext="$stats['highest'] ? $stats['highest']['date'] . ', ' . $stats['highest']['meal'] : null"
            />
            <x-stat 
                label="Lowest" 
                :value="$stats['lowest'] ? $stats['lowest']['sugar'] : '--'" 
                :subtext="$stats['lowest'] ? $stats['lowest']['date'] . ', ' . $stats['lowest']['meal'] : null"
            />
        </div>

        <!-- Card: Sugar Trend Line Chart -->
        <x-card class="p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <h2 class="text-[15px] font-bold text-[#12201C]">
                    {{ __('app.sugar_trend') }}
                </h2>
                <span class="text-[11px] text-[#4B5F59]">mg/dL vs Day</span>
            </div>
            
            <x-sugar-chart 
                type="line" 
                :data="$stats['trend_days']" 
                :targetLow="$patient->target_low ?? 80" 
                :targetHigh="$patient->target_high ?? 180" 
            />
        </x-card>

        <!-- Card: Average by Meal -->
        <x-card class="p-4 flex flex-col gap-3">
            <h2 class="text-[15px] font-bold text-[#12201C]">
                {{ __('app.average_by_meal') }}
            </h2>

            <div class="flex flex-col gap-3">
                @php
                    $meals = [
                        ['label' => 'Nashta (Morning)', 'avg' => $stats['meal_averages']['nashta']['average'], 'count' => $stats['meal_averages']['nashta']['count']],
                        ['label' => 'Dopahar (Lunch)', 'avg' => $stats['meal_averages']['lunch']['average'], 'count' => $stats['meal_averages']['lunch']['count']],
                        ['label' => 'Raat (Dinner)', 'avg' => $stats['meal_averages']['dinner']['average'], 'count' => $stats['meal_averages']['dinner']['count']],
                    ];
                @endphp

                @foreach($meals as $m)
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center justify-between text-[13px]">
                            <span class="font-medium text-[#12201C]">{{ $m['label'] }}</span>
                            <span class="font-bold text-[#12201C]">
                                {{ $m['avg'] ? $m['avg'] . ' mg/dL' : '--' }}
                            </span>
                        </div>
                        <!-- Horizontal bar track -->
                        <div class="w-full h-3 bg-[#E3EBE8] rounded-full overflow-hidden">
                            @if($m['avg'])
                                @php
                                    $pct = min(100, max(5, ($m['avg'] / 300) * 100));
                                    $barColor = $m['avg'] > $patient->target_high ? 'bg-[#C2410C]' : 'bg-[#0F766E]';
                                @endphp
                                <div class="{{ $barColor }} h-full rounded-full transition-all duration-300" style="width: {{ $pct }}%"></div>
                            @endif
                        </div>
                        @if($m['count'] === 1)
                            <span class="text-[11px] text-[#4B5F59] italic">Only 1 reading recorded</span>
                        @elseif($m['count'] === 0)
                            <span class="text-[11px] text-[#4B5F59] italic">No readings recorded</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-card>

        <!-- Card: When Sugar Was Good -->
        <x-card class="p-4 flex flex-col gap-3">
            <h2 class="text-[15px] font-bold text-[#12201C]">
                {{ __('app.when_sugar_was_good') }}
            </h2>

            <div class="flex flex-col divide-y divide-[#E3EBE8]">
                @forelse($stats['good_days'] as $gd)
                    <div class="py-2.5 first:pt-0 last:pb-0 flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-[14px] font-bold text-[#12201C]">{{ $gd['full_date'] }}</span>
                            <span class="text-[12px] text-[#4B5F59]">{{ $gd['summary'] }}</span>
                        </div>
                        <x-status-badge status="in_range" label="In range" />
                    </div>
                @empty
                    <p class="text-[13px] text-[#4B5F59] py-2">No 100% in-target days recorded in this period.</p>
                @endforelse
            </div>
        </x-card>

        <!-- Card: Needs Attention -->
        <x-card class="p-4 flex flex-col gap-3">
            <h2 class="text-[15px] font-bold text-[#12201C]">
                {{ __('app.needs_attention') }}
            </h2>

            <div class="flex flex-col divide-y divide-[#E3EBE8]">
                @forelse($stats['needs_attention'] as $item)
                    <div class="py-2.5 first:pt-0 last:pb-0 flex items-center justify-between">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <span class="text-[14px] font-bold text-[#12201C]">{{ $item['title'] }}</span>
                                <span class="text-[12px] text-[#4B5F59]">({{ $item['date'] }})</span>
                            </div>
                            <span class="text-[12px] text-[#4B5F59]">{{ $item['detail'] }}</span>
                        </div>
                        <x-status-badge :status="$item['badge_type']" :label="$item['badge']" />
                    </div>
                @empty
                    <p class="text-[13px] text-[#4B5F59] py-2">All readings look healthy and in range!</p>
                @endforelse
            </div>
        </x-card>

        <!-- Footer Line: Usual Insulin Dose -->
        <footer class="text-center py-4 text-[12px] text-[#4B5F59] border-t border-[#CBD8D4]/50">
            Usual insulin dose: Morning <strong>{{ $patient->usual_morning_dose ? $patient->usual_morning_dose . ' u' : '--' }}</strong> &bull; Night <strong>{{ $patient->usual_night_dose ? $patient->usual_night_dose . ' u' : '--' }}</strong>
        </footer>
    </div>
</x-app-layout>
