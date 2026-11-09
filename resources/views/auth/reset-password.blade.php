<x-app-layout title="Reset Password - SugarSync">
    <div class="px-[24px] pt-[60px] pb-[32px] flex flex-col min-h-screen justify-between" x-data="{ 
        code: '{{ old('code', '') }}',
        showPass: false,
        showConfirm: false
    }">
        <div class="flex flex-col">
            <!-- Brand Logo -->
            <div class="flex items-center gap-3 mb-6">
                <div class="w-[48px] h-[48px] bg-[#0F766E] rounded-tr-[24px] rounded-bl-[24px] rounded-br-[24px] rounded-tl-[4px] rotate-[-45deg] flex items-center justify-center shadow-sm">
                    <span class="w-3 h-3 bg-white rounded-full"></span>
                </div>
                <span class="text-[22px] font-bold text-[#12201C] tracking-tight">SugarSync</span>
            </div>

            <!-- Title & Subtext -->
            <h1 class="text-[28px] font-bold text-[#12201C] leading-[1.2] tracking-tight mb-2">
                Set New Password
            </h1>
            <p class="text-[14px] text-[#4B5F59] leading-relaxed mb-4">
                Enter the verification code and choose a new password for <span class="font-semibold text-[#12201C]">{{ $login }}</span>.
            </p>

            <!-- Demo Verification Code Pill / Card -->
            @if(!empty($code))
                <div class="mb-5 p-3.5 rounded-[14px] bg-[#DDF0EC] border border-[#0F766E]/40 text-[#0B5C55] flex items-center justify-between shadow-2xs">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-[#0F766E]">Verification Code</div>
                        <div class="text-[20px] font-mono font-bold tracking-widest text-[#0B5C55]">{{ $code }}</div>
                    </div>
                    <button 
                        type="button" 
                        @click="code = '{{ $code }}'"
                        class="px-3 py-1.5 bg-[#0F766E] text-white text-[12px] font-bold rounded-[8px] hover:bg-[#0B5C55] cursor-pointer active:scale-95 transition-all shadow-2xs"
                    >
                        Auto-fill Code
                    </button>
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf

                <!-- Verification Code -->
                <div>
                    <label for="code" class="block text-[13px] text-[#4B5F59] font-normal mb-1.5">
                        6-Digit Verification Code
                    </label>
                    <input 
                        type="text" 
                        id="code" 
                        name="code" 
                        x-model="code"
                        required 
                        maxlength="6"
                        placeholder="123456"
                        class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-4 text-[18px] font-mono font-bold tracking-widest text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E] transition-colors"
                    >
                    @error('code')
                        <span class="text-[12px] text-[#9A3412] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- New Password -->
                <div>
                    <label for="password" class="block text-[13px] text-[#4B5F59] font-normal mb-1.5">
                        New Password
                    </label>
                    <div class="relative flex items-center">
                        <input 
                            :type="showPass ? 'text' : 'password'" 
                            id="password" 
                            name="password" 
                            required 
                            placeholder="At least 6 characters"
                            class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] pl-4 pr-16 text-[15px] font-medium text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E] transition-colors"
                        >
                        <button 
                            type="button" 
                            @click="showPass = !showPass"
                            class="absolute right-4 text-[13px] font-semibold text-[#0F766E] hover:text-[#0B5C55] cursor-pointer"
                        >
                            <span x-text="showPass ? 'Hide' : 'Show'"></span>
                        </button>
                    </div>
                    @error('password')
                        <span class="text-[12px] text-[#9A3412] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Confirm New Password -->
                <div>
                    <label for="password_confirmation" class="block text-[13px] text-[#4B5F59] font-normal mb-1.5">
                        Confirm New Password
                    </label>
                    <div class="relative flex items-center">
                        <input 
                            :type="showConfirm ? 'text' : 'password'" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            required 
                            placeholder="Re-type new password"
                            class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] pl-4 pr-16 text-[15px] font-medium text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E] transition-colors"
                        >
                        <button 
                            type="button" 
                            @click="showConfirm = !showConfirm"
                            class="absolute right-4 text-[13px] font-semibold text-[#0F766E] hover:text-[#0B5C55] cursor-pointer"
                        >
                            <span x-text="showConfirm ? 'Hide' : 'Show'"></span>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button 
                        type="submit" 
                        class="w-full h-[52px] bg-[#12201C] text-white rounded-[26px] text-[16px] font-bold hover:bg-[#12201C]/90 active:scale-[0.99] transition-all cursor-pointer flex items-center justify-center shadow-sm"
                    >
                        Update Password & Login
                    </button>
                </div>
            </form>

            <!-- Resend Code -->
            <div class="pt-4 text-center">
                <form method="POST" action="{{ route('password.resend') }}">
                    @csrf
                    <span class="text-[13px] text-[#4B5F59]">Didn't receive the code?</span>
                    <button type="submit" class="text-[13px] font-semibold text-[#0F766E] hover:underline cursor-pointer ml-1">
                        Resend Code
                    </button>
                </form>
            </div>
        </div>

        <!-- Back to Login -->
        <div class="text-center pt-8">
            <a href="{{ route('login') }}" class="text-[14px] font-medium text-[#0F766E] hover:underline flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Back to Login</span>
            </a>
        </div>
    </div>
</x-app-layout>
