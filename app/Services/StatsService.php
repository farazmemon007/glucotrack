<?php

namespace App\Services;

use App\Models\DailyLog;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StatsService
{
    /**
     * Determine status for a blood sugar reading.
     */
    public function getStatus(?int $sugar, int $targetLow = 80, int $targetHigh = 180): ?string
    {
        if ($sugar === null) {
            return null;
        }

        if ($sugar < $targetLow) {
            return 'low';
        }

        if ($sugar <= $targetHigh) {
            return 'in_range';
        }

        return 'high';
    }

    /**
     * Get label for status.
     */
    public function getStatusLabel(?string $status): string
    {
        return match ($status) {
            'low' => 'Low',
            'in_range' => 'In range',
            'high' => 'High',
            'check' => 'Check',
            'missing' => 'Missing',
            default => 'Unknown',
        };
    }

    /**
     * Calculate all stats for a patient over a specified number of days or collection of logs.
     *
     * @param Patient $patient
     * @param int|null $days If provided, filters logs from (max_date - days + 1) or today - days
     * @param Collection|null $logs
     * @return array
     */
    public function calculateStats(Patient $patient, ?int $days = null, ?Collection $logs = null, ?string $startDate = null, ?string $endDate = null): array
    {
        if ($logs === null) {
            $query = DailyLog::where('patient_id', $patient->id)->orderBy('log_date', 'asc');

            if ($startDate !== null && $endDate !== null) {
                $query->where('log_date', '>=', $startDate)
                      ->where('log_date', '<=', $endDate);
            } elseif ($days !== null) {
                // If there are logs, get the most recent date as reference anchor so historic seed data (July 2026) filters correctly
                $latestLog = DailyLog::where('patient_id', $patient->id)->orderBy('log_date', 'desc')->first();
                if ($latestLog) {
                    $anchorDate = Carbon::parse($latestLog->log_date);
                    $calcStartDate = $anchorDate->copy()->subDays($days - 1)->startOfDay();
                    $query->where('log_date', '>=', $calcStartDate->toDateString())
                          ->where('log_date', '<=', $anchorDate->toDateString());
                } else {
                    $query->where('log_date', '>=', Carbon::now()->subDays($days - 1)->toDateString());
                }
            }

            $logs = $query->get();
        }

        $targetLow = $patient->target_low ?? 80;
        $targetHigh = $patient->target_high ?? 180;

        $allReadings = [];
        $nashtaReadings = [];
        $lunchReadings = [];
        $dinnerReadings = [];

        foreach ($logs as $log) {
            $dateStr = Carbon::parse($log->log_date)->format('j M');
            $fullDateStr = Carbon::parse($log->log_date)->format('j M Y');

            if ($log->nashta_sugar !== null) {
                $item = [
                    'sugar' => $log->nashta_sugar,
                    'meal' => 'Nashta',
                    'meal_key' => 'nashta',
                    'timing' => $log->nashta_timing ?? 'before',
                    'date' => $dateStr,
                    'full_date' => $fullDateStr,
                    'log_date' => $log->log_date,
                    'status' => $this->getStatus($log->nashta_sugar, $targetLow, $targetHigh),
                    'dose' => $log->insulin_morning,
                    'log' => $log,
                ];
                $allReadings[] = $item;
                $nashtaReadings[] = $item;
            }

            if ($log->lunch_sugar !== null) {
                $item = [
                    'sugar' => $log->lunch_sugar,
                    'meal' => 'Dopahar',
                    'meal_key' => 'lunch',
                    'timing' => $log->lunch_timing ?? 'before',
                    'date' => $dateStr,
                    'full_date' => $fullDateStr,
                    'log_date' => $log->log_date,
                    'status' => $this->getStatus($log->lunch_sugar, $targetLow, $targetHigh),
                    'dose' => null,
                    'log' => $log,
                ];
                $allReadings[] = $item;
                $lunchReadings[] = $item;
            }

            if ($log->dinner_sugar !== null) {
                $item = [
                    'sugar' => $log->dinner_sugar,
                    'meal' => 'Raat',
                    'meal_key' => 'dinner',
                    'timing' => $log->dinner_timing ?? 'before',
                    'date' => $dateStr,
                    'full_date' => $fullDateStr,
                    'log_date' => $log->log_date,
                    'status' => $this->getStatus($log->dinner_sugar, $targetLow, $targetHigh),
                    'dose' => $log->insulin_night,
                    'log' => $log,
                ];
                $allReadings[] = $item;
                $dinnerReadings[] = $item;
            }
        }

        $totalCount = count($allReadings);
        $inRangeCount = 0;
        $highCount = 0;
        $lowCount = 0;

        foreach ($allReadings as $r) {
            if ($r['status'] === 'in_range') {
                $inRangeCount++;
            } elseif ($r['status'] === 'high') {
                $highCount++;
            } elseif ($r['status'] === 'low') {
                $lowCount++;
            }
        }

        $timeInRangePercent = $totalCount > 0 ? (int) round(($inRangeCount / $totalCount) * 100) : 0;
        $highPercent = $totalCount > 0 ? (int) round(($highCount / $totalCount) * 100) : 0;
        $lowPercent = $totalCount > 0 ? (int) round(($lowCount / $totalCount) * 100) : 0;

        $totalSum = array_sum(array_column($allReadings, 'sugar'));
        $overallAverage = $totalCount > 0 ? (int) round($totalSum / $totalCount) : null;

        // Highest and lowest
        $highest = null;
        $lowest = null;

        if ($totalCount > 0) {
            $sorted = $allReadings;
            usort($sorted, fn($a, $b) => $b['sugar'] <=> $a['sugar']);
            $highest = $sorted[0];

            usort($sorted, fn($a, $b) => $a['sugar'] <=> $b['sugar']);
            $lowest = $sorted[0];
        }

        // Meal averages
        $nashtaCount = count($nashtaReadings);
        $nashtaAverage = $nashtaCount > 0 ? (int) round(array_sum(array_column($nashtaReadings, 'sugar')) / $nashtaCount) : null;

        $lunchCount = count($lunchReadings);
        $lunchAverage = $lunchCount > 0 ? (int) round(array_sum(array_column($lunchReadings, 'sugar')) / $lunchCount) : null;

        $dinnerCount = count($dinnerReadings);
        $dinnerAverage = $dinnerCount > 0 ? (int) round(array_sum(array_column($dinnerReadings, 'sugar')) / $dinnerCount) : null;

        // Good days: days where every recorded sugar reading is in range (at least 1 reading exists), newest first
        $goodDays = [];
        $logsByDateDesc = $logs->sortByDesc('log_date');

        foreach ($logsByDateDesc as $log) {
            $dayReadings = [];
            if ($log->nashta_sugar !== null) $dayReadings[] = $log->nashta_sugar;
            if ($log->lunch_sugar !== null) $dayReadings[] = $log->lunch_sugar;
            if ($log->dinner_sugar !== null) $dayReadings[] = $log->dinner_sugar;

            if (count($dayReadings) > 0) {
                $allDayInRange = true;
                foreach ($dayReadings as $val) {
                    if ($this->getStatus($val, $targetLow, $targetHigh) !== 'in_range') {
                        $allDayInRange = false;
                        break;
                    }
                }
                if ($allDayInRange) {
                    $details = [];
                    if ($log->nashta_sugar !== null) $details[] = "Nashta: {$log->nashta_sugar}";
                    if ($log->lunch_sugar !== null) $details[] = "Dopahar: {$log->lunch_sugar}";
                    if ($log->dinner_sugar !== null) $details[] = "Raat: {$log->dinner_sugar}";

                    $goodDays[] = [
                        'log' => $log,
                        'date' => Carbon::parse($log->log_date)->format('j M'),
                        'full_date' => Carbon::parse($log->log_date)->format('j M Y'),
                        'readings_count' => count($dayReadings),
                        'summary' => implode(' • ', $details),
                    ];
                }
            }
        }

        // Needs attention:
        // 1. Highest readings (especially > targetHigh)
        // 2. Days with missing expected entries or doses, or needs_check
        $needsAttention = [];

        // Check logs for missing expected meals (e.g. expected Nashta & Raat, or missing insulin) or needs_check
        foreach ($logsByDateDesc as $log) {
            $dateLabel = Carbon::parse($log->log_date)->format('j M');

            if ($log->needs_check) {
                $needsAttention[] = [
                    'date' => $dateLabel,
                    'title' => 'Unclear dose or value',
                    'detail' => $log->notes ?: 'Value needs review by doctor/caregiver',
                    'badge' => 'Check',
                    'badge_type' => 'check',
                ];
            }

            // High readings on this day
            if ($log->nashta_sugar !== null && $log->nashta_sugar > $targetHigh) {
                $needsAttention[] = [
                    'date' => $dateLabel,
                    'title' => "Nashta: {$log->nashta_sugar} mg/dL",
                    'detail' => 'Above target range (' . $targetHigh . ' mg/dL)',
                    'badge' => 'High',
                    'badge_type' => 'high',
                ];
            }

            if ($log->dinner_sugar !== null && $log->dinner_sugar > $targetHigh) {
                $needsAttention[] = [
                    'date' => $dateLabel,
                    'title' => "Raat: {$log->dinner_sugar} mg/dL",
                    'detail' => 'Above target range (' . $targetHigh . ' mg/dL)',
                    'badge' => 'High',
                    'badge_type' => 'high',
                ];
            }

            // Missing expected reading (e.g. dinner was missed or nashta was missed)
            if ($log->nashta_sugar !== null && $log->dinner_sugar === null) {
                $needsAttention[] = [
                    'date' => $dateLabel,
                    'title' => 'Raat reading missing',
                    'detail' => 'Evening blood sugar was not logged',
                    'badge' => 'Missing',
                    'badge_type' => 'missing',
                ];
            } elseif ($log->dinner_sugar !== null && $log->nashta_sugar === null) {
                $needsAttention[] = [
                    'date' => $dateLabel,
                    'title' => 'Nashta reading missing',
                    'detail' => 'Morning blood sugar was not logged',
                    'badge' => 'Missing',
                    'badge_type' => 'missing',
                ];
            }
        }

        // Limit needs attention to top 6 items
        $needsAttention = array_slice($needsAttention, 0, 6);

        // Trend data for line chart (day numbers, nashta, raat)
        // Sort logs by log_date ascending
        $logsAsc = $logs->sortBy('log_date');
        $trendDays = [];
        foreach ($logsAsc as $log) {
            $d = Carbon::parse($log->log_date);
            $trendDays[] = [
                'day' => (int) $d->format('j'),
                'date_label' => $d->format('j M'),
                'nashta' => $log->nashta_sugar,
                'raat' => $log->dinner_sugar,
                'dopahar' => $log->lunch_sugar,
            ];
        }

        // Bar chart data for 7-day view
        // Take the last 7 logged days or the range
        $recent7Logs = $logs->sortByDesc('log_date')->take(7)->reverse();
        $barChart = [];
        foreach ($recent7Logs as $log) {
            $d = Carbon::parse($log->log_date);
            $avg = $log->daily_average;
            $barStatus = $avg !== null ? $this->getStatus((int) $avg, $targetLow, $targetHigh) : 'missing';

            $barChart[] = [
                'weekday' => $d->format('D'),
                'day_num' => $d->format('j'),
                'date' => $d->format('j M'),
                'average' => $avg,
                'status' => $barStatus,
                'in_range' => $barStatus === 'in_range',
            ];
        }

        return [
            'total_readings' => $totalCount,
            'in_range_count' => $inRangeCount,
            'high_count' => $highCount,
            'low_count' => $lowCount,
            'time_in_range_percent' => $timeInRangePercent,
            'high_percent' => $highPercent,
            'low_percent' => $lowPercent,
            'average' => $overallAverage,
            'highest' => $highest,
            'lowest' => $lowest,
            'meal_averages' => [
                'nashta' => [
                    'average' => $nashtaAverage,
                    'count' => $nashtaCount,
                ],
                'lunch' => [
                    'average' => $lunchAverage,
                    'count' => $lunchCount,
                ],
                'dinner' => [
                    'average' => $dinnerAverage,
                    'count' => $dinnerCount,
                ],
            ],
            'good_days' => $goodDays,
            'needs_attention' => $needsAttention,
            'trend_days' => $trendDays,
            'bar_chart' => $barChart,
            'all_readings' => $allReadings,
        ];
    }
}
