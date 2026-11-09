<x-app-layout title="Register - SugarSync">
    <div class="px-[24px] pt-[70px] pb-[32px] flex flex-col min-h-screen justify-between">
        <div class="flex flex-col">
            <!-- Brand Logo -->
            <div class="flex items-center gap-3 mb-6">
                <div class="w-[48px] h-[48px] bg-[#0F766E] rounded-tr-[24px] rounded-bl-[24px] rounded-br-[24px] rounded-tl-[4px] rotate-[-45deg] flex items-center justify-center shadow-sm">
                    <span class="w-3 h-3 bg-white rounded-full"></span>
                </div>
                <span class="text-[22px] font-bold text-[#12201C] tracking-tight">SugarSync</span>
            </div>

            <!-- Title -->
            <h1 class="text-[28px] font-bold text-[#12201C] leading-[1.2] tracking-tight mb-2">
                {{ __('app.register_title') }}
            </h1>
            <p class="text-[14px] text-[#4B5F59] leading-relaxed mb-6">
                {{ __('app.register_subtext') }}
            </p>

            <form method="POST" action="{{ route('register.post') }}" class="space-y-4">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-[13px] text-[#4B5F59] font-normal mb-1.5">
                        {{ __('app.name_label') }}
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}" 
                        required 
                        placeholder="Your full name"
                        class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-4 text-[15px] font-medium text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                    >
                    @error('name')
                        <span class="text-[12px] text-[#9A3412] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-[13px] text-[#4B5F59] font-normal mb-1.5">
                        {{ __('app.email_label') }}
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        placeholder="yourname@example.com"
                        class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-4 text-[15px] font-medium text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                    >
                    @error('email')
                        <span class="text-[12px] text-[#9A3412] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-[13px] text-[#4B5F59] font-normal mb-1.5">
                        {{ __('app.phone_label') }}
                    </label>
                    <input 
                        type="text" 
                        id="phone" 
                        name="phone" 
                        value="{{ old('phone') }}" 
                        placeholder="03XXXXXXXXX"
                        class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-4 text-[15px] font-medium text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                    >
                    @error('phone')
                        <span class="text-[12px] text-[#9A3412] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-[13px] text-[#4B5F59] font-normal mb-1.5">
                        {{ __('app.password_label') }}
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-4 text-[15px] font-medium text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                    >
                    @error('password')
                        <span class="text-[12px] text-[#9A3412] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-[13px] text-[#4B5F59] font-normal mb-1.5">
                        Confirm Password
                    </label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required 
                        class="w-full h-[48px] bg-white border border-[#CBD8D4] rounded-[12px] px-4 text-[15px] font-medium text-[#12201C] focus:outline-none focus:border-[#0F766E] focus:ring-1 focus:ring-[#0F766E]"
                    >
                </div>

                <div class="pt-4">
                    <button 
                        type="submit" 
                        class="w-full h-[52px] bg-[#12201C] text-white rounded-[26px] text-[16px] font-bold hover:bg-[#12201C]/90 active:scale-[0.99] transition-all cursor-pointer flex items-center justify-center"
                    >
                        {{ __('app.register_btn') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="text-center pt-6">
            <a href="{{ route('login') }}" class="text-[14px] text-[#4B5F59]">
                {{ __('app.already_have_account') }}
            </a>
        </div>
    </div>
</x-app-layout>
