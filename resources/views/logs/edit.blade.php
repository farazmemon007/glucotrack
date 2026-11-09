<x-app-layout title="Edit Entry - SugarSync">
    <div class="px-[20px] pt-[56px] pb-[40px] flex flex-col gap-[16px]">
        
        <!-- Header with Back Button -->
        <header class="flex items-center gap-3">
            <a 
                href="{{ route('dashboard', ['patient_id' => $patient->id]) }}" 
                class="w-[44px] h-[44px] bg-white border border-[#CBD8D4] rounded-[22px] flex items-center justify-center text-[#12201C] hover:bg-[#E3EBE8]/50 transition-colors shadow-2xs"
                aria-label="Back to dashboard"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="flex flex-col">
                <h1 class="text-[24px] font-bold text-[#12201C] tracking-tight leading-tight">
                    Edit daily entry
                </h1>
                <span class="text-[12px] text-[#4B5F59]">
                    {{ $patient->name }} &bull; {{ $log->log_date->format('j M Y') }}
                </span>
            </div>
        </header>

        <!-- Form -->
        <form method="POST" action="{{ route('patients.logs.update', [$patient, $log]) }}" class="flex flex-col gap-[16px]">
            @csrf
            @method('PUT')

            <!-- Date Picker Field -->
            <x-card class="flex flex-col gap-1.5">
                <label for="log_date" class="text-[13px] text-[#4B5F59] font-normal">
                    {{ __('app.date_label') }}
                </label>
                <input 
                    type="date" 
                    id="log_date" 
                    name="log_date" 
                    value="{{ old('log_date', $log->log_date->format('Y-m-d')) }}" 
                    required 
                    max="{{ date('Y-m-d') }}"
                    class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-4 text-[15px] font-semibold text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                >
                @error('log_date')
                    <span class="text-[12px] text-[#9A3412] mt-1">{{ $message }}</span>
                @enderror
            </x-card>

            <!-- Card Morning (Nashta) -->
            <x-card class="flex flex-col gap-3.5" x-data="{ 
                timing: '{{ old('nashta_timing', $log->nashta_timing ?? 'before') }}',
                sugarBefore: '{{ old('nashta_sugar_before', $log->nashta_sugar_before ?? ($log->nashta_timing === 'before' ? $log->nashta_sugar : '')) }}',
                sugarAfter: '{{ old('nashta_sugar_after', $log->nashta_sugar_after ?? ($log->nashta_timing === 'after' ? $log->nashta_sugar : '')) }}'
            }">
                <div class="flex items-center justify-between">
                    <h2 class="text-[15px] font-bold text-[#12201C]">
                        {{ __('app.morning') }}
                    </h2>
                    <span class="text-[12px] text-[#4B5F59]">Breakfast</span>
                </div>

                <input type="hidden" name="nashta_timing" :value="timing">
                <input type="hidden" name="nashta_sugar_before" :value="sugarBefore">
                <input type="hidden" name="nashta_sugar_after" :value="sugarAfter">

                <!-- Timing Toggle -->
                <div class="bg-[#E3EBE8] p-[4px] rounded-[22px] flex items-center w-full">
                    <button 
                        type="button" 
                        @click="timing = 'before'"
                        :class="timing === 'before' ? 'bg-[#0F766E] text-white shadow-xs font-bold' : 'bg-transparent text-[#12201C] hover:text-[#0F766E] font-medium'"
                        class="flex-1 h-[40px] rounded-[18px] text-[13px] transition-all flex items-center justify-center gap-1.5 cursor-pointer select-none"
                    >
                        <span>Before breakfast</span>
                        <template x-if="sugarBefore">
                            <span class="text-[11px] px-1.5 py-0.5 rounded-full font-bold bg-white/25" x-text="sugarBefore"></span>
                        </template>
                    </button>
                    <button 
                        type="button" 
                        @click="timing = 'after'"
                        :class="timing === 'after' ? 'bg-[#0F766E] text-white shadow-xs font-bold' : 'bg-transparent text-[#12201C] hover:text-[#0F766E] font-medium'"
                        class="flex-1 h-[40px] rounded-[18px] text-[13px] transition-all flex items-center justify-center gap-1.5 cursor-pointer select-none"
                    >
                        <span>After breakfast</span>
                        <template x-if="sugarAfter">
                            <span class="text-[11px] px-1.5 py-0.5 rounded-full font-bold bg-white/25" x-text="sugarAfter"></span>
                        </template>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-1">
                    <!-- Before Breakfast Sugar Input -->
                    <div x-show="timing === 'before'">
                        <label class="block text-[12px] text-[#0F766E] font-bold mb-1">
                            Before breakfast sugar (mg/dL)
                        </label>
                        <input 
                            type="number" 
                            x-model="sugarBefore"
                            min="20" 
                            max="600" 
                            placeholder="e.g. 110"
                            class="w-full h-[48px] bg-white border-2 border-[#0F766E] rounded-[12px] px-3 text-[16px] font-bold text-[#12201C] focus:outline-none focus:ring-1 focus:ring-[#0F766E]"
                        >
                    </div>

                    <!-- After Breakfast Sugar Input -->
                    <div x-show="timing === 'after'">
                        <label class="block text-[12px] text-[#0F766E] font-bold mb-1">
                            After breakfast sugar (mg/dL)
                        </label>
                        <input 
                            type="number" 
                            x-model="sugarAfter"
                            min="20" 
                            max="600" 
                            placeholder="e.g. 160"
                            class="w-full h-[48px] bg-white border-2 border-[#0F766E] rounded-[12px] px-3 text-[16px] font-bold text-[#12201C] focus:outline-none focus:ring-1 focus:ring-[#0F766E]"
                        >
                    </div>

                    <!-- Insulin Morning -->
                    <div>
                        <label for="insulin_morning" class="block text-[12px] text-[#4B5F59] font-normal mb-1">
                            {{ __('app.insulin_morning_label') }}
                        </label>
                        <input 
                            type="number" 
                            step="0.5" 
                            id="insulin_morning" 
                            name="insulin_morning" 
                            value="{{ old('insulin_morning', $log->insulin_morning) }}" 
                            min="0" 
                            max="200" 
                            placeholder="e.g. 30"
                            class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-3 text-[16px] font-bold text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                        >
                    </div>
                </div>

                <div class="text-[11px] text-[#4B5F59] min-h-[16px]">
                    <span x-show="timing === 'before' && sugarAfter">
                        Recorded after breakfast: <strong class="text-[#0F766E]" x-text="sugarAfter + ' mg/dL'"></strong> &bull; <button type="button" @click="timing = 'after'" class="text-[#0F766E] underline cursor-pointer">Edit after breakfast</button>
                    </span>
                    <span x-show="timing === 'after' && sugarBefore">
                        Recorded before breakfast: <strong class="text-[#0F766E]" x-text="sugarBefore + ' mg/dL'"></strong> &bull; <button type="button" @click="timing = 'before'" class="text-[#0F766E] underline cursor-pointer">Edit before breakfast</button>
                    </span>
                </div>
            </x-card>

            <!-- Card Dopahar (Lunch) -->
            <x-card class="flex flex-col gap-3.5" x-data="{ 
                timing: '{{ old('lunch_timing', $log->lunch_timing ?? 'before') }}',
                sugarBefore: '{{ old('lunch_sugar_before', $log->lunch_sugar_before ?? ($log->lunch_timing === 'before' ? $log->lunch_sugar : '')) }}',
                sugarAfter: '{{ old('lunch_sugar_after', $log->lunch_sugar_after ?? ($log->lunch_timing === 'after' ? $log->lunch_sugar : '')) }}'
            }">
                <div class="flex items-center justify-between">
                    <h2 class="text-[15px] font-bold text-[#12201C]">
                        {{ __('app.dopahar') }}
                    </h2>
                    <span class="text-[12px] text-[#4B5F59]">Optional</span>
                </div>

                <input type="hidden" name="lunch_timing" :value="timing">
                <input type="hidden" name="lunch_sugar_before" :value="sugarBefore">
                <input type="hidden" name="lunch_sugar_after" :value="sugarAfter">

                <!-- Timing Toggle -->
                <div class="bg-[#E3EBE8] p-[4px] rounded-[22px] flex items-center w-full">
                    <button 
                        type="button" 
                        @click="timing = 'before'"
                        :class="timing === 'before' ? 'bg-[#0F766E] text-white shadow-xs font-bold' : 'bg-transparent text-[#12201C] hover:text-[#0F766E] font-medium'"
                        class="flex-1 h-[40px] rounded-[18px] text-[13px] transition-all flex items-center justify-center gap-1.5 cursor-pointer select-none"
                    >
                        <span>Before lunch</span>
                        <template x-if="sugarBefore">
                            <span class="text-[11px] px-1.5 py-0.5 rounded-full font-bold bg-white/25" x-text="sugarBefore"></span>
                        </template>
                    </button>
                    <button 
                        type="button" 
                        @click="timing = 'after'"
                        :class="timing === 'after' ? 'bg-[#0F766E] text-white shadow-xs font-bold' : 'bg-transparent text-[#12201C] hover:text-[#0F766E] font-medium'"
                        class="flex-1 h-[40px] rounded-[18px] text-[13px] transition-all flex items-center justify-center gap-1.5 cursor-pointer select-none"
                    >
                        <span>After lunch</span>
                        <template x-if="sugarAfter">
                            <span class="text-[11px] px-1.5 py-0.5 rounded-full font-bold bg-white/25" x-text="sugarAfter"></span>
                        </template>
                    </button>
                </div>

                <div>
                    <!-- Before Lunch Sugar Input -->
                    <div x-show="timing === 'before'">
                        <label class="block text-[12px] text-[#0F766E] font-bold mb-1">
                            Before lunch sugar (mg/dL)
                        </label>
                        <input 
                            type="number" 
                            x-model="sugarBefore"
                            min="20" 
                            max="600" 
                            placeholder="e.g. 120"
                            class="w-full h-[48px] bg-white border-2 border-[#0F766E] rounded-[12px] px-4 text-[16px] font-bold text-[#12201C] focus:outline-none focus:ring-1 focus:ring-[#0F766E]"
                        >
                    </div>

                    <!-- After Lunch Sugar Input -->
                    <div x-show="timing === 'after'">
                        <label class="block text-[12px] text-[#0F766E] font-bold mb-1">
                            After lunch sugar (mg/dL)
                        </label>
                        <input 
                            type="number" 
                            x-model="sugarAfter"
                            min="20" 
                            max="600" 
                            placeholder="e.g. 165"
                            class="w-full h-[48px] bg-white border-2 border-[#0F766E] rounded-[12px] px-4 text-[16px] font-bold text-[#12201C] focus:outline-none focus:ring-1 focus:ring-[#0F766E]"
                        >
                    </div>
                </div>

                <div class="text-[11px] text-[#4B5F59] min-h-[16px]">
                    <span x-show="timing === 'before' && sugarAfter">
                        Recorded after lunch: <strong class="text-[#0F766E]" x-text="sugarAfter + ' mg/dL'"></strong> &bull; <button type="button" @click="timing = 'after'" class="text-[#0F766E] underline cursor-pointer">Edit after lunch</button>
                    </span>
                    <span x-show="timing === 'after' && sugarBefore">
                        Recorded before lunch: <strong class="text-[#0F766E]" x-text="sugarBefore + ' mg/dL'"></strong> &bull; <button type="button" @click="timing = 'before'" class="text-[#0F766E] underline cursor-pointer">Edit before lunch</button>
                    </span>
                </div>
            </x-card>

            <!-- Card Raat (Dinner) -->
            <x-card class="flex flex-col gap-3.5" x-data="{ 
                timing: '{{ old('dinner_timing', $log->dinner_timing ?? 'before') }}',
                sugarBefore: '{{ old('dinner_sugar_before', $log->dinner_sugar_before ?? ($log->dinner_timing === 'before' ? $log->dinner_sugar : '')) }}',
                sugarAfter: '{{ old('dinner_sugar_after', $log->dinner_sugar_after ?? ($log->dinner_timing === 'after' ? $log->dinner_sugar : '')) }}'
            }">
                <div class="flex items-center justify-between">
                    <h2 class="text-[15px] font-bold text-[#12201C]">
                        {{ __('app.raat') }}
                    </h2>
                    <span class="text-[12px] text-[#4B5F59]">Dinner</span>
                </div>

                <input type="hidden" name="dinner_timing" :value="timing">
                <input type="hidden" name="dinner_sugar_before" :value="sugarBefore">
                <input type="hidden" name="dinner_sugar_after" :value="sugarAfter">

                <!-- Timing Toggle -->
                <div class="bg-[#E3EBE8] p-[4px] rounded-[22px] flex items-center w-full">
                    <button 
                        type="button" 
                        @click="timing = 'before'"
                        :class="timing === 'before' ? 'bg-[#0F766E] text-white shadow-xs font-bold' : 'bg-transparent text-[#12201C] hover:text-[#0F766E] font-medium'"
                        class="flex-1 h-[40px] rounded-[18px] text-[13px] transition-all flex items-center justify-center gap-1.5 cursor-pointer select-none"
                    >
                        <span>Before dinner</span>
                        <template x-if="sugarBefore">
                            <span class="text-[11px] px-1.5 py-0.5 rounded-full font-bold bg-white/25" x-text="sugarBefore"></span>
                        </template>
                    </button>
                    <button 
                        type="button" 
                        @click="timing = 'after'"
                        :class="timing === 'after' ? 'bg-[#0F766E] text-white shadow-xs font-bold' : 'bg-transparent text-[#12201C] hover:text-[#0F766E] font-medium'"
                        class="flex-1 h-[40px] rounded-[18px] text-[13px] transition-all flex items-center justify-center gap-1.5 cursor-pointer select-none"
                    >
                        <span>After dinner</span>
                        <template x-if="sugarAfter">
                            <span class="text-[11px] px-1.5 py-0.5 rounded-full font-bold bg-white/25" x-text="sugarAfter"></span>
                        </template>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-1">
                    <!-- Before Dinner Sugar Input -->
                    <div x-show="timing === 'before'">
                        <label class="block text-[12px] text-[#0F766E] font-bold mb-1">
                            Before dinner sugar (mg/dL)
                        </label>
                        <input 
                            type="number" 
                            x-model="sugarBefore"
                            min="20" 
                            max="600" 
                            placeholder="e.g. 130"
                            class="w-full h-[48px] bg-white border-2 border-[#0F766E] rounded-[12px] px-3 text-[16px] font-bold text-[#12201C] focus:outline-none focus:ring-1 focus:ring-[#0F766E]"
                        >
                    </div>

                    <!-- After Dinner Sugar Input -->
                    <div x-show="timing === 'after'">
                        <label class="block text-[12px] text-[#0F766E] font-bold mb-1">
                            After dinner sugar (mg/dL)
                        </label>
                        <input 
                            type="number" 
                            x-model="sugarAfter"
                            min="20" 
                            max="600" 
                            placeholder="e.g. 175"
                            class="w-full h-[48px] bg-white border-2 border-[#0F766E] rounded-[12px] px-3 text-[16px] font-bold text-[#12201C] focus:outline-none focus:ring-1 focus:ring-[#0F766E]"
                        >
                    </div>

                    <!-- Insulin Night -->
                    <div>
                        <label for="insulin_night" class="block text-[12px] text-[#4B5F59] font-normal mb-1">
                            {{ __('app.insulin_night_label') }}
                        </label>
                        <input 
                            type="number" 
                            step="0.5" 
                            id="insulin_night" 
                            name="insulin_night" 
                            value="{{ old('insulin_night', $log->insulin_night) }}" 
                            min="0" 
                            max="200" 
                            placeholder="e.g. 30"
                            class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-3 text-[16px] font-bold text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                        >
                    </div>
                </div>

                <div class="text-[11px] text-[#4B5F59] min-h-[16px]">
                    <span x-show="timing === 'before' && sugarAfter">
                        Recorded after dinner: <strong class="text-[#0F766E]" x-text="sugarAfter + ' mg/dL'"></strong> &bull; <button type="button" @click="timing = 'after'" class="text-[#0F766E] underline cursor-pointer">Edit after dinner</button>
                    </span>
                    <span x-show="timing === 'after' && sugarBefore">
                        Recorded before dinner: <strong class="text-[#0F766E]" x-text="sugarBefore + ' mg/dL'"></strong> &bull; <button type="button" @click="timing = 'before'" class="text-[#0F766E] underline cursor-pointer">Edit before dinner</button>
                    </span>
                </div>
            </x-card>
            </x-card>

            <!-- Notes & Check Flag -->
            <x-card class="flex flex-col gap-3">
                <div>
                    <label for="notes" class="block text-[12px] text-[#4B5F59] font-normal mb-1">
                        {{ __('app.notes_label') }}
                    </label>
                    <textarea 
                        id="notes" 
                        name="notes" 
                        rows="2" 
                        class="w-full bg-white border border-[#CBD8D4] rounded-[12px] p-3 text-[14px] text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                    >{{ old('notes', $log->notes) }}</textarea>
                </div>

                <div class="flex items-center gap-2 pt-1 border-t border-[#E3EBE8]">
                    <input 
                        type="checkbox" 
                        id="needs_check" 
                        name="needs_check" 
                        value="1" 
                        {{ old('needs_check', $log->needs_check) ? 'checked' : '' }}
                        class="w-4 h-4 rounded text-[#0F766E] focus:ring-[#0F766E] border-[#CBD8D4]"
                    >
                    <label for="needs_check" class="text-[12px] text-[#4B5F59] cursor-pointer">
                        Mark entry as unclear / needs verification (Check box)
                    </label>
                </div>
            </x-card>

            <!-- Save Entry Button -->
            <div class="pt-2">
                <button 
                    type="submit" 
                    class="w-full h-[52px] bg-[#12201C] text-white rounded-[26px] text-[16px] font-bold hover:bg-[#12201C]/90 active:scale-[0.99] transition-all cursor-pointer flex items-center justify-center shadow-md"
                >
                    Update entry
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
