<?php

namespace Tests\Feature;

use App\Http\Controllers\Backend\TrackConversionController;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\TrackConversionTableQuery;
use App\Support\Tracking\TrackingPeriod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use Tests\Support\CreatesTrackConversionsTable;
use Tests\Support\InteractsWithAttributionTestMode;
use Tests\TestCase;

/**
 * Filtro de periodo (hoy, 7/30/90 dias, este mes, mes pasado, un mes elegido, rango de fechas, todo) de la pantalla
 * Google Ads (Seguimiento de conversiones): el resumen, la tabla y la exportacion usan el mismo periodo.
 */
class TrackConversionPeriodTest extends TestCase
{
    use CreatesTrackConversionsTable;
    use InteractsWithAttributionTestMode;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareTrackConversionsTable();
        $this->prepareAdminUsersTables();
        config(['app.timezone' => 'UTC']);
        Carbon::setTestNow(Carbon::create(2026, 11, 15, 12, 0, 0, 'UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** Eventos en fechas conocidas (hoy simulado = 2026-11-15 12:00). */
    private function seedEvents(): void
    {
        $this->makeEvent(['type' => 'page_visit', 'session_id' => 'A'], '2026-09-20 10:00:00');
        $this->makeEvent(['type' => 'page_visit', 'session_id' => 'B'], '2026-10-05 10:00:00');
        $this->makeEvent(['type' => 'whatsapp_click', 'session_id' => 'B'], '2026-10-31 23:59:59');
        $this->makeEvent(['type' => 'page_visit', 'session_id' => 'C'], '2026-11-01 00:00:00');
        $this->makeEvent(['type' => 'whatsapp_click', 'session_id' => 'C'], '2026-11-10 09:00:00');
        $this->makeEvent(['type' => 'whatsapp_click', 'session_id' => 'D'], '2026-11-15 08:00:00');
    }

    private function summary(array $query): array
    {
        return app(TrackConversionController::class)->summary(Request::create('/admin/x', 'GET', $query))->getData(true);
    }

    private function total(array $summary): int
    {
        return $summary['totals']['visits'] + $summary['totals']['conversions'];
    }

    // ------------------------------------------------------------------ TrackingPeriod

    public function test_presets_resolve_to_the_expected_windows(): void
    {
        $this->assertSame(['range' => 'this_month', 'from' => '2026-11-01', 'to' => '2026-11-30'], TrackingPeriod::resolve('this_month')->toArray());
        $this->assertSame(['range' => 'last_month', 'from' => '2026-10-01', 'to' => '2026-10-31'], TrackingPeriod::resolve('last_month')->toArray());
        $this->assertSame(['range' => 'today', 'from' => '2026-11-15', 'to' => null], TrackingPeriod::resolve('today')->toArray());
        $this->assertSame(['range' => '7', 'from' => '2026-11-08', 'to' => null], TrackingPeriod::resolve('7')->toArray());
        $this->assertSame(['range' => '30', 'from' => '2026-10-16', 'to' => null], TrackingPeriod::resolve('30')->toArray());
        $this->assertSame(['range' => '90', 'from' => '2026-08-17', 'to' => null], TrackingPeriod::resolve('90')->toArray());
        $this->assertSame(['range' => 'all', 'from' => null, 'to' => null], TrackingPeriod::resolve('all')->toArray());
    }

    public function test_last_month_crosses_the_year_boundary(): void
    {
        Carbon::setTestNow(Carbon::create(2027, 1, 20, 12, 0, 0, 'UTC'));

        $this->assertSame(['range' => 'last_month', 'from' => '2026-12-01', 'to' => '2026-12-31'], TrackingPeriod::resolve('last_month')->toArray());
    }

    public function test_a_chosen_month_covers_the_whole_month(): void
    {
        $period = TrackingPeriod::resolve('month', '2026-02');

        $this->assertSame('2026-02-01 00:00:00', $period->from->format('Y-m-d H:i:s'));
        $this->assertSame('2026-02-28 23:59:59', $period->to->format('Y-m-d H:i:s'));
    }

    public function test_invalid_month_or_dates_fall_back_to_30_days(): void
    {
        foreach ([['month', 'noviembre'], ['month', '2026-13'], ['month', null], ['month', '1999-05']] as [$range, $value]) {
            $this->assertSame('30', TrackingPeriod::resolve($range, $value)->range, "month=" . var_export($value, true));
        }
        $this->assertSame('30', TrackingPeriod::resolve('custom', null, 'ayer', '31/10/2026')->range);
        $this->assertSame('30', TrackingPeriod::resolve('custom', null, '2026-02-31', null)->range);
        $this->assertSame('30', TrackingPeriod::resolve('inventado')->range);
    }

    public function test_custom_range_orders_swapped_dates_and_allows_an_open_end(): void
    {
        $swapped = TrackingPeriod::resolve('custom', null, '2026-11-10', '2026-10-31');
        $this->assertSame('2026-10-31 00:00:00', $swapped->from->format('Y-m-d H:i:s'));
        $this->assertSame('2026-11-10 23:59:59', $swapped->to->format('Y-m-d H:i:s'));

        $onlyFrom = TrackingPeriod::resolve('custom', null, '2026-11-05', null);
        $this->assertSame('2026-11-05', $onlyFrom->from->format('Y-m-d'));
        $this->assertNull($onlyFrom->to);

        $onlyTo = TrackingPeriod::resolve('custom', null, null, '2026-11-05');
        $this->assertNull($onlyTo->from);
        $this->assertSame('2026-11-05 23:59:59', $onlyTo->to->format('Y-m-d H:i:s'));
    }

    // ------------------------------------------------------------------ resumen

    public function test_summary_filters_by_month_last_month_and_this_month(): void
    {
        $this->seedEvents();

        // Octubre: 05/10 (visita) y 31/10 23:59:59 (conversion).
        $october = $this->summary(['range' => 'month', 'month' => '2026-10']);
        $this->assertSame(2, $this->total($october));
        $this->assertSame(1, $october['totals']['visits']);
        $this->assertSame(1, $october['totals']['conversions']);
        $this->assertSame('2026-10-01', $october['period']['from']);
        $this->assertSame('2026-10-31', $october['period']['to']);

        $this->assertSame(2, $this->total($this->summary(['range' => 'last_month'])));

        // Noviembre: 01/11 00:00:00, 10/11 y 15/11.
        $this->assertSame(3, $this->total($this->summary(['range' => 'this_month'])));
        $this->assertSame(1, $this->total($this->summary(['range' => 'month', 'month' => '2026-09'])));
        $this->assertSame(0, $this->total($this->summary(['range' => 'month', 'month' => '2026-08'])));
    }

    public function test_summary_custom_range_is_inclusive_on_both_days(): void
    {
        $this->seedEvents();

        $both = $this->summary(['range' => 'custom', 'from' => '2026-10-31', 'to' => '2026-11-01']);
        $this->assertSame(2, $this->total($both));   // 31/10 23:59:59 y 01/11 00:00:00

        $this->assertSame(1, $this->total($this->summary(['range' => 'custom', 'from' => '2026-11-15', 'to' => '2026-11-15'])));
        $this->assertSame(5, $this->total($this->summary(['range' => 'custom', 'from' => '2026-10-01'])));
        $this->assertSame(3, $this->total($this->summary(['range' => 'custom', 'to' => '2026-10-31'])));   // 20/09, 05/10 y 31/10 23:59:59
    }

    public function test_summary_presets_and_default_keep_working(): void
    {
        $this->seedEvents();

        $this->assertSame(6, $this->total($this->summary(['range' => 'all'])));
        $this->assertSame('30', $this->summary([])['range']);
        $this->assertSame(4, $this->total($this->summary([])));                 // ultimos 30 dias (desde el 16/10): 31/10, 01/11, 10/11 y 15/11
        $this->assertSame(1, $this->total($this->summary(['range' => 'today'])));
        $this->assertSame('30', $this->summary(['range' => 'month', 'month' => 'x'])['range']);
    }

    // ------------------------------------------------------------------ tabla y exportacion

    public function test_table_and_export_follow_the_period_including_tab_counts(): void
    {
        $this->seedEvents();
        $table = fn () => app(TrackConversionTableQuery::class);
        $period = TrackingPeriod::resolve('month', '2026-10');

        $limited = $table()->forPeriod($period->from, $period->to);
        $result = $limited->paginate(new AdminTableRequest(perPage: 100));

        $this->assertSame(2, $result['total']);
        $this->assertCount(2, $result['rows']);
        $counts = collect($result['filters'])->pluck('count', 'key');
        $this->assertSame(2, $counts['todas']);
        $this->assertSame(1, $counts['visitas']);
        $this->assertSame(1, $counts['whatsapp']);

        $this->assertCount(2, $table()->forPeriod($period->from, $period->to)->exportRows(new AdminTableRequest())->all());
        // Sin periodo no hay limite (comportamiento anterior).
        $this->assertCount(6, $table()->paginate(new AdminTableRequest(perPage: 100))['rows']);
    }

    public function test_table_endpoint_applies_the_period_only_when_a_range_is_sent(): void
    {
        $this->seedEvents();
        $controller = app(TrackConversionController::class);
        $rows = fn (array $q) => count($controller->tableData(Request::create('/admin/x', 'GET', $q + ['per_page' => 100]), app(TrackConversionTableQuery::class))->getData(true)['rows']);

        $this->assertSame(2, $rows(['range' => 'month', 'month' => '2026-10']));
        $this->assertSame(1, $rows(['range' => 'custom', 'from' => '2026-11-15', 'to' => '2026-11-15']));
        $this->assertSame(6, $rows([]));
    }

    // ------------------------------------------------------------------ pantalla

    public function test_the_page_offers_month_and_date_range_controls(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get('/admin/track-conversion')
            ->assertOk()
            ->assertSee('id="tc-range"', false)
            ->assertSee('value="this_month"', false)
            ->assertSee('value="last_month"', false)
            ->assertSee('value="month"', false)
            ->assertSee('value="custom"', false)
            ->assertSee('id="tc-month"', false)
            ->assertSee('id="tc-from"', false)
            ->assertSee('id="tc-to"', false);
    }
}
