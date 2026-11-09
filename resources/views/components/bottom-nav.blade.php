@props([
    'active' => 'home', // 'home' | 'history' | 'clients' | 'reports'
    'patientId' => null,
])

@php
    if (!$patientId && auth()->check()) {
        $user = auth()->user();
        $p = \App\Models\Patient::where('user_id', $user->id)->orWhere('caregiver_id', $user->id)->first();
        $patientId = $p?->id;
    }
    $patientParam = $patientId ? ['patient' => $patientId] : [];
    $homeUrl = route('dashboard', $patientParam);
    $historyUrl = $patientId ? route('patients.history', $patientParam) : route('dashboard');
    $clientsUrl = route('patients.index');
    $reportsUrl = $patientId ? route('patients.sheet', $patientParam) : route('dashboard');
@endphp

<nav class="fixed bottom-0 left-0 right-0 z-40">
    <div class="max-w-[480px] mx-auto w-full h-[76px] bg-white border-t border-[#DCE5E2] px-2 flex items-center justify-around">
        <!-- Home -->
        <a href="{{ $homeUrl }}" 
           class="flex flex-col items-center justify-center flex-1 py-1.5 transition-colors {{ $active === 'home' ? 'text-[#0F766E]' : 'text-[#4B5F59] hover:text-[#12201C]' }}">
            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-[11px] font-semibold leading-none">{{ __('app.home') }}</span>
        </a>

        <!-- History -->
        <a href="{{ $historyUrl }}" 
           class="flex flex-col items-center justify-center flex-1 py-1.5 transition-colors {{ $active === 'history' ? 'text-[#0F766E]' : 'text-[#4B5F59] hover:text-[#12201C]' }}">
            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-[11px] font-semibold leading-none">{{ __('app.history') }}</span>
        </a>

        <!-- Clients -->
        <a href="{{ $clientsUrl }}" 
           class="flex flex-col items-center justify-center flex-1 py-1.5 transition-colors {{ $active === 'clients' ? 'text-[#0F766E]' : 'text-[#4B5F59] hover:text-[#12201C]' }}">
            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="text-[11px] font-semibold leading-none">{{ __('app.clients') }}</span>
        </a>

        <!-- Reports -->
        <a href="{{ $reportsUrl }}" 
           class="flex flex-col items-center justify-center flex-1 py-1.5 transition-colors {{ $active === 'reports' ? 'text-[#0F766E]' : 'text-[#4B5F59] hover:text-[#12201C]' }}">
            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="text-[11px] font-semibold leading-none">{{ __('app.reports') }}</span>
        </a>

        <!-- Logout -->
        <form method="POST" action="{{ route('logout') }}" class="flex-1 flex justify-center py-1.5">
            @csrf
            <button type="submit" 
                    class="flex flex-col items-center justify-center w-full text-[#9A3412] hover:text-[#7C2D12] transition-colors cursor-pointer"
                    title="Logout">
                <svg class="w-5 h-5 mb-1 text-[#9A3412]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span class="text-[11px] font-semibold leading-none">{{ __('app.logout') }}</span>
            </button>
        </form>
    </div>
</nav>
