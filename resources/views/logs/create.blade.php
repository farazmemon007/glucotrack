<x-app-layout title="Daily Entry - SugarSync">
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
                    {{ __('app.daily_entry_title') }}
                </h1>
                <span class="text-[12px] text-[#4B5F59]">
                    {{ $patient->name }}
                </span>
            </div>
        </header>

        <p class="text-[13px] text-[#4B5F59] leading-snug">
            {{ __('app.daily_entry_subtext') }}
        </p>

        <!-- Form -->
        <form method="POST" action="{{ route('patients.logs.store', $patient) }}" class="flex flex-col gap-[16px]">
            @csrf

            <!-- Date Picker Field -->
            <x-card class="flex flex-col gap-1.5">
                <label for="log_date" class="text-[13px] text-[#4B5F59] font-normal">
                    {{ __('app.date_label') }}
                </label>
                <input 
                    type="date" 
                    id="log_date" 
                    name="log_date" 
                    value="{{ old('log_date', $defaultDate) }}" 
                    required 
                    max="{{ date('Y-m-d') }}"
                    class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-4 text-[15px] font-semibold text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                >
                @error('log_date')
                    <span class="text-[12px] text-[#9A3412] mt-1">{{ $message }}</span>
                @enderror
            </x-card>

            <!-- Card Morning (Nashta) -->
            <x-card class="flex flex-col gap-3.5">
                <div class="flex items-center justify-between">
                    <h2 class="text-[15px] font-bold text-[#12201C]">
                        {{ __('app.morning') }}
                    </h2>
                    <span class="text-[12px] text-[#4B5F59]">Breakfast</span>
                </div>

                <!-- Timing Toggle -->
                <x-segmented 
                    name="nashta_timing" 
                    :value="old('nashta_timing', 'before')"
                    :options="['before' => __('app.before_breakfast'), 'after' => __('app.after_breakfast')]" 
                />

                <div class="grid grid-cols-2 gap-3 mt-1">
                    <!-- Nashta Sugar -->
                    <div>
                        <label for="nashta_sugar" class="block text-[12px] text-[#4B5F59] font-normal mb-1">
                            {{ __('app.nashta_sugar_label') }}
                        </label>
                        <input 
                            type="number" 
                            id="nashta_sugar" 
                            name="nashta_sugar" 
                            value="{{ old('nashta_sugar') }}" 
                            min="20" 
                            max="600" 
                            placeholder="e.g. 180"
                            class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-3 text-[16px] font-bold text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                        >
                        @error('nashta_sugar')
                            <span class="text-[11px] text-[#9A3412] mt-0.5 block">{{ $message }}</span>
                        @enderror
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
                            value="{{ old('insulin_morning', $patient->usual_morning_dose) }}" 
                            min="0" 
                            max="200" 
                            placeholder="e.g. 30"
                            class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-3 text-[16px] font-bold text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                        >
                        @error('insulin_morning')
                            <span class="text-[11px] text-[#9A3412] mt-0.5 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </x-card>

            <!-- Card Dopahar (Lunch) -->
            <x-card class="flex flex-col gap-3.5">
                <div class="flex items-center justify-between">
                    <h2 class="text-[15px] font-bold text-[#12201C]">
                        {{ __('app.dopahar') }}
                    </h2>
                    <span class="text-[12px] text-[#4B5F59]">Optional</span>
                </div>

                <!-- Timing Toggle -->
                <x-segmented 
                    name="lunch_timing" 
                    :value="old('lunch_timing', 'before')"
                    :options="['before' => __('app.before_lunch'), 'after' => __('app.after_lunch')]" 
                />

                <div>
                    <label for="lunch_sugar" class="block text-[12px] text-[#4B5F59] font-normal mb-1">
                        {{ __('app.lunch_sugar_label') }}
                    </label>
                    <input 
                        type="number" 
                        id="lunch_sugar" 
                        name="lunch_sugar" 
                        value="{{ old('lunch_sugar') }}" 
                        min="20" 
                        max="600" 
                        placeholder="optional"
                        class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-4 text-[16px] font-bold text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                    >
                    @error('lunch_sugar')
                        <span class="text-[11px] text-[#9A3412] mt-0.5 block">{{ $message }}</span>
                    @enderror
                </div>
            </x-card>

            <!-- Card Raat (Dinner) -->
            <x-card class="flex flex-col gap-3.5">
                <div class="flex items-center justify-between">
                    <h2 class="text-[15px] font-bold text-[#12201C]">
                        {{ __('app.raat') }}
                    </h2>
                    <span class="text-[12px] text-[#4B5F59]">Dinner</span>
                </div>

                <!-- Timing Toggle -->
                <x-segmented 
                    name="dinner_timing" 
                    :value="old('dinner_timing', 'before')"
                    :options="['before' => __('app.before_dinner'), 'after' => __('app.after_dinner')]" 
                />

                <div class="grid grid-cols-2 gap-3 mt-1">
                    <!-- Raat Sugar -->
                    <div>
                        <label for="dinner_sugar" class="block text-[12px] text-[#4B5F59] font-normal mb-1">
                            {{ __('app.raat_sugar_label') }}
                        </label>
                        <input 
                            type="number" 
                            id="dinner_sugar" 
                            name="dinner_sugar" 
                            value="{{ old('dinner_sugar') }}" 
                            min="20" 
                            max="600" 
                            placeholder="e.g. 165"
                            class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-3 text-[16px] font-bold text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                        >
                        @error('dinner_sugar')
                            <span class="text-[11px] text-[#9A3412] mt-0.5 block">{{ $message }}</span>
                        @enderror
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
                            value="{{ old('insulin_night', $patient->usual_night_dose) }}" 
                            min="0" 
                            max="200" 
                            placeholder="e.g. 30"
                            class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-3 text-[16px] font-bold text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                        >
                        @error('insulin_night')
                            <span class="text-[11px] text-[#9A3412] mt-0.5 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
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
                        placeholder="optional notes or remarks..."
                        class="w-full bg-white border border-[#CBD8D4] rounded-[12px] p-3 text-[14px] text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                    >{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center gap-2 pt-1 border-t border-[#E3EBE8]">
                    <input 
                        type="checkbox" 
                        id="needs_check" 
                        name="needs_check" 
                        value="1" 
                        {{ old('needs_check') ? 'checked' : '' }}
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
                    {{ __('app.save_entry') }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
