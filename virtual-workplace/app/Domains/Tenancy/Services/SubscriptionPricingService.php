<?php

namespace App\Domains\Tenancy\Services;

use App\Domains\Tenancy\Models\Plan;
use App\Domains\Tenancy\Models\Subscription;

class SubscriptionPricingService
{
    /**
     * Default USD to SAR conversion rate.
     */
    public const DEFAULT_USD_TO_SAR_RATE = 3.75;

    /**
     * Calculate authoritative subscription pricing for a given plan and seat count.
     *
     * @param  Plan  $plan
     * @param  int  $seats
     * @param  string  $billingCycle ('monthly' or 'yearly')
     * @param  float  $sarRate
     * @return array
     */
    public function calculatePrice(Plan $plan, int $seats = 1, string $billingCycle = 'monthly', float $sarRate = self::DEFAULT_USD_TO_SAR_RATE): array
    {
        $isPerSeat = (bool) ($plan->is_per_seat ?? false);
        $minSeats = $plan->getEffectiveMinSeats();
        $effectiveSeats = $isPerSeat ? max($minSeats, $seats) : 1;

        $unitPriceUSD = (float) $plan->price;
        $unitPriceSAR = round($unitPriceUSD * $sarRate, 2);

        $months = $billingCycle === 'yearly' ? 12 : 1;

        if ($isPerSeat) {
            $totalUSD = round($unitPriceUSD * $effectiveSeats * $months, 2);
            $totalSAR = round($unitPriceSAR * $effectiveSeats * $months, 2);
            $monthlyUSD = round($unitPriceUSD * $effectiveSeats, 2);
            $monthlySAR = round($unitPriceSAR * $effectiveSeats, 2);
        } else {
            $totalUSD = round($unitPriceUSD * $months, 2);
            $totalSAR = round($unitPriceSAR * $months, 2);
            $monthlyUSD = $unitPriceUSD;
            $monthlySAR = $unitPriceSAR;
        }

        return [
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'plan_slug' => $plan->slug,
            'is_per_seat' => $isPerSeat,
            'seats' => $effectiveSeats,
            'min_seats' => $minSeats,
            'billing_cycle' => $billingCycle,
            'months' => $months,
            'unit_price_usd' => $unitPriceUSD,
            'unit_price_sar' => $unitPriceSAR,
            'monthly_total_usd' => $monthlyUSD,
            'monthly_total_sar' => $monthlySAR,
            'total_usd' => $totalUSD,
            'total_sar' => $totalSAR,
            'sar_rate' => $sarRate,
        ];
    }

    /**
     * Calculate the cost adjustment when changing seats on an active subscription.
     *
     * @param  Subscription  $subscription
     * @param  int  $newSeats
     * @param  float  $sarRate
     * @return array
     */
    public function calculateSeatAdjustment(Subscription $subscription, int $newSeats, float $sarRate = self::DEFAULT_USD_TO_SAR_RATE): array
    {
        $plan = $subscription->plan;
        $currentSeats = max(1, (int) ($subscription->seats ?? ($plan?->seat_limit ?: 1)));
        $isPerSeat = (bool) ($plan?->is_per_seat ?? false);
        $unitPriceUSD = (float) ($plan?->price ?? 0);
        $unitPriceSAR = round($unitPriceUSD * $sarRate, 2);

        $difference = $newSeats - $currentSeats;
        $months = ($subscription->billing_cycle === 'yearly') ? 12 : 1;

        if ($isPerSeat) {
            $diffUSD = round($difference * $unitPriceUSD * $months, 2);
            $diffSAR = round($difference * $unitPriceSAR * $months, 2);
            $newTotalUSD = round($newSeats * $unitPriceUSD * $months, 2);
            $newTotalSAR = round($newSeats * $unitPriceSAR * $months, 2);
        } else {
            $diffUSD = 0.0;
            $diffSAR = 0.0;
            $newTotalUSD = round($unitPriceUSD * $months, 2);
            $newTotalSAR = round($unitPriceSAR * $months, 2);
        }

        return [
            'current_seats' => $currentSeats,
            'new_seats' => $newSeats,
            'difference' => $difference,
            'is_increase' => $difference > 0,
            'is_decrease' => $difference < 0,
            'is_per_seat' => $isPerSeat,
            'unit_price_usd' => $unitPriceUSD,
            'unit_price_sar' => $unitPriceSAR,
            'difference_amount_usd' => abs($diffUSD),
            'difference_amount_sar' => abs($diffSAR),
            'new_total_usd' => $newTotalUSD,
            'new_total_sar' => $newTotalSAR,
        ];
    }

    /**
     * Validate whether a proposed seat count is permitted.
     *
     * @param  Plan  $plan
     * @param  int  $requestedSeats
     * @param  int|null  $activeMembersCount
     * @return array ['valid' => bool, 'message' => string|null]
     */
    public function validateSeatQuantity(Plan $plan, int $requestedSeats, ?int $activeMembersCount = null): array
    {
        if ($plan->is_per_seat) {
            $min = $plan->getEffectiveMinSeats();
            if ($requestedSeats < $min) {
                return [
                    'valid' => false,
                    'message' => __("Minimum required seats for this plan is :min seats.", ['min' => $min]),
                ];
            }

            if ($plan->max_seats && $requestedSeats > $plan->max_seats) {
                return [
                    'valid' => false,
                    'message' => __("Maximum allowed seats for this plan is :max seats.", ['max' => $plan->max_seats]),
                ];
            }
        }

        if ($activeMembersCount !== null && $requestedSeats < $activeMembersCount) {
            return [
                'valid' => false,
                'message' => __("You currently have :count active users. You must remove users before reducing your seats below :count.", ['count' => $activeMembersCount]),
            ];
        }

        return ['valid' => true, 'message' => null];
    }
}
