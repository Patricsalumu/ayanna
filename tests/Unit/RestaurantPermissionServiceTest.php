<?php

namespace Tests\Unit;

use App\Services\PermissionService;
use PHPUnit\Framework\TestCase;

class RestaurantPermissionServiceTest extends TestCase
{
    public function test_waitress_can_access_only_her_tables(): void
    {
        $service = new PermissionService();

        $waitress = new \stdClass();
        $waitress->id = 7;
        $waitress->role = 'Serveuse';

        $ownTable = new \stdClass();
        $ownTable->id = 12;
        $ownTable->serveuse_id = 7;

        $otherTable = new \stdClass();
        $otherTable->id = 13;
        $otherTable->serveuse_id = 8;

        $this->assertTrue($service->canAccessTable($waitress, $ownTable));
        $this->assertFalse($service->canAccessTable($waitress, $otherTable));
    }

    public function test_cashier_can_access_his_tables_but_waitress_cannot_validate_payment(): void
    {
        $service = new PermissionService();

        $cashier = new \stdClass();
        $cashier->id = 4;
        $cashier->role = 'Caissier';

        $waitress = new \stdClass();
        $waitress->id = 7;
        $waitress->role = 'Serveuse';

        $table = new \stdClass();
        $table->id = 21;
        $table->serveuse_id = 4;

        $this->assertTrue($service->canAccessTable($cashier, $table));
        $this->assertFalse($service->canValidatePayment($waitress));
        $this->assertTrue($service->canValidatePayment($cashier));
    }

    public function test_cashier_can_apply_discount(): void
    {
        $service = new PermissionService();

        $cashier = new \stdClass();
        $cashier->id = 4;
        $cashier->role = 'Caissier';

        $this->assertTrue($service->canApplyDiscount($cashier));
        $this->assertTrue($service->canTransferTableProducts($cashier));
        $this->assertTrue($service->isCashier($cashier));
    }

    public function test_supervisor_is_cashier_like_but_read_only(): void
    {
        $service = new PermissionService();

        $supervisor = new \stdClass();
        $supervisor->id = 9;
        $supervisor->role = 'Superviseur';

        $table = new \stdClass();
        $table->id = 12;
        $table->serveuse_id = 9;

        $this->assertTrue($service->isSupervisor($supervisor));
        $this->assertTrue($service->isCashier($supervisor));
        $this->assertTrue($service->canAccessTable($supervisor, $table));
        $this->assertFalse($service->canOpenTable($supervisor));
        $this->assertFalse($service->canValidatePayment($supervisor));
        $this->assertTrue($service->canAddProductsToTable($supervisor));
        $this->assertTrue($service->canTransferTableProducts($supervisor));
        $this->assertFalse($service->canApplyDiscount($supervisor));
        $this->assertFalse($service->canManageSalesSession($supervisor));
    }

    public function test_waitress_is_auto_assigned_to_her_own_orders(): void
    {
        $service = new PermissionService();

        $waitress = new \stdClass();
        $waitress->id = 7;
        $waitress->role = 'Serveuse';

        $this->assertSame('7', $service->resolveServeuseId($waitress, null));
        $this->assertSame('7', $service->resolveServeuseId($waitress, '8'));
    }
}
