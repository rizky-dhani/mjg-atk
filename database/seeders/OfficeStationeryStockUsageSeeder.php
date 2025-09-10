<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\CompanyDivision;
use Illuminate\Database\Seeder;
use App\Models\OfficeStationeryItem;
use App\Models\OfficeStationeryStockUsage;
use App\Models\OfficeStationeryStockUsageItem;
use App\Models\OfficeStationeryStockPerDivision;

class OfficeStationeryStockUsageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userAdmin = User::with('roles')->whereHas('roles', function ($query) {
            $query->where('name', 'Admin');
        })
        ->get();
        $userHead = User::with('roles')->whereHas('roles', function ($query) {
            $query->where('name', 'Head');
        })
        ->get();
        $userGA = User::with(['roles', 'division'])
        ->whereHas('roles', function ($query) {
            $query->where('name', 'Admin');
        })
        ->whereHas('division', function ($query) {
            $query->where('name', 'General Affairs');
        })
        ->get();
        $userHcg = User::with(['roles', 'division'])
        ->whereHas('roles', function ($query) {
            $query->where('name', 'Head');
        })
        ->whereHas('division', function ($query) {
            $query->where('initial', 'HCG');
        })
        ->get();
        $divisions = CompanyDivision::all();
        $items = OfficeStationeryItem::all();

        // Check if we have users with required roles
        if ($userAdmin->isEmpty() || $userHead->isEmpty()) {
            echo "Missing required users with specific roles. Skipping StockUsageSeeder.\n";
            return;
        }

        foreach (range(1, 25) as $i) {
            // Pick a random division first
            $division = $divisions->random();

            // Pick a random admin in this division
            $adminsInDivision = $userAdmin->where('division_id', $division->id);
            if ($adminsInDivision->isEmpty()) {
                continue; // Skip if no admin in this division
            }
            $admin = $adminsInDivision->random();

            // Pick a random head in this division
            $headsInDivision = $userHead->where('division_id', $division->id);
            if ($headsInDivision->isEmpty()) {
                continue; // Skip if no head in this division
            }
            $head = $headsInDivision->random();

            // Pick a random GA admin
            $gaAdmin = $userGA->isNotEmpty() ? $userGA->random() : $admin; // Fallback to regular admin if no GA admin
            $hcgHead = $userHcg->isNotEmpty() ? $userHcg->random() : $head;
            
            $usage = OfficeStationeryStockUsage::create([
                'requested_by' => $admin->id,
                'division_id' => $division->id,
                'type' => OfficeStationeryStockUsage::TYPE_DECREASE,
                'status' => [
                    OfficeStationeryStockUsage::STATUS_PENDING, 
                    OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD, 
                    OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD, 
                    OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN,
                    OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN,
                    OfficeStationeryStockUsage::STATUS_APPROVED_BY_HCG_HEAD,
                    OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD,
                    OfficeStationeryStockUsage::STATUS_COMPLETED
                ][rand(0, 7)],
                'notes' => 'Sample usage ' . $i,
            ]);

            // Set approval fields based on status
            if ($usage->status === OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD) {
                $usage->approval_head_id = $head->id;
                $usage->approval_head_at = now()->timezone('Asia/Jakarta');
                $usage->save();
            } elseif($usage->status === OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN) {
                $usage->approval_head_id = $head->id;
                $usage->approval_head_at = now()->timezone('Asia/Jakarta');
                $usage->approval_ga_admin_id = $gaAdmin->id;
                $usage->approval_ga_admin_at = now()->timezone('Asia/Jakarta');
                $usage->save();
            } elseif($usage->status === OfficeStationeryStockUsage::STATUS_APPROVED_BY_HCG_HEAD) {
                $usage->approval_head_id = $head->id;
                $usage->approval_head_at = now()->timezone('Asia/Jakarta');
                $usage->approval_ga_admin_id = $gaAdmin->id;
                $usage->approval_ga_admin_at = now()->timezone('Asia/Jakarta');
                $usage->approval_hcg_head_id = $hcgHead->id;
                $usage->approval_hcg_head_at = now()->timezone('Asia/Jakarta');
                $usage->save();
            } elseif($usage->status === OfficeStationeryStockUsage::STATUS_COMPLETED) {
                $usage->approval_head_id = $head->id;
                $usage->approval_head_at = now()->timezone('Asia/Jakarta');
                $usage->approval_ga_admin_id = $gaAdmin->id;
                $usage->approval_ga_admin_at = now()->timezone('Asia/Jakarta');
                $usage->approval_hcg_head_id = $hcgHead->id;
                $usage->approval_hcg_head_at = now()->timezone('Asia/Jakarta');
                $usage->save();
            } elseif($usage->status === OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD) {
                $usage->rejection_head_id = $head->id;
                $usage->rejection_head_at = now()->timezone('Asia/Jakarta');
                $usage->rejection_reason = 'Rejected by head due to budget constraints';
                $usage->save();
            } elseif($usage->status === OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN) {
                $usage->approval_head_id = $head->id;
                $usage->approval_head_at = now()->timezone('Asia/Jakarta');
                $usage->rejection_ga_admin_id = $gaAdmin->id;
                $usage->rejection_ga_admin_at = now()->timezone('Asia/Jakarta');
                $usage->rejection_reason = 'Rejected by GA Admin due to documentation issues';
                $usage->save();
            } elseif($usage->status === OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD) {
                $usage->approval_head_id = $head->id;
                $usage->approval_head_at = now()->timezone('Asia/Jakarta');
                $usage->approval_ga_admin_id = $gaAdmin->id;
                $usage->approval_ga_admin_at = now()->timezone('Asia/Jakarta');
                $usage->rejection_hcg_head_id = $hcgHead->id;
                $usage->rejection_hcg_head_at = now()->timezone('Asia/Jakarta');
                $usage->rejection_reason = 'Rejected by HCG Head due to budget approval';
                $usage->save();
            }

            // Attach 2-4 random items to this usage
            if ($items->count() >= 2) {
                foreach ($items->random(rand(2, min(4, $items->count()))) as $item) {
                    // Get current stock from DivisionStock
                    $stock = OfficeStationeryStockPerDivision::where('division_id', $division->id)
                        ->where('item_id', $item->id)
                        ->first();

                    $currentStock = $stock?->current_stock ?? 0;

                    // For decrease, only allow up to current_stock
                    $allowed = max($currentStock, 0);
                    if ($allowed < 1) {
                        continue;
                    }
                    $quantity = rand(1, $allowed);

                    OfficeStationeryStockUsageItem::create([
                        'stock_usage_id' => $usage->id,
                        'item_id' => $item->id,
                        'category_id' => $item->category->id,
                        'quantity' => $quantity,
                        'notes' => 'Item for usage ' . (string)$i,
                    ]);
                }
            }
        }
    }
}
