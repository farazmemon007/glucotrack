<x-app-layout title="Login - SugarSync">
    <div class="px-[24px] pt-[80px] pb-[32px] flex flex-col min-h-screen justify-between">
        <div class="flex flex-col">
            <!-- Brand Logo & App Name -->
            <div class="flex items-center gap-3 mb-8">
                <!-- 48px teal rotated drop icon -->
                <div class="w-[48px] h-[48px] bg-[#0F766E] rounded-tr-[24px] rounded-bl-[24px] rounded-br-[24px] rounded-tl-[4px] rotate-[-45deg] flex items-center justify-center shadow-sm">
                    <span class="w-3 h-3 bg-white rounded-full"></span>
                </div>
                <span class="text-[22px] font-bold text-[#12201C] tracking-tight">SugarSync</span>
            </div>

            <!-- Heading & Subtext -->
            <h1 class="text-[30px] font-bold text-[#12201C] leading-[1.2] tracking-tight mb-2">
                {{ __('app.welcome_back') }}
            </h1>
            <p class="text-[14px] text-[#4B5F59] leading-relaxed mb-8">
                {{ __('app.login_subtext') }}
            </p>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                <!-- Mobile / Email Field -->
                <div>
                    <label for="login" class="block text-[13px] text-[#4B5F59] font-normal mb-1.5">
                        {{ __('app.login_id_label') }}
                    </label>
                    <input 
                        type="text" 
                        id="login" 
                        name="login" 
                        value="{{ old('login', '03001234567') }}" 
                        required 
                        placeholder="03XXXXXXXXX"
                        class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-4 text-[15px] font-medium text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E] transition-colors"
                    >
                    @error('login')
                        <span class="text-[12px] text-[#9A3412] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Field with Show/Hide -->
                <div x-data="{ showPass: false }">
                    <label for="password" class="block text-[13px] text-[#4B5F59] font-normal mb-1.5">
                        {{ __('app.password_label') }}
                    </label>
                    <div class="relative flex items-center">
                        <input 
                            :type="showPass ? 'text' : 'password'" 
                            id="password" 
                            name="password" 
                            value="password123"
                            required 
                            class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] pl-4 pr-16 text-[15px] font-medium text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E] transition-colors"
                        >
                        <button 
                            type="button" 
                            @click="showPass = !showPass"
                            class="absolute right-4 text-[13px] font-semibold text-[#0F766E] hover:text-[#0B5C55] cursor-pointer"
                        >
                            <span x-text="showPass ? '{{ __('app.hide') }}' : '{{ __('app.show') }}'"></span>
                        </button>
                    </div>
                    @error('password')
                        <span class="text-[12px] text-[#9A3412] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Forgot Password -->
                <div class="flex justify-end pt-1">
                    <a href="{{ route('password.request') }}" class="text-[13px] font-semibold text-[#0F766E] hover:underline">
                        {{ __('app.forgot_password') }}
                    </a>
                </div>

                <!-- Login Button -->
                <div class="pt-4">
                    <button 
                        type="submit" 
                        class="w-full h-[52px] bg-[#12201C] text-white rounded-[26px] text-[16px] font-bold hover:bg-[#12201C]/90 active:scale-[0.99] transition-all cursor-pointer flex items-center justify-center"
                    >
                        {{ __('app.login_btn') }}
                    </button>
                </div>
            </form>

            <!-- Quick Demo Credentials Picker -->
            <div class="mt-8 p-3 rounded-[14px] bg-[#E3EBE8]/60 border border-[#CBD8D4]/60 text-[12px] text-[#4B5F59]">
                <div class="font-semibold text-[#12201C] mb-1.5">Quick Demo Logins:</div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="document.getElementById('login').value='03001234567'; document.getElementById('password').value='password123';" class="px-2.5 py-1 rounded-[14px] bg-white text-[#0F766E] font-semibold border border-[#CBD8D4] text-[11px] cursor-pointer hover:bg-[#DDF0EC]">
                        Caregiver (Dr. Ahmed)
                    </button>
                    <button type="button" onclick="document.getElementById('login').value='03009876543'; document.getElementById('password').value='password123';" class="px-2.5 py-1 rounded-[14px] bg-white text-[#0F766E] font-semibold border border-[#CBD8D4] text-[11px] cursor-pointer hover:bg-[#DDF0EC]">
                        Patient (Tariq Mahmood)
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Link -->
        <div class="text-center pt-8">
            <a href="{{ route('register') }}" class="text-[14px] text-[#4B5F59]">
                {{ __('app.new_here') }}
            </a>
        </div>
    </div>
</x-app-layout>
