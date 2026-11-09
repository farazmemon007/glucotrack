<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#E8EFEF]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'SugarSync') }}</title>

    <!-- Google Fonts: Inter 400, 600, 700 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans antialiased text-[#12201C] bg-[#E8EFEF] flex flex-col justify-start items-center">
    <!-- App Container: Centered at max 480px on tablets/desktops, full width on mobile -->
    <div class="w-full max-w-[480px] min-h-screen bg-[#F4F7F6] relative flex flex-col shadow-2xl border-x border-[#CBD8D4]/30 pb-[90px]">
        
        <!-- One-time Patient Credentials Flash Banner (Section 6) -->
        @if(session('patient_credentials'))
            @php $creds = session('patient_credentials'); @endphp
            <div x-data="{ show: true }" x-show="show" class="m-4 p-4 rounded-[16px] bg-[#DDF0EC] border-2 border-[#0F766E] text-[#0B5C55] shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#0F766E]"></span>
                            <h4 class="text-[14px] font-bold text-[#0F766E]">
                                {{ !empty($creds['is_updated']) ? 'Patient Login Updated' : 'Patient Login Created' }} (Shown Once)
                            </h4>
                        </div>
                        <p class="text-[12px] text-[#4B5F59] mb-2">Give these credentials to {{ $creds['name'] }}:</p>
                        <div class="bg-white/80 rounded-[10px] p-2.5 text-[13px] font-mono select-all flex flex-col gap-1">
                            <div><strong>Phone:</strong> {{ $creds['phone'] }}</div>
                            <div><strong>Password:</strong> <span class="text-[#0F766E] font-bold">{{ $creds['temp_password'] }}</span></div>
                        </div>
                    </div>
                    <button type="button" @click="show = false" class="text-[#0F766E] font-bold p-1 hover:opacity-75">&times;</button>
                </div>
            </div>
        @endif

        <!-- Flash alerts -->
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="mx-5 mt-4 p-3 rounded-[12px] bg-[#DDF0EC] text-[#0B5C55] text-[13px] font-semibold flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button type="button" @click="show = false" class="text-sm font-bold">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" class="mx-5 mt-4 p-3 rounded-[12px] bg-[#FDEBDD] text-[#9A3412] text-[13px] font-semibold flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button type="button" @click="show = false" class="text-sm font-bold">&times;</button>
            </div>
        @endif

        <!-- Main Screen Content -->
        <main class="flex-1 w-full">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
