<?php

namespace Database\Seeders;

use App\Domains\Tenancy\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'seat_limit' => 5,
                'max_offices' => 1,
                'room_limit' => 3,
                'max_guest_invitations' => 5,
                'max_room_capacity' => 8,
                'storage_limit_gb' => 1,
                'price' => 0,
                'features' => ['basic_chat', 'basic_presence', 'basic_audio'],
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'seat_limit' => 25,
                'max_offices' => 3,
                'room_limit' => 10,
                'max_guest_invitations' => 25,
                'max_room_capacity' => 15,
                'storage_limit_gb' => 10,
                'price' => 29.99,
                'features' => ['basic_chat', 'basic_presence', 'basic_audio', 'video', 'screen_share', 'file_sharing'],
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'seat_limit' => 100,
                'max_offices' => 10,
                'room_limit' => 50,
                'max_guest_invitations' => 100,
                'max_room_capacity' => 50,
                'storage_limit_gb' => 100,
                'price' => 79.99,
                'features' => ['basic_chat', 'basic_presence', 'basic_audio', 'video', 'screen_share', 'file_sharing', 'guest_access', 'analytics', 'custom_branding'],
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'seat_limit' => 0, // Unlimited
                'max_offices' => 0, // Unlimited
                'room_limit' => 0, // Unlimited
                'max_guest_invitations' => 0, // Unlimited
                'max_room_capacity' => 0, // Unlimited
                'storage_limit_gb' => 0, // Unlimited
                'price' => 199.99,
                'features' => ['basic_chat', 'basic_presence', 'basic_audio', 'video', 'screen_share', 'file_sharing', 'guest_access', 'analytics', 'custom_branding', 'sso', 'api_access', 'priority_support', 'advanced_analytics'],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
