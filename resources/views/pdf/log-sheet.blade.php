<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Blood Sugar & Insulin Log - {{ $patient->name }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #12201C;
            margin: 0;
            padding: 20px;
            font-size: 11px;
            background-color: #FFFFFF;
        }
        .header {
            margin-bottom: 18px;
            border-bottom: 2px solid #0F766E;
            padding-bottom: 12px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #12201C;
            margin: 0 0 4px 0;
        }
        .subtitle {
            font-size: 12px;
            color: #4B5F59;
            margin: 0;
        }
        .info-bar {
            margin-top: 8px;
            font-size: 11px;
            color: #4B5F59;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        th {
            background-color: #12201C;
            color: #FFFFFF;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
            padding: 7px 4px;
            text-align: center;
        }
        td {
            padding: 6px 4px;
            border-bottom: 1px solid #E3EBE8;
            font-size: 10px;
            text-align: center;
        }
        .date-col {
            width: 48px;
            font-weight: bold;
            text-align: center;
        }
        .sugar-col {
            width: 55px;
        }
        .dose-col {
            width: 55px;
        }
        .notes-col {
            text-align: left;
            padding-left: 8px;
            color: #4B5F59;
        }
        .high-badge {
            background-color: #FDEBDD;
            color: #9A3412;
            font-weight: bold;
            padding: 2px 4px;
            border-radius: 4px;
        }
        .check-box {
            border: 1px dashed #C2410C;
            color: #C2410C;
            font-weight: bold;
            padding: 2px 4px;
            border-radius: 4px;
        }
        .stats-summary {
            margin-top: 15px;
            display: table;
            width: 100%;
        }
        .stat-box {
            display: table-cell;
            width: 50%;
            background-color: #F4F7F6;
            padding: 10px;
            border-radius: 8px;
            margin-right: 10px;
        }
        .stat-label {
            font-size: 10px;
            color: #4B5F59;
            margin-bottom: 2px;
        }
        .stat-val {
            font-size: 16px;
            font-weight: bold;
            color: #12201C;
        }
        .legend {
            margin-top: 14px;
            font-size: 9px;
            color: #4B5F59;
        }
        .footer {
            margin-top: 25px;
            font-size: 9px;
            color: #4B5F59;
            text-align: center;
            border-top: 1px solid #E3EBE8;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">Daily Blood Sugar &amp; Insulin Log</h1>
        <p class="subtitle">Patient: <strong>{{ $patient->name }}</strong> &bull; Month: <strong>{{ $monthDate->format('F Y') }}</strong></p>
        <div class="info-bar">
            Target Range: {{ $patient->target_low }}–{{ $patient->target_high }} mg/dL &bull; Usual Morning Dose: {{ $patient->usual_morning_dose ? $patient->usual_morning_dose . ' u' : '--' }} &bull; Usual Night Dose: {{ $patient->usual_night_dose ? $patient->usual_night_dose . ' u' : '--' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="date-col">DATE</th>
                <th class="sugar-col">BREAKFAST</th>
                <th class="dose-col">INS. AM</th>
                <th class="sugar-col">LUNCH</th>
                <th class="sugar-col">DINNER</th>
                <th class="dose-col">INS. PM</th>
                <th class="notes-col">NOTES / REMARKS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                @php
                    $targetHigh = $patient->target_high ?? 180;
                    $isNashtaHigh = $log->nashta_sugar !== null && $log->nashta_sugar > $targetHigh;
                    $isLunchHigh = $log->lunch_sugar !== null && $log->lunch_sugar > $targetHigh;
                    $isRaatHigh = $log->dinner_sugar !== null && $log->dinner_sugar > $targetHigh;
                @endphp
                <tr>
                    <td class="date-col">{{ $log->log_date->format('j M') }}</td>
                    <td class="sugar-col">
                        @if($log->nashta_sugar !== null)
                            @if($isNashtaHigh)
                                <span class="high-badge">{{ $log->nashta_sugar }}</span>
                            @else
                                {{ $log->nashta_sugar }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="dose-col">
                        @if($log->needs_check && $log->insulin_morning === null)
                            <span class="check-box">?</span>
                        @elseif($log->insulin_morning !== null)
                            {{ $log->insulin_morning }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="sugar-col">
                        @if($log->lunch_sugar !== null)
                            @if($isLunchHigh)
                                <span class="high-badge">{{ $log->lunch_sugar }}</span>
                            @else
                                {{ $log->lunch_sugar }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="sugar-col">
                        @if($log->dinner_sugar !== null)
                            @if($isRaatHigh)
                                <span class="high-badge">{{ $log->dinner_sugar }}</span>
                            @else
                                {{ $log->dinner_sugar }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="dose-col">
                        @if($log->needs_check && $log->insulin_night == 6)
                            <span class="check-box">6?</span>
                        @elseif($log->insulin_night !== null)
                            {{ $log->insulin_night }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="notes-col">
                        @if($log->needs_check)
                            <strong>[Check]</strong> {{ $log->notes }}
                        @else
                            {{ $log->notes ?: '-' }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="padding: 20px;">No entries logged for this month.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="legend">
        <strong>Legend:</strong> High sugar readings (&gt;{{ $patient->target_high }} mg/dL) highlighted in orange. [?] represents unclear entry requiring check.
    </div>

    <table style="width: 100%; margin-top: 15px;">
        <tr>
            <td style="width: 50%; background-color: #F4F7F6; padding: 10px; border: none; text-align: left; border-radius: 6px;">
                <div class="stat-label">Average Breakfast Sugar</div>
                <div class="stat-val">{{ $avgNashta ? $avgNashta . ' mg/dL' : '--' }}</div>
            </td>
            <td style="width: 10px; border: none;"></td>
            <td style="width: 50%; background-color: #F4F7F6; padding: 10px; border: none; text-align: left; border-radius: 6px;">
                <div class="stat-label">Average Dinner Sugar</div>
                <div class="stat-val">{{ $avgRaat ? $avgRaat . ' mg/dL' : '--' }}</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Generated by SugarSync on {{ date('j M Y, h:i A') }} &bull; Page 1 of 1
    </div>
</body>
</html>
