<x-app-layout title="Edit Patient - {{ $patient->name }}">
    <div class="px-[20px] pt-[56px] pb-[40px] flex flex-col gap-[16px]">
        
        <!-- Header with Back Button -->
        <header class="flex items-center gap-3">
            <a 
                href="{{ route('patients.index') }}" 
                class="w-[44px] h-[44px] bg-white border border-[#CBD8D4] rounded-[22px] flex items-center justify-center text-[#12201C] hover:bg-[#E3EBE8]/50 transition-colors shadow-2xs"
                aria-label="Back to patients"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-[22px] font-bold text-[#12201C] tracking-tight">
                    Edit Patient
                </h1>
                <p class="text-[12px] text-[#4B5F59]">{{ $patient->name }}</p>
            </div>
        </header>

        <!-- Form -->
        <form method="POST" action="{{ route('patients.update', $patient) }}" class="flex flex-col gap-[16px]">
            @csrf
            @method('PUT')

            <!-- Patient Details Card -->
            <x-card class="flex flex-col gap-3.5">
                <!-- Full name -->
                <div>
                    <label for="name" class="block text-[13px] text-[#4B5F59] font-normal mb-1">
                        {{ __('app.full_name') }} *
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', $patient->name) }}" 
                        required 
                        placeholder="Patient full name"
                        class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-4 text-[15px] font-medium text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                    >
                    @error('name')
                        <span class="text-[12px] text-[#9A3412] mt-0.5 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Age and Gender -->
                <div class="grid grid-cols-2 gap-3 items-end">
                    <div>
                        <label for="age" class="block text-[13px] text-[#4B5F59] font-normal mb-1">
                            {{ __('app.age') }}
                        </label>
                        <input 
                            type="number" 
                            id="age" 
                            name="age" 
                            value="{{ old('age', $patient->age) }}" 
                            placeholder="Years"
                            min="1"
                            max="120"
                            class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-4 text-[15px] font-medium text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                        >
                    </div>

                    <div>
                        <label class="block text-[13px] text-[#4B5F59] font-normal mb-1">
                            {{ __('app.gender') }}
                        </label>
                        <x-segmented 
                            name="gender" 
                            :value="old('gender', $patient->gender ?? 'male')"
                            :options="['male' => __('app.male'), 'female' => __('app.female')]" 
                        />
                    </div>
                </div>

                <!-- Phone number -->
                <div>
                    <label for="phone" class="block text-[13px] text-[#4B5F59] font-normal mb-1">
                        {{ __('app.phone_label') }} (Patient Login Username)
                    </label>
                    <input 
                        type="text" 
                        id="phone" 
                        name="phone" 
                        value="{{ old('phone', $patient->phone) }}" 
                        placeholder="03XXXXXXXXX"
                        class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-4 text-[15px] font-medium text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                    >
                    <span class="text-[11px] text-[#4B5F59] mt-1 block">Phone number used by patient to log into their account.</span>
                    @error('phone')
                        <span class="text-[12px] text-[#9A3412] mt-0.5 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Change / Set Password Field -->
                <div x-data="{ 
                    showPass: false, 
                    passVal: '{{ old('password', '') }}',
                    generatePass() {
                        const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                        let res = '';
                        for (let i = 0; i < 8; i++) {
                            res += chars.charAt(Math.floor(Math.random() * chars.length));
                        }
                        this.passVal = res;
                        this.showPass = true;
                    }
                }">
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-[13px] text-[#4B5F59] font-normal">
                            Change / Set Password
                        </label>
                        <button 
                            type="button" 
                            @click="generatePass()"
                            class="text-[12px] font-semibold text-[#0F766E] hover:text-[#0B5C55] flex items-center gap-1 cursor-pointer transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Auto-Generate
                        </button>
                    </div>
                    <div class="relative flex items-center">
                        <input 
                            :type="showPass ? 'text' : 'password'" 
                            id="password" 
                            name="password" 
                            x-model="passVal"
                            placeholder="Enter new password (leave blank to keep unchanged)"
                            class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] pl-4 pr-16 text-[15px] font-medium text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                        >
                        <button 
                            type="button" 
                            @click="showPass = !showPass"
                            class="absolute right-4 text-[13px] font-semibold text-[#0F766E] hover:text-[#0B5C55] cursor-pointer"
                        >
                            <span x-text="showPass ? 'Hide' : 'Show'"></span>
                        </button>
                    </div>
                    <span class="text-[11px] text-[#4B5F59] mt-1 block">Enter new password, click Auto-Generate, or leave blank to keep unchanged.</span>
                    @error('password')
                        <span class="text-[12px] text-[#9A3412] mt-0.5 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Diabetes type toggle -->
                <div>
                    <label class="block text-[13px] text-[#4B5F59] font-normal mb-1.5">
                        {{ __('app.diabetes_type') }}
                    </label>
                    <x-segmented 
                        name="diabetes_type" 
                        :value="old('diabetes_type', $patient->diabetes_type)"
                        :options="['type1' => __('app.type_1'), 'type2' => __('app.type_2'), 'other' => __('app.other')]" 
                    />
                </div>
            </x-card>

            <!-- Card: Target sugar range (mg/dL) -->
            <x-card class="flex flex-col gap-3">
                <h2 class="text-[15px] font-bold text-[#12201C]">
                    {{ __('app.target_range') }}
                </h2>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="target_low" class="block text-[12px] text-[#4B5F59] font-normal mb-1">
                            Low (min)
                        </label>
                        <input 
                            type="number" 
                            id="target_low" 
                            name="target_low" 
                            value="{{ old('target_low', $patient->target_low) }}" 
                            required 
                            class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-4 text-[16px] font-bold text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                        >
                    </div>

                    <div>
                        <label for="target_high" class="block text-[12px] text-[#4B5F59] font-normal mb-1">
                            High (max)
                        </label>
                        <input 
                            type="number" 
                            id="target_high" 
                            name="target_high" 
                            value="{{ old('target_high', $patient->target_high) }}" 
                            required 
                            class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-4 text-[16px] font-bold text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                        >
                    </div>
                </div>
            </x-card>

            <!-- Card: Usual insulin dose (units) -->
            <x-card class="flex flex-col gap-3">
                <h2 class="text-[15px] font-bold text-[#12201C]">
                    {{ __('app.usual_insulin') }}
                </h2>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="usual_morning_dose" class="block text-[12px] text-[#4B5F59] font-normal mb-1">
                            Morning (u)
                        </label>
                        <input 
                            type="number" 
                            step="0.5" 
                            id="usual_morning_dose" 
                            name="usual_morning_dose" 
                            value="{{ old('usual_morning_dose', $patient->usual_morning_dose) }}" 
                            placeholder="e.g. 30"
                            class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-4 text-[16px] font-bold text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                        >
                    </div>

                    <div>
                        <label for="usual_night_dose" class="block text-[12px] text-[#4B5F59] font-normal mb-1">
                            Night (u)
                        </label>
                        <input 
                            type="number" 
                            step="0.5" 
                            id="usual_night_dose" 
                            name="usual_night_dose" 
                            value="{{ old('usual_night_dose', $patient->usual_night_dose) }}" 
                            placeholder="e.g. 30"
                            class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-4 text-[16px] font-bold text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                        >
                    </div>
                </div>
            </x-card>

            <!-- Notes -->
            <x-card class="flex flex-col gap-1.5">
                <label for="notes" class="block text-[12px] text-[#4B5F59] font-normal">
                    {{ __('app.notes_label') }}
                </label>
                <textarea 
                    id="notes" 
                    name="notes" 
                    rows="2" 
                    placeholder="Medical history, target instructions..."
                    class="w-full bg-white border border-[#CBD8D4] rounded-[12px] p-3 text-[14px] text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                >{{ old('notes', $patient->notes) }}</textarea>
            </x-card>

            <!-- Submit Button -->
            <div class="pt-2">
                <button 
                    type="submit" 
                    class="w-full h-[52px] bg-[#12201C] text-white rounded-[26px] text-[16px] font-bold hover:bg-[#12201C]/90 active:scale-[0.99] transition-all flex items-center justify-center shadow-md cursor-pointer"
                >
                    Update Patient & Password
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
