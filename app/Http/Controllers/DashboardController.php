<?php

namespace App\Http\Controllers;

use App\Models\DailyLog;
use App\Models\Patient;
use App\Services\StatsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected StatsService $statsService
    ) {}

    /**
     * Caregiver Home Screen (Section 5 Screen 2).
     */
    public function home(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->isPatient()) {
            return redirect()->route('patient.dashboard');
        }

        $patients = Patient::where('caregiver_id', $user->id)->orderBy('name')->get();

        if ($patients->isEmpty()) {
            return redirect()->route('patients.create')
                ->with('info', 'Please add your first patient to begin.');
        }

        // Active patient resolution
        $activePatientId = $request->input('patient_id') ?? session('active_patient_id') ?? $patients->first()->id;
        $activePatient = $patients->firstWhere('id', $activePatientId) ?? $patients->first();
        session(['active_patient_id' => $activePatient->id]);

        // Get the latest log or today's log
        $latestLog = DailyLog::where('patient_id', $activePatient->id)->orderBy('log_date', 'desc')->first();

        // Target range
        $targetLow = $activePatient->target_low ?? 80;
        $targetHigh = $activePatient->target_high ?? 180;

        // Date filtering support for Home screen
        $selectedDateParam = $request->input('date');
        if ($selectedDateParam) {
            $displayDate = Carbon::parse($selectedDateParam);
            $displayLog = DailyLog::where('patient_id', $activePatient->id)
                ->whereDate('log_date', $displayDate->toDateString())
                ->first();
        } else {
            // Default to latest log if available, or today
            $displayLog = $latestLog;
            $displayDate = $displayLog ? Carbon::parse($displayLog->log_date) : Carbon::today();
        }

        // Available recent dates with readings for quick selector
        $recentLoggedDates = DailyLog::where('patient_id', $activePatient->id)
            ->orderBy('log_date', 'desc')
            ->pluck('log_date')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->take(15)
            ->all();

        // Compute today's readings, average, and total insulin
        $readingsToday = [];
        $totalInsulin = 0.0;

        if ($displayLog) {
            $readingsToday = $displayLog->readings;
            $totalInsulin = $displayLog->total_insulin;
        }

        $readingsCountToday = count($readingsToday);
        $avgToday = $readingsCountToday > 0 ? (int) round(array_sum(array_column($readingsToday, 'sugar')) / $readingsCountToday) : null;

        // Determine latest reading for hero card
        $latestReading = null;
        if (!empty($readingsToday)) {
            // Pick last logged reading in chronological order (Dinner, then Lunch, then Nashta)
            $lastItem = end($readingsToday);
            $latestReading = [
                'sugar' => $lastItem['sugar'],
                'meal_label' => $lastItem['label'],
                'timing' => $lastItem['timing'],
                'status' => $lastItem['status'],
                'insulin' => $lastItem['insulin'],
                'time_text' => $lastItem['label'] . ' (' . ($lastItem['timing'] === 'before' ? 'Before meal' : 'After meal') . ')',
            ];
        }

        return view('dashboard.home', [
            'patient' => $activePatient,
            'patients' => $patients,
            'displayDate' => $displayDate,
            'latestReading' => $latestReading,
            'readingsCountToday' => $readingsCountToday,
            'avgToday' => $avgToday,
            'totalInsulin' => $totalInsulin,
            'readingsToday' => $readingsToday,
            'displayLog' => $displayLog,
            'recentLoggedDates' => $recentLoggedDates,
            'targetLow' => $targetLow,
            'targetHigh' => $targetHigh,
        ]);
    }

    /**
     * History Screen (Section 5 Screen 4).
     */
    public function history(Request $request, ?Patient $patient = null): View|RedirectResponse
    {
        $user = Auth::user();
        $patient = $this->resolvePatient($user, $patient);

        if (!$patient) {
            return redirect()->route('dashboard');
        }

        $days = (int) $request->input('days', 7);
        if (!in_array($days, [7, 30])) {
            $days = 7;
        }

        $stats = $this->statsService->calculateStats($patient, $days);

        // Fetch logs for the grouped list
        $logs = DailyLog::where('patient_id', $patient->id)
            ->orderBy('log_date', 'desc')
            ->take($days)
            ->get();

        return view('dashboard.history', [
            'patient' => $patient,
            'days' => $days,
            'stats' => $stats,
            'logs' => $logs,
        ]);
    }

    /**
     * Log Sheet View Screen (Section 5 Screen 5).
     */
    public function sheet(Request $request, ?Patient $patient = null): View|RedirectResponse
    {
        $user = Auth::user();
        $patient = $this->resolvePatient($user, $patient);

        if (!$patient) {
            return redirect()->route('dashboard');
        }

        // Determine month and year or specific date filter
        $monthParam = $request->input('month');
        $yearParam = $request->input('year');
        $dateParam = $request->input('date');

        if ($dateParam) {
            $parsedDate = Carbon::parse($dateParam);
            $currentMonth = $parsedDate->month;
            $currentYear = $parsedDate->year;
        } elseif ($monthParam && $yearParam) {
            $currentMonth = (int) $monthParam;
            $currentYear = (int) $yearParam;
        } else {
            // Default to the month of the patient's latest log or current month
            $latestLog = DailyLog::where('patient_id', $patient->id)->orderBy('log_date', 'desc')->first();
            if ($latestLog) {
                $currentMonth = $latestLog->log_date->month;
                $currentYear = $latestLog->log_date->year;
            } else {
                $currentMonth = Carbon::now()->month;
                $currentYear = Carbon::now()->year;
            }
        }

        $monthDate = Carbon::createFromDate($currentYear, $currentMonth, 1);
        $prevMonthDate = $monthDate->copy()->subMonth();
        $nextMonthDate = $monthDate->copy()->addMonth();

        $query = DailyLog::where('patient_id', $patient->id)
            ->whereYear('log_date', $currentYear)
            ->whereMonth('log_date', $currentMonth)
            ->orderBy('log_date', 'asc');

        if ($dateParam) {
            $query->whereDate('log_date', $dateParam);
        }

        $logs = $query->get();

        // Calculate averages for Nashta and Raat for this sheet
        $nashtaReadings = $logs->whereNotNull('nashta_sugar')->pluck('nashta_sugar')->all();
        $dinnerReadings = $logs->whereNotNull('dinner_sugar')->pluck('dinner_sugar')->all();

        $avgNashta = !empty($nashtaReadings) ? (int) round(array_sum($nashtaReadings) / count($nashtaReadings)) : null;
        $avgRaat = !empty($dinnerReadings) ? (int) round(array_sum($dinnerReadings) / count($dinnerReadings)) : null;

        // Group available months for quick switcher
        $allLogs = DailyLog::where('patient_id', $patient->id)->orderBy('log_date', 'desc')->get();
        $availableMonths = $allLogs->groupBy(fn($item) => $item->log_date->format('Y-m'))
            ->map(function ($group) {
                $item = $group->first();
                return [
                    'year' => $item->log_date->year,
                    'month' => $item->log_date->month,
                    'label' => $item->log_date->format('F Y'),
                    'count' => $group->count(),
                    'val' => $item->log_date->format('Y-m'),
                ];
            })->values();

        return view('dashboard.sheet', [
            'patient' => $patient,
            'monthDate' => $monthDate,
            'prevMonthDate' => $prevMonthDate,
            'nextMonthDate' => $nextMonthDate,
            'logs' => $logs,
            'avgNashta' => $avgNashta,
            'avgRaat' => $avgRaat,
            'currentMonth' => $currentMonth,
            'currentYear' => $currentYear,
            'selectedDate' => $dateParam,
            'availableMonths' => $availableMonths,
        ]);
    }

    /**
     * Export Log Sheet to PDF (Section 5 Screen 5 / Section 7).
     */
    public function exportPdf(Request $request, Patient $patient): Response
    {
        $user = Auth::user();
        $this->authorizePatientAccess($user, $patient);

        $month = (int) $request->input('month');
        $year = (int) $request->input('year');

        if (!$month || !$year) {
            $latestLog = DailyLog::where('patient_id', $patient->id)->orderBy('log_date', 'desc')->first();
            $month = $latestLog ? $latestLog->log_date->month : Carbon::now()->month;
            $year = $latestLog ? $latestLog->log_date->year : Carbon::now()->year;
        }

        $monthDate = Carbon::createFromDate($year, $month, 1);

        $logs = DailyLog::where('patient_id', $patient->id)
            ->whereYear('log_date', $year)
            ->whereMonth('log_date', $month)
            ->orderBy('log_date', 'asc')
            ->get();

        $nashtaReadings = $logs->whereNotNull('nashta_sugar')->pluck('nashta_sugar')->all();
        $dinnerReadings = $logs->whereNotNull('dinner_sugar')->pluck('dinner_sugar')->all();

        $avgNashta = !empty($nashtaReadings) ? (int) round(array_sum($nashtaReadings) / count($nashtaReadings)) : null;
        $avgRaat = !empty($dinnerReadings) ? (int) round(array_sum($dinnerReadings) / count($dinnerReadings)) : null;

        $pdf = Pdf::loadView('pdf.log-sheet', [
            'patient' => $patient,
            'monthDate' => $monthDate,
            'logs' => $logs,
            'avgNashta' => $avgNashta,
            'avgRaat' => $avgRaat,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $fileName = 'blood-sugar-log-' . Str::slug($patient->name) . '-' . $monthDate->format('M-Y') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Patient Dashboard Screen (Section 5 Screen 7).
     */
    public function patientDashboard(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->isCaregiver()) {
            // If caregiver visits patient dashboard view, use active patient
            $patient = Patient::where('caregiver_id', $user->id)
                ->where('id', session('active_patient_id'))
                ->first() ?? Patient::where('caregiver_id', $user->id)->first();
        } else {
            $patient = Patient::where('user_id', $user->id)->first();
        }

        if (!$patient) {
            abort(404, 'Patient profile not found.');
        }

        // Stats across all records or selected period (7 or 30 days)
        $days = $request->input('days');
        $daysInt = $days ? (int) $days : null;

        $stats = $this->statsService->calculateStats($patient, $daysInt);

        // Date range display label
        $logs = DailyLog::where('patient_id', $patient->id)->orderBy('log_date', 'asc')->get();
        $dateRangeText = 'All readings';
        if ($logs->isNotEmpty()) {
            $first = $logs->first()->log_date->format('j M');
            $last = $logs->last()->log_date->format('j M Y');
            $dateRangeText = "{$first} – {$last}";
        }

        return view('dashboard.patient', [
            'patient' => $patient,
            'stats' => $stats,
            'dateRangeText' => $dateRangeText,
        ]);
    }

    private function resolvePatient($user, ?Patient $patient = null): ?Patient
    {
        if ($patient) {
            $this->authorizePatientAccess($user, $patient);
            return $patient;
        }

        if ($user->isPatient()) {
            return Patient::where('user_id', $user->id)->first();
        }

        $activeId = session('active_patient_id');
        if ($activeId) {
            $p = Patient::where('caregiver_id', $user->id)->where('id', $activeId)->first();
            if ($p) return $p;
        }

        return Patient::where('caregiver_id', $user->id)->first();
    }

    private function authorizePatientAccess($user, Patient $patient): void
    {
        if ($user->isCaregiver() && $patient->caregiver_id === $user->id) {
            return;
        }

        if ($user->isPatient() && $patient->user_id === $user->id) {
            return;
        }

        abort(403, 'Unauthorized access to this patient record.');
    }
}
