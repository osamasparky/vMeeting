<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Tenancy\Models\Plan;
use App\Domains\Tenancy\Models\Subscription;
use App\Domains\Tenancy\Models\SubscriptionRequest;
use App\Domains\Tenancy\Services\SubscriptionPricingService;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeatSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Organization $organization;
    protected Plan $perSeatPlan;
    protected Plan $flatPlan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['email' => 'seat-admin@example.com']);
        $this->organization = app(CreateOrganizationAction::class)->execute(['name' => 'Seat Test Co'], $this->admin);

        $this->perSeatPlan = Plan::create([
            'name' => 'Team Per-Seat',
            'slug' => 'team-per-seat',
            'price' => 10.00,
            'is_per_seat' => true,
            'min_seats' => 2,
            'max_seats' => 100,
            'seat_limit' => 0,
            'max_offices' => 3,
            'room_limit' => 10,
            'storage_limit_gb' => 10,
            'features' => ['basic_chat', 'basic_presence'],
            'is_active' => true,
        ]);

        $this->flatPlan = Plan::create([
            'name' => 'Business Flat',
            'slug' => 'business-flat',
            'price' => 50.00,
            'is_per_seat' => false,
            'min_seats' => 1,
            'seat_limit' => 15,
            'max_offices' => 5,
            'room_limit' => 20,
            'storage_limit_gb' => 25,
            'features' => ['basic_chat', 'advanced_analytics'],
            'is_active' => true,
        ]);
    }

    public function test_pricing_service_calculates_correct_totals_for_per_seat_plans(): void
    {
        $service = new SubscriptionPricingService();

        // 2 seats monthly (min 2)
        $pricing2 = $service->calculatePrice($this->perSeatPlan, 2, 'monthly');
        $this->assertEquals(2, $pricing2['seats']);
        $this->assertEquals(20.00, $pricing2['total_usd']);
        $this->assertEquals(75.00, $pricing2['total_sar']);

        // 5 seats monthly
        $pricing5 = $service->calculatePrice($this->perSeatPlan, 5, 'monthly');
        $this->assertEquals(5, $pricing5['seats']);
        $this->assertEquals(50.00, $pricing5['total_usd']);
        $this->assertEquals(187.50, $pricing5['total_sar']);

        // 5 seats yearly (12 months)
        $pricingYearly = $service->calculatePrice($this->perSeatPlan, 5, 'yearly');
        $this->assertEquals(5, $pricingYearly['seats']);
        $this->assertEquals(600.00, $pricingYearly['total_usd']);
        $this->assertEquals(2250.00, $pricingYearly['total_sar']);

        // Flat plan (price is fixed regardless of seats parameter)
        $flatPricing = $service->calculatePrice($this->flatPlan, 10, 'monthly');
        $this->assertEquals(50.00, $flatPricing['total_usd']);
        $this->assertEquals(187.50, $flatPricing['total_sar']);
    }

    public function test_pricing_service_enforces_minimum_seats(): void
    {
        $service = new SubscriptionPricingService();

        // Requested 1 seat on a plan with min 2 seats -> automatically clamped to min 2
        $pricing = $service->calculatePrice($this->perSeatPlan, 1, 'monthly');
        $this->assertEquals(2, $pricing['seats']);
        $this->assertEquals(20.00, $pricing['total_usd']);

        $validation = $service->validateSeatQuantity($this->perSeatPlan, 1);
        $this->assertFalse($validation['valid']);
    }

    public function test_payment_page_loads_with_requested_seats(): void
    {
        $response = $this->actingAs($this->admin)->get(route('subscription.payment', [
            'plan' => $this->perSeatPlan->id,
            'seats' => 5,
            'cycle' => 'monthly',
        ]));

        $response->assertOk();
        $response->assertViewHas('requestedSeats', 5);
        $response->assertViewHas('priceUSD', 50.00);
        $response->assertViewHas('priceSAR', 187.50);
    }

    public function test_submit_bank_transfer_stores_seats_and_price_per_seat(): void
    {
        Storage::fake('public');

        $receipt = UploadedFile::fake()->create('receipt.pdf', 200, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('subscription.payment.submit', $this->perSeatPlan->id), [
            'sender_name' => 'Acme Corp',
            'bank_name' => 'Al Rajhi Bank',
            'transfer_reference' => 'TRF-SEAT-1001',
            'amount' => 187.50,
            'currency' => 'SAR',
            'billing_cycle' => 'monthly',
            'transfer_date' => now()->format('Y-m-d'),
            'seats' => 5,
            'request_type' => 'new_subscription',
            'receipt' => $receipt,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('subscription_requests', [
            'organization_id' => $this->organization->id,
            'plan_id' => $this->perSeatPlan->id,
            'seats' => 5,
            'price_per_seat' => 10.00,
            'amount' => 187.50,
            'transfer_reference' => 'TRF-SEAT-1001',
            'status' => 'pending',
        ]);
    }

    public function test_cannot_reduce_seats_below_active_members_count(): void
    {
        // Organization currently has 1 active member ($this->admin)
        // Add another member so active count = 2
        $user2 = User::factory()->create();
        OrganizationMember::create([
            'organization_id' => $this->organization->id,
            'user_id' => $user2->id,
            'role_id' => $this->organization->members()->first()->role_id,
            'status' => 'active',
        ]);

        $this->assertEquals(2, $this->organization->activeMembersCount());

        // Assign plan and active subscription with 5 seats
        $this->organization->update(['plan_id' => $this->perSeatPlan->id]);
        $sub = Subscription::create([
            'organization_id' => $this->organization->id,
            'plan_id' => $this->perSeatPlan->id,
            'seats' => 5,
            'status' => 'active',
            'current_period_end' => now()->addMonth(),
        ]);

        // Attempt reducing to 1 seat (below active count of 2) -> must fail
        $response = $this->actingAs($this->admin)->post(route('subscription.seats.update'), [
            'seats' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals(5, $sub->fresh()->seats);

        // Reducing to 2 seats (matches active count) -> must succeed
        $response2 = $this->actingAs($this->admin)->post(route('subscription.seats.update'), [
            'seats' => 2,
        ]);

        $response2->assertRedirect();
        $response2->assertSessionHas('success');
        $this->assertEquals(2, $sub->fresh()->seats);
    }

    public function test_seat_increase_redirects_to_payment_page(): void
    {
        $this->organization->update(['plan_id' => $this->perSeatPlan->id]);
        Subscription::create([
            'organization_id' => $this->organization->id,
            'plan_id' => $this->perSeatPlan->id,
            'seats' => 2,
            'status' => 'active',
            'current_period_end' => now()->addMonth(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('subscription.seats.update'), [
            'seats' => 6,
        ]);

        $response->assertRedirect(route('subscription.payment', [
            'plan' => $this->perSeatPlan->id,
            'seats' => 6,
            'type' => 'seat_increase',
        ]));
    }

    public function test_superadmin_approval_sets_subscription_seats_and_cycle(): void
    {
        $superAdmin = User::factory()->create(['is_super_admin' => true]);

        $subRequest = SubscriptionRequest::create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->admin->id,
            'plan_id' => $this->perSeatPlan->id,
            'seats' => 8,
            'price_per_seat' => 10.00,
            'amount' => 300.00,
            'currency' => 'SAR',
            'billing_cycle' => 'monthly',
            'payment_method' => 'bank_transfer',
            'transfer_reference' => 'TRF-SEAT-APPROVED',
            'transfer_date' => now(),
            'status' => 'pending',
            'request_type' => 'new_subscription',
        ]);

        $response = $this->actingAs($superAdmin)->post(route('superadmin.subscriptions.approve', $subRequest->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('subscription_requests', [
            'id' => $subRequest->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'organization_id' => $this->organization->id,
            'plan_id' => $this->perSeatPlan->id,
            'seats' => 8,
            'status' => 'active',
        ]);

        $this->assertEquals(8, $this->organization->fresh()->getEffectiveSeatLimit());
    }
}
