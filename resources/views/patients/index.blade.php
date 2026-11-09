<x-app-layout title="Patients - SugarSync">
    <div class="px-[20px] pt-[56px] pb-[100px] flex flex-col gap-[16px]">
        
        <!-- Header -->
        <header class="flex items-center justify-between">
            <div>
                <span class="text-[12px] text-[#4B5F59] font-normal tracking-wide">
                    Directory
                </span>
                <h1 class="text-[24px] font-bold text-[#12201C] tracking-tight">
                    {{ __('app.clients') }}
                </h1>
            </div>

            <a 
                href="{{ route('patients.create') }}" 
                class="h-[44px] px-4 bg-[#0F766E] text-white rounded-[22px] text-[13px] font-bold hover:bg-[#0F766E]/90 active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-xs"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add Patient</span>
            </a>
        </header>

        <!-- Patients List -->
        <div class="flex flex-col gap-3">
            @forelse($patients as $p)
                <x-card class="p-4 flex flex-col gap-3 transition-all hover:border-[#0F766E]/40 border border-transparent">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-[17px] font-bold text-[#12201C]">{{ $p->name }}</h3>
                                @if($p->id == $activePatientId)
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-[10px] bg-[#DDF0EC] text-[#0B5C55]">Active</span>
                                @endif
                            </div>
                            <span class="text-[12px] text-[#4B5F59] mt-0.5 block">
                                {{ ucfirst($p->diabetes_type) }} &bull; {{ $p->age ? $p->age . ' yrs' : 'Age not specified' }} &bull; {{ $p->phone ?: 'No phone' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-[12px] text-[#4B5F59] pt-2 border-t border-[#E3EBE8]">
                        <span>Target: {{ $p->target_low }}–{{ $p->target_high }} mg/dL</span>
                        <span>{{ $p->daily_logs_count }} logs recorded</span>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <form method="POST" action="{{ route('patients.switch', $p) }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="return_to" value="dashboard">
                            <button 
                                type="submit" 
                                class="w-full h-[40px] bg-[#12201C] text-white rounded-[20px] text-[13px] font-bold hover:bg-[#12201C]/90 transition-all flex items-center justify-center cursor-pointer"
                            >
                                Open Dashboard
                            </button>
                        </form>

                        <a 
                            href="{{ route('patients.sheet', $p) }}" 
                            class="h-[40px] px-3.5 bg-white border border-[#CBD8D4] text-[#12201C] rounded-[20px] text-[13px] font-semibold hover:bg-[#E3EBE8]/50 transition-all flex items-center justify-center"
                        >
                            Log sheet
                        </a>

                        <a 
                            href="{{ route('patients.edit', $p) }}" 
                            class="h-[40px] px-3 bg-white border border-[#0F766E]/40 text-[#0F766E] rounded-[20px] text-[13px] font-semibold hover:bg-[#DDF0EC] transition-all flex items-center justify-center gap-1 shadow-2xs"
                            title="Edit details & password"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                            <span>Edit</span>
                        </a>
                    </div>
                </x-card>
            @empty
                <div class="p-8 text-center text-[#4B5F59]">
                    <p class="text-[14px] mb-3">No patients registered under your account yet.</p>
                    <a href="{{ route('patients.create') }}" class="inline-flex items-center justify-center h-[48px] px-6 bg-[#0F766E] text-white rounded-[24px] font-bold text-[14px]">
                        + Add First Patient
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Bottom Tab Bar -->
        <x-bottom-nav active="clients" :patientId="$activePatientId" />
    </div>
</x-app-layout>
