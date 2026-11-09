<x-app-layout title="Forgot Password - SugarSync">
    <div class="px-[24px] pt-[70px] pb-[32px] flex flex-col min-h-screen justify-between">
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
                {{ __('app.forgot_password') }}
            </h1>
            <p class="text-[14px] text-[#4B5F59] leading-relaxed mb-6">
                Enter your registered mobile number or email address to recover your account.
            </p>

            <!-- Form -->
            <form method="POST" action="{{ route('password.send') }}" class="space-y-4">
                @csrf

                <!-- Mobile or Email -->
                <div>
                    <label for="login" class="block text-[13px] text-[#4B5F59] font-normal mb-1.5">
                        {{ __('app.login_id_label') }}
                    </label>
                    <input 
                        type="text" 
                        id="login" 
                        name="login" 
                        value="{{ old('login') }}" 
                        required 
                        autofocus
                        placeholder="03XXXXXXXXX or email@domain.com"
                        class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-4 text-[15px] font-medium text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E] transition-colors"
                    >
                    @error('login')
                        <span class="text-[12px] text-[#9A3412] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button 
                        type="submit" 
                        class="w-full h-[52px] bg-[#12201C] text-white rounded-[26px] text-[16px] font-bold hover:bg-[#12201C]/90 active:scale-[0.99] transition-all cursor-pointer flex items-center justify-center shadow-sm"
                    >
                        Send Verification Code
                    </button>
                </div>
            </form>
        </div>

        <!-- Back to Login -->
        <div class="text-center pt-8">
            <a href="{{ route('login') }}" class="text-[14px] font-medium text-[#0F766E] hover:underline flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>{{ __('app.already_have_account') }}</span>
            </a>
        </div>
    </div>
</x-app-layout>
