@props([
    'type' => 'bar', // 'bar' | 'line'
    'data' => [],
    'targetLow' => 80,
    'targetHigh' => 180,
    'height' => 180,
    'title' => 'Blood sugar trend chart',
])

@if($type === 'bar')
    @php
        $barItems = $data;
        $maxVal = 300;
        $svgHeight = 140;
        $barWidth = 16;
        $count = count($barItems);
        $spacing = $count > 1 ? (340 - ($count * $barWidth)) / ($count - 1) : 40;
    @endphp

    <div class="w-full flex flex-col">
        <!-- Legend -->
        <div class="flex items-center justify-end gap-3 mb-2 text-[11px] text-[#4B5F59] font-medium">
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-[2px] bg-[#0F766E] inline-block"></span>
                <span>In range ({{ $targetLow }}–{{ $targetHigh }})</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-[2px] bg-[#C2410C] inline-block"></span>
                <span>High (&gt;{{ $targetHigh }})</span>
            </div>
        </div>

        <svg viewBox="0 0 350 160" class="w-full h-auto overflow-visible" role="img" aria-label="7-day blood sugar daily average bar chart">
            <desc>A bar chart representing blood sugar readings across the last 7 recorded days, showing when average daily sugar was in range or high.</desc>
            
            <!-- Reference target line for 180 -->
            @php
                $targetHighY = 120 - ((180 / $maxVal) * 110);
            @endphp
            <line x1="10" y1="{{ $targetHighY }}" x2="340" y2="{{ $targetHighY }}" stroke="#CBD8D4" stroke-dasharray="3,3" stroke-width="1"/>
            <text x="345" y="{{ $targetHighY + 3 }}" fill="#4B5F59" font-size="9" font-family="Inter" text-anchor="start">180</text>

            @foreach($barItems as $index => $item)
                @php
                    $x = 20 + ($index * ($barWidth + $spacing));
                    $avg = $item['average'];
                    $barH = $avg ? min(110, max(10, ($avg / $maxVal) * 110)) : 0;
                    $y = 120 - $barH;
                    $color = '#CBD8D4'; // default track/missing
                    if ($avg !== null) {
                        if ($avg > $targetHigh) {
                            $color = '#C2410C'; // high-bar
                        } elseif ($avg < $targetLow) {
                            $color = '#1D4ED8'; // low
                        } else {
                            $color = '#0F766E'; // primary
                        }
                    }
                @endphp

                <!-- Track background -->
                <rect x="{{ $x }}" y="10" width="{{ $barWidth }}" height="110" rx="8" fill="#E3EBE8" />

                <!-- Filled bar -->
                @if($avg !== null)
                    <rect x="{{ $x }}" y="{{ $y }}" width="{{ $barWidth }}" height="{{ $barH }}" rx="8" fill="{{ $color }}" />
                    <text x="{{ $x + ($barWidth / 2) }}" y="{{ max(22, $y - 4) }}" fill="#12201C" font-size="10" font-weight="700" font-family="Inter" text-anchor="middle">{{ $avg }}</text>
                @else
                    <text x="{{ $x + ($barWidth / 2) }}" y="65" fill="#4B5F59" font-size="10" font-family="Inter" text-anchor="middle">-</text>
                @endif

                <!-- Weekday label -->
                <text x="{{ $x + ($barWidth / 2) }}" y="138" fill="#4B5F59" font-size="11" font-weight="600" font-family="Inter" text-anchor="middle">
                    {{ $item['weekday'] }}
                </text>
                <!-- Day number -->
                <text x="{{ $x + ($barWidth / 2) }}" y="152" fill="#4B5F59" font-size="9" font-family="Inter" text-anchor="middle">
                    {{ $item['day_num'] }}
                </text>
            @endforeach
        </svg>
    </div>

@elseif($type === 'line')
    @php
        $days = $data;
        $totalDays = count($days);
        $viewW = 340;
        $viewH = 180;
        $padL = 36;
        $padR = 15;
        $padT = 15;
        $padB = 30;

        $chartW = $viewW - $padL - $padR;
        $chartH = $viewH - $padT - $padB;

        // Y axis: 100 to 300
        $minY = 100;
        $maxY = 300;

        $getY = function($val) use ($minY, $maxY, $chartH, $padT) {
            $clamped = max($minY, min($maxY, $val));
            return $padT + $chartH - ((($clamped - $minY) / ($maxY - $minY)) * $chartH);
        };

        $getX = function($idx) use ($totalDays, $chartW, $padL) {
            if ($totalDays <= 1) return $padL + ($chartW / 2);
            return $padL + (($idx / ($totalDays - 1)) * $chartW);
        };

        // Target band (80-180 mg/dL): in 100-300 view, from 100 to 180
        $bandTop = $getY(180);
        $bandBottom = $getY(100);
        $bandHeight = $bandBottom - $bandTop;

        // Segments for Nashta (solid dark line, filled circles)
        $nashtaSegments = [];
        $currentSegment = [];
        foreach ($days as $idx => $d) {
            if ($d['nashta'] !== null) {
                $currentSegment[] = ['x' => $getX($idx), 'y' => $getY($d['nashta']), 'val' => $d['nashta']];
            } else {
                if (!empty($currentSegment)) {
                    $nashtaSegments[] = $currentSegment;
                    $currentSegment = [];
                }
            }
        }
        if (!empty($currentSegment)) {
            $nashtaSegments[] = $currentSegment;
        }

        // Segments for Raat (dashed teal line, hollow squares)
        $raatSegments = [];
        $currentRaat = [];
        foreach ($days as $idx => $d) {
            if ($d['raat'] !== null) {
                $currentRaat[] = ['x' => $getX($idx), 'y' => $getY($d['raat']), 'val' => $d['raat']];
            } else {
                if (!empty($currentRaat)) {
                    $raatSegments[] = $currentRaat;
                    $currentRaat = [];
                }
            }
        }
        if (!empty($currentRaat)) {
            $raatSegments[] = $currentRaat;
        }
    @endphp

    <div class="w-full flex flex-col">
        <!-- Legend -->
        <div class="flex items-center justify-between text-[11px] text-[#4B5F59] font-medium mb-2">
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-[2px] bg-[#DDF0EC] border border-[#CBD8D4] inline-block"></span>
                <span>Target (80–180)</span>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#12201C] inline-block"></span>
                    <span class="text-[#12201C] font-semibold">Breakfast</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 border-2 border-[#0F766E] bg-white inline-block"></span>
                    <span class="text-[#0F766E] font-semibold">Dinner</span>
                </div>
            </div>
        </div>

        <svg viewBox="0 0 {{ $viewW }} {{ $viewH }}" class="w-full h-auto overflow-visible" role="img" aria-label="Sugar trend line chart with target range band">
            <desc>Line chart of blood sugar levels with Nashta plotted as solid line with filled circles and Raat plotted as dashed teal line with hollow squares.</desc>

            <!-- Target Range Band (80-180 mg/dL) -->
            <rect x="{{ $padL }}" y="{{ $bandTop }}" width="{{ $chartW }}" height="{{ $bandHeight }}" fill="#DDF0EC" opacity="0.8"/>

            <!-- Grid lines for Y-axis (100, 150, 200, 250, 300) -->
            @foreach([100, 150, 200, 250, 300] as $gridVal)
                @php $gy = $getY($gridVal); @endphp
                <line x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $padL + $chartW }}" y2="{{ $gy }}" stroke="#E3EBE8" stroke-width="1"/>
                <text x="{{ $padL - 6 }}" y="{{ $gy + 3 }}" fill="#4B5F59" font-size="9" font-family="Inter" text-anchor="end">{{ $gridVal }}</text>
            @endforeach

            <!-- Nashta Path segments (Solid dark line, filled circles) -->
            @foreach($nashtaSegments as $seg)
                @if(count($seg) > 1)
                    @php
                        $pathD = 'M ' . $seg[0]['x'] . ' ' . $seg[0]['y'];
                        for ($i = 1; $i < count($seg); $i++) {
                            $pathD .= ' L ' . $seg[$i]['x'] . ' ' . $seg[$i]['y'];
                        }
                    @endphp
                    <path d="{{ $pathD }}" fill="none" stroke="#12201C" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                @endif
                <!-- Circles on Nashta points -->
                @foreach($seg as $pt)
                    <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="4" fill="#12201C"/>
                @endforeach
            @endforeach

            <!-- Raat Path segments (Dashed teal line, hollow squares) -->
            @foreach($raatSegments as $seg)
                @if(count($seg) > 1)
                    @php
                        $pathD = 'M ' . $seg[0]['x'] . ' ' . $seg[0]['y'];
                        for ($i = 1; $i < count($seg); $i++) {
                            $pathD .= ' L ' . $seg[$i]['x'] . ' ' . $seg[$i]['y'];
                        }
                    @endphp
                    <path d="{{ $pathD }}" fill="none" stroke="#0F766E" stroke-width="2" stroke-dasharray="4,3" stroke-linecap="round" stroke-linejoin="round"/>
                @endif
                <!-- Hollow squares on Raat points -->
                @foreach($seg as $pt)
                    <rect x="{{ $pt['x'] - 3.5 }}" y="{{ $pt['y'] - 3.5 }}" width="7" height="7" fill="#FFFFFF" stroke="#0F766E" stroke-width="1.8"/>
                @endforeach
            @endforeach

            <!-- X-axis Labels (Day numbers) -->
            @foreach($days as $idx => $d)
                @php $dx = $getX($idx); @endphp
                <text x="{{ $dx }}" y="{{ $viewH - 10 }}" fill="#4B5F59" font-size="10" font-weight="600" font-family="Inter" text-anchor="middle">
                    {{ $d['day'] }}
                </text>
            @endforeach
        </svg>
    </div>
@endif
