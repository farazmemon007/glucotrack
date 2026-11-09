<?php

namespace Tests\Feature;

use App\Models\DailyLog;
use App\Models\Patient;
use App\Models\User;
use App\Services\StatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatsCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_section_7_and_8_stats_calculations_match_expected_results(): void
    {
        // Run database seed
        $this->seed();

        $patient = Patient::where('name', 'Tariq Mahmood')->first();
        $this->assertNotNull($patient);

        $statsService = new StatsService();
        $stats = $statsService->calculateStats($patient);

        // Section 8 expected results:
        // "24 readings, 12 in range (50%), average 187, highest 273 on 12 Jul, lowest 125 on 16 Jul, Nashta average 205, Raat average 173. Use these numbers as a test."
        $this->assertEquals(24, $stats['total_readings'], 'Total readings count should be 24');
        $this->assertEquals(12, $stats['in_range_count'], 'In-range readings count should be 12');
        $this->assertEquals(50, $stats['time_in_range_percent'], 'Time in range percent should be 50%');
        $this->assertEquals(187, $stats['average'], 'Overall average should be 187');

        $this->assertEquals(273, $stats['highest']['sugar'], 'Highest reading should be 273');
        $this->assertStringContainsString('12 Jul', $stats['highest']['date'], 'Highest reading date should be 12 Jul');
        $this->assertEquals('Nashta', $stats['highest']['meal'], 'Highest reading meal should be Nashta');

        $this->assertEquals(125, $stats['lowest']['sugar'], 'Lowest reading should be 125');
        $this->assertStringContainsString('16 Jul', $stats['lowest']['date'], 'Lowest reading date should be 16 Jul');
        $this->assertEquals('Raat', $stats['lowest']['meal'], 'Lowest reading meal should be Raat');

        $this->assertEquals(205, $stats['meal_averages']['nashta']['average'], 'Nashta average should be 205');
        $this->assertEquals(12, $stats['meal_averages']['nashta']['count'], 'Nashta count should be 12');

        $this->assertEquals(173, $stats['meal_averages']['dinner']['average'], 'Raat average should be 173');
        $this->assertEquals(11, $stats['meal_averages']['dinner']['count'], 'Raat count should be 11');

        $this->assertEquals(137, $stats['meal_averages']['lunch']['average'], 'Lunch average should be 137');
        $this->assertEquals(1, $stats['meal_averages']['lunch']['count'], 'Lunch count should be 1');

        // Verify status rules:
        // Status per reading: Low below target_low, In range from target_low to target_high inclusive, High above target_high
        $this->assertEquals('low', $statsService->getStatus(79, 80, 180));
        $this->assertEquals('in_range', $statsService->getStatus(80, 80, 180));
        $this->assertEquals('in_range', $statsService->getStatus(180, 80, 180));
        $this->assertEquals('high', $statsService->getStatus(181, 80, 180));

        // Verify good days:
        // A good day is a day where every recorded sugar reading is in range
        // In the data, 15 Jul (Nashta 163, Raat 140) and 16 Jul (Nashta 174, Raat 125) are good days!
        $this->assertNotEmpty($stats['good_days']);
        $goodDates = array_column($stats['good_days'], 'date');
        $this->assertContains('16 Jul', $goodDates);
        $this->assertContains('15 Jul', $goodDates);

        // Verify needs attention:
        // Should include highest reading 273 and check / missing items
        $this->assertNotEmpty($stats['needs_attention']);
    }
}
