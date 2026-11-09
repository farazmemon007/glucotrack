<x-app-layout title="Home - SugarSync">
    <div x-data="{ showSwitchModal: false }" class="px-[20px] pt-[56px] pb-[100px] flex flex-col gap-[16px]">
        
        <!-- Header: Date Selector, Patient Name & Switch Button -->
        <header class="flex items-center justify-between">
            <div class="flex flex-col">
                <!-- Interactive Date Selector with Quick Picker -->
                <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-1.5 mb-0.5">
                    <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                    <label for="home_date" class="text-[12px] text-[#0F766E] font-bold tracking-wide flex items-center gap-1 cursor-pointer hover:underline" title="Click to pick any date">
                        <svg class="w-3.5 h-3.5 text-[#0F766E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ $displayDate->format('j M Y') }}</span>
                    </label>
                    <input 
                        type="date" 
                        id="home_date" 
                        name="date" 
                        value="{{ $displayDate->format('Y-m-d') }}" 
                        onchange="this.form.submit()" 
                        class="opacity-0 w-4 h-4 -ml-4 cursor-pointer"
                        title="Choose date"
                    >
                </form>

                <h1 class="text-[22px] font-bold text-[#12201C] tracking-tight truncate max-w-[240px]">
                    {{ $patient->name }}
                </h1>
            </div>

            <div class="flex items-center gap-1.5">
                <!-- Outline Pill Button: Switch -->
                @if($patients->count() > 1)
                <button 
                    type="button" 
                    @click="showSwitchModal = true"
                    class="h-[40px] px-3.5 bg-white border border-[#CBD8D4] rounded-[20px] text-[13px] font-semibold text-[#12201C] hover:bg-[#E3EBE8]/50 active:scale-[0.98] transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs"
                >
                    <svg class="w-3.5 h-3.5 text-[#4B5F59]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span>{{ __('app.switch') }}</span>
                </button>
                @endif

                <!-- Outline Pill Button: Logout -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button 
                        type="submit" 
                        class="h-[40px] px-3 bg-white border border-[#CBD8D4] hover:bg-[#FDEBDD] hover:border-[#F97316]/30 text-[#9A3412] rounded-[20px] text-[12px] font-bold active:scale-[0.98] transition-all flex items-center gap-1 cursor-pointer shadow-2xs"
                        title="Logout"
                    >
                        <svg class="w-3.5 h-3.5 text-[#9A3412]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Quick Date Jump Navigation Bar -->
        <div class="flex items-center justify-between gap-1.5 bg-white p-2 rounded-[14px] border border-[#CBD8D4]/50 shadow-2xs">
            <a 
                href="{{ route('dashboard', ['patient_id' => $patient->id, 'date' => $displayDate->copy()->subDay()->format('Y-m-d')]) }}" 
                class="h-[32px] px-2.5 rounded-[16px] bg-[#F4F7F6] hover:bg-[#E3EBE8] text-[#12201C] text-[11px] font-semibold flex items-center gap-1 transition-colors"
                title="Previous Day"
            >
                <svg class="w-3.5 h-3.5 text-[#4B5F59]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                <span>Prev Day</span>
            </a>

            <!-- Direct Calendar Picker Form -->
            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-1">
                <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                <input 
                    type="date" 
                    name="date" 
                    value="{{ $displayDate->format('Y-m-d') }}" 
                    onchange="this.form.submit()" 
                    class="h-[32px] px-2 bg-[#F4F7F6] border border-[#CBD8D4] rounded-[10px] text-[11px] font-bold text-[#0F766E] focus:outline-none focus:border-[#0F766E] cursor-pointer"
                >
            </form>

            <a 
                href="{{ route('dashboard', ['patient_id' => $patient->id, 'date' => $displayDate->copy()->addDay()->format('Y-m-d')]) }}" 
                class="h-[32px] px-2.5 rounded-[16px] bg-[#F4F7F6] hover:bg-[#E3EBE8] text-[#12201C] text-[11px] font-semibold flex items-center gap-1 transition-colors"
                title="Next Day"
            >
                <span>Next Day</span>
                <svg class="w-3.5 h-3.5 text-[#4B5F59]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        @if(!empty($recentLoggedDates))
            <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none py-0.5">
                <span class="text-[11px] text-[#4B5F59] font-medium whitespace-nowrap">Logged days:</span>
                @foreach($recentLoggedDates as $rDate)
                    @php 
                        $parsedRDate = \Carbon\Carbon::parse($rDate);
                        $isSelected = $rDate === $displayDate->format('Y-m-d');
                    @endphp
                    <a 
                        href="{{ route('dashboard', ['patient_id' => $patient->id, 'date' => $rDate]) }}"
                        class="px-2 py-0.5 rounded-[12px] text-[11px] font-semibold whitespace-nowrap transition-colors {{ $isSelected ? 'bg-[#0F766E] text-white shadow-2xs' : 'bg-white border border-[#CBD8D4] text-[#12201C] hover:bg-[#DDF0EC]' }}"
                    >
                        {{ $parsedRDate->format('j M') }}
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Teal Hero Card (radius 20px, white text) -->
        <div class="bg-[#0F766E] text-white rounded-[20px] p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[13px] font-medium text-white/80">
                    {{ $latestReading ? $latestReading['time_text'] : __('app.latest_reading') }}
                </span>
                @if($latestReading)
                    <x-status-badge :status="$latestReading['status']" />
                @else
                    <span class="text-[12px] bg-white/20 text-white font-semibold px-2.5 py-1 rounded-[12px]">No logs</span>
                @endif
            </div>

            <div class="flex items-baseline gap-2 mb-3">
                <span class="text-[52px] font-bold leading-none tracking-tight">
                    {{ $latestReading ? $latestReading['sugar'] : '--' }}
                </span>
                <span class="text-[16px] font-normal text-white/80">mg/dL</span>
            </div>

            <div class="text-[13px] text-white/90 font-medium flex items-center justify-between border-t border-white/15 pt-3">
                <span>Insulin given: {{ $latestReading && $latestReading['insulin'] ? $latestReading['insulin'] . ' u' : ($displayLog && $displayLog->total_insulin > 0 ? $displayLog->total_insulin . ' u' : '0 u') }}</span>
                <span class="text-[12px] text-white/70">Target: {{ $targetLow }}–{{ $targetHigh }}</span>
            </div>
        </div>

        <!-- Three Stat Cards: Readings today, Avg today, Total insulin -->
        <div class="grid grid-cols-3 gap-2.5">
            <x-stat 
                :label="__('app.readings_today')" 
                :value="$readingsCountToday" 
            />
            <x-stat 
                :label="__('app.avg_today')" 
                :value="$avgToday ? $avgToday . ' mg/dL' : '--'" 
            />
            <x-stat 
                :label="__('app.total_insulin')" 
                :value="$totalInsulin > 0 ? $totalInsulin . ' u' : '0 u'" 
            />
        </div>

        <!-- Section: Today's entries -->
        <div class="flex flex-col gap-2.5 mt-1">
            <div class="flex items-center justify-between">
                <h2 class="text-[15px] font-bold text-[#12201C]">
                    {{ __('app.todays_entries') }}
                </h2>
                @if($displayLog)
                    <a href="{{ route('patients.logs.edit', [$patient, $displayLog]) }}" class="text-[12px] font-semibold text-[#0F766E] hover:underline">
                        Edit entry
                    </a>
                @endif
            </div>

            <x-card class="p-0 overflow-hidden divide-y divide-[#E3EBE8]">
                @forelse($readingsToday as $reading)
                    <div class="p-3.5 flex items-center justify-between hover:bg-[#F4F7F6]/50 transition-colors">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <span class="text-[16px] font-bold text-[#12201C]">{{ $reading['sugar'] }} mg/dL</span>
                                <x-status-badge :status="$reading['status']" />
                            </div>
                            <span class="text-[12px] text-[#4B5F59] mt-0.5">
                                {{ $reading['label'] }} ({{ $reading['timing'] === 'before' ? 'Before meal' : 'After meal' }})
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-[14px] font-semibold text-[#12201C]">
                                {{ $reading['insulin'] ? 'Insulin ' . $reading['insulin'] . ' u' : 'Insulin --' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-[#4B5F59]">
                        <p class="text-[13px]">No entries logged yet for {{ $displayDate->format('j M Y') }}.</p>
                        <a href="{{ route('patients.logs.create', ['patient' => $patient->id, 'date' => $displayDate->toDateString()]) }}" class="inline-block mt-2 text-[13px] font-bold text-[#0F766E] hover:underline">
                            + Add today's log
                        </a>
                    </div>
                @endforelse
            </x-card>
        </div>

        <!-- Switch Patient Modal -->
        <div 
            x-show="showSwitchModal" 
            x-cloak 
            class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4 backdrop-blur-xs"
            @click.self="showSwitchModal = false"
        >
            <div class="w-full max-w-[360px] bg-white rounded-[20px] p-5 shadow-xl flex flex-col gap-4 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between">
                    <h3 class="text-[16px] font-bold text-[#12201C]">Switch Patient</h3>
                    <button type="button" @click="showSwitchModal = false" class="text-xl text-[#4B5F59] font-bold cursor-pointer">&times;</button>
                </div>
                <div class="max-h-[260px] overflow-y-auto divide-y divide-[#E3EBE8]">
                    @foreach($patients as $p)
                        <form method="POST" action="{{ route('patients.switch', $p) }}">
                            @csrf
                            <input type="hidden" name="return_to" value="dashboard">
                            <button 
                                type="submit" 
                                class="w-full text-left py-3 px-2 flex items-center justify-between hover:bg-[#F4F7F6] rounded-[10px] cursor-pointer {{ $p->id === $patient->id ? 'bg-[#DDF0EC]/60 text-[#0F766E] font-bold' : 'text-[#12201C]' }}"
                            >
                                <span class="text-[14px]">{{ $p->name }}</span>
                                @if($p->id === $patient->id)
                                    <span class="text-[12px] text-[#0F766E] font-bold">Active</span>
                                @endif
                            </button>
                        </form>
                    @endforeach
                </div>
                <div class="pt-2 border-t border-[#E3EBE8] flex justify-between items-center">
                    <a href="{{ route('patients.create') }}" class="text-[13px] font-semibold text-[#0F766E] hover:underline">
                        + Add new patient
                    </a>
                    <button type="button" @click="showSwitchModal = false" class="text-[13px] font-semibold text-[#4B5F59]">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Floating Action Button: Add reading sits 92px above bottom, left & right 20px -->
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

        <!-- Fixed Bottom Tab Bar -->
        <x-bottom-nav active="home" :patientId="$patient->id" />
    </div>
</x-app-layout>
