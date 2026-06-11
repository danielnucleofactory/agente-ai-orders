<?php

namespace Tests\Unit;

use App\Exceptions\PorthTemporarilyBlockedException;
use App\Models\PurchaseOrder;
use App\Services\PorthApiService;
use App\Services\PorthImportService;
use App\Services\PorthSyncUpdatedService;
use Carbon\Carbon;
use Mockery;
use Tests\TestCase;

class PorthSyncUpdatedServiceTest extends TestCase
{
    public function test_sync_range_only_fetches_details_for_linked_purchase_orders(): void
    {
        $po = new PurchaseOrder(['porth_id' => 'linked-porth-id']);

        $api = Mockery::mock(PorthApiService::class);
        $api->shouldReceive('listLastUpdated')
            ->once()
            ->andReturn(['unlinked-porth-id', 'linked-porth-id']);
        $api->shouldReceive('getShipmentById')
            ->once()
            ->with('linked-porth-id')
            ->andReturn(['id' => 'linked-porth-id']);

        $importer = Mockery::mock(PorthImportService::class);
        $importer->shouldReceive('importShipment')
            ->once()
            ->with(['id' => 'linked-porth-id'])
            ->andReturn($po);

        $service = new class($api, $importer) extends PorthSyncUpdatedService {
            protected function linkedPurchaseOrderPorthIds(array $ids): array
            {
                return ['linked-porth-id'];
            }
        };

        $summary = $service->syncRange(Carbon::parse('2026-06-11 10:00:00'), Carbon::parse('2026-06-11 12:00:00'));

        $this->assertSame(['linked-porth-id'], $summary['ids']);
        $this->assertSame(2, $summary['fetched_ids_count']);
        $this->assertSame(1, $summary['filtered_out_unlinked']);
        $this->assertSame('imported', $summary['results']['linked-porth-id']['status']);
    }

    public function test_sync_range_stops_when_porth_temporarily_blocks_requests(): void
    {
        $api = Mockery::mock(PorthApiService::class);
        $api->shouldReceive('listLastUpdated')
            ->once()
            ->andReturn(['blocked-porth-id', 'next-porth-id']);
        $api->shouldReceive('getShipmentById')
            ->once()
            ->with('blocked-porth-id')
            ->andThrow(new PorthTemporarilyBlockedException(300));

        $importer = Mockery::mock(PorthImportService::class);
        $importer->shouldNotReceive('importShipment');

        $service = new class($api, $importer) extends PorthSyncUpdatedService {
            protected function linkedPurchaseOrderPorthIds(array $ids): array
            {
                return ['blocked-porth-id', 'next-porth-id'];
            }
        };

        $summary = $service->syncRange(Carbon::parse('2026-06-11 10:00:00'), Carbon::parse('2026-06-11 12:00:00'));

        $this->assertSame(['blocked-porth-id', 'next-porth-id'], $summary['ids']);
        $this->assertSame('temporarily_blocked', $summary['results']['blocked-porth-id']['status']);
        $this->assertSame(300, $summary['results']['blocked-porth-id']['retry_after_seconds']);
        $this->assertArrayNotHasKey('next-porth-id', $summary['results']);
    }
}
