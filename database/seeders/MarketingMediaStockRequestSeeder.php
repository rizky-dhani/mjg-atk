<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\CompanyDivision;
use Illuminate\Database\Seeder;
use App\Models\MarketingMediaItem;
use App\Models\DivisionInventorySetting;
use App\Models\MarketingMediaStockRequest;
use App\Models\MarketingMediaStockPerDivision;
use App\Models\MarketingMediaStockRequestItem;

class MarketingMediaStockRequestSeeder extends Seeder
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
        $userIPC = User::with(['roles', 'division'])
        ->whereHas('roles', function ($query) {
            $query->where('name', 'Admin');
        })
        ->whereHas('division', function ($query) {
            $query->where('initial', 'IPC');
        })
        ->get();
        $userGA = User::with(['roles', 'division'])
        ->whereHas('roles', function ($query) {
            $query->where('name', 'Admin');
        })
        ->whereHas('division', function ($query) {
            $query->where('initial', 'GA');
        })
        ->get();
        $userMarketingSupport = User::with(['roles', 'division'])
        ->whereHas('roles', function ($query) {
            $query->where('name', 'Head');
        })
        ->whereHas('division', function ($query) {
            $query->where('initial', 'Marketing Support');
        })
        ->get();
        $divisions = CompanyDivision::all();
        $items = MarketingMediaItem::all();

        // Check if we have users with required roles
        if ($userAdmin->isEmpty() || $userHead->isEmpty() || $userIPC->isEmpty()) {
            echo "Missing required users with specific roles. Skipping StockRequestSeeder.\n";
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

            // Pick a random IPC
            if ($userIPC->isEmpty()) {
                continue; // Skip if no IPC users
            }
            $ipc = $userIPC->random();
            
            // Pick a random GA admin
            $gaAdmin = $userGA->isNotEmpty() ? $userGA->random() : $admin; // Fallback to regular admin if no GA admin
            $marketingSupportHead = $userMarketingSupport->isNotEmpty() ? $userMarketingSupport->random() : $head;
            
            $request = MarketingMediaStockRequest::create([
                'requested_by' => $admin->id,
                'division_id' => $division->id,
                'type' => MarketingMediaStockRequest::TYPE_INCREASE,
                'status' => [
                    MarketingMediaStockRequest::STATUS_PENDING, 
                    MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD, 
                    MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD, 
                    MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC, 
                    MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC, 
                    MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD,
                    MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD,
                    MarketingMediaStockRequest::STATUS_DELIVERED, 
                    MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT,
                    MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN,
                    MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN,
                    MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD,
                    MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD,
                    MarketingMediaStockRequest::STATUS_COMPLETED
                ][rand(0, 13)],
                'notes' => 'Sample marketing media request ' . $i,
            ]);

            // Set approval fields based on status
            if ($request->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now();
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now();
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now();
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now();
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now();
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now();
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_DELIVERED) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now();
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now();
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now();
                $request->delivered_by = $ipc->id;
                $request->delivered_at = now();
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now();
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now();
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now();
                $request->delivered_by = $ipc->id;
                $request->delivered_at = now();
                $request->approval_stock_adjustment_id = $ipc->id;
                $request->approval_stock_adjustment_at = now();
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now();
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now();
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now();
                $request->delivered_by = $ipc->id;
                $request->delivered_at = now();
                $request->approval_stock_adjustment_id = $ipc->id;
                $request->approval_stock_adjustment_at = now();
                $request->approval_ga_admin_id = $gaAdmin->id;
                $request->approval_ga_admin_at = now();
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now();
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now();
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now();
                $request->delivered_by = $ipc->id;
                $request->delivered_at = now();
                $request->approval_stock_adjustment_id = $ipc->id;
                $request->approval_stock_adjustment_at = now();
                $request->approval_ga_admin_id = $gaAdmin->id;
                $request->approval_ga_admin_at = now();
                $request->approval_marketing_head_id = $marketingSupportHead->id;
                $request->approval_marketing_head_at = now();
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_COMPLETED) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now();
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now();
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now();
                $request->delivered_by = $ipc->id;
                $request->delivered_at = now();
                $request->approval_stock_adjustment_id = $ipc->id;
                $request->approval_stock_adjustment_at = now();
                $request->approval_ga_admin_id = $gaAdmin->id;
                $request->approval_ga_admin_at = now();
                $request->approval_marketing_head_id = $marketingSupportHead->id;
                $request->approval_marketing_head_at = now();
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD) {
                $request->rejection_head_id = $head->id;
                $request->rejection_head_at = now();
                $request->rejection_reason = 'Rejected by head due to budget constraints';
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now();
                $request->rejection_ipc_id = $ipc->id;
                $request->rejection_ipc_at = now();
                $request->rejection_reason = 'Rejected by IPC due to stock availability';
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now();
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now();
                $request->rejection_ipc_head_id = $ipc->id;
                $request->rejection_ipc_head_at = now();
                $request->rejection_reason = 'Rejected by IPC Head due to policy violation';
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now();
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now();
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now();
                $request->delivered_by = $ipc->id;
                $request->delivered_at = now();
                $request->approval_stock_adjustment_id = $ipc->id;
                $request->approval_stock_adjustment_at = now();
                $request->rejection_ga_admin_id = $gaAdmin->id;
                $request->rejection_ga_admin_at = now();
                $request->rejection_reason = 'Rejected by GA Admin due to documentation issues';
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now();
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now();
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now();
                $request->delivered_by = $ipc->id;
                $request->delivered_at = now();
                $request->approval_stock_adjustment_id = $ipc->id;
                $request->approval_stock_adjustment_at = now();
                $request->approval_ga_admin_id = $gaAdmin->id;
                $request->approval_ga_admin_at = now();
                $request->rejection_marketing_head_id = $marketingSupportHead->id;
                $request->rejection_marketing_head_at = now();
                $request->rejection_reason = 'Rejected by Marketing Support Head due to budget approval';
                $request->save();
            }

            // Attach 2-4 random items to this request
            if ($items->count() >= 2) {
                foreach ($items->random(rand(2, min(4, $items->count()))) as $item) {
                    // Get max_limit from DivisionInventorySetting
                    $setting = DivisionInventorySetting::where('division_id', $division->id)
                        ->where('item_id', $item->id)
                        ->first();

                    $maxLimit = $setting?->max_limit ?? 0;

                    // Get current stock from DivisionStock
                    $stock = MarketingMediaStockPerDivision::where('division_id', $division->id)
                        ->where('item_id', $item->id)
                        ->first();

                    $currentStock = $stock?->current_stock ?? 0;

                    // Calculate allowed quantity for request
                    if ($request->type === MarketingMediaStockRequest::TYPE_INCREASE) {
                        // Only allow up to (max_limit - current_stock)
                        $allowed = max($maxLimit - $currentStock, 0);
                        // If allowed is 0, skip this item
                        if ($allowed < 1) {
                            continue;
                        }
                        $quantity = rand(1, $allowed);
                    } else {
                        // For decrease, only allow up to current_stock
                        $allowed = max($currentStock, 0);
                        if ($allowed < 1) {
                            continue;
                        }
                        $quantity = rand(1, $allowed);
                    }

                    MarketingMediaStockRequestItem::create([
                        'stock_request_id' => $request->id,
                        'item_id' => $item->id,
                        'category_id' => $item->category->id,
                        'quantity' => $quantity,
                        'adjusted_quantity' => $quantity, // For simplicity, we'll set adjusted quantity to the same as requested
                        'notes' => 'Item for request ' . (string)$i,
                    ]);
                }
            }
        }
    }
}