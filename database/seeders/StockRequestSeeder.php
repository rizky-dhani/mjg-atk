<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StockRequest;
use App\Models\StockRequestItem;
use App\Models\User;
use App\Models\CompanyDivision;
use App\Models\OfficeStationeryItem;
use App\Models\DivisionInventorySetting;
use App\Models\OfficeStationeryStockPerDivision;

class StockRequestSeeder extends Seeder
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
            $query->where('name', 'Staff');
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
        $divisions = CompanyDivision::all();
        $items = OfficeStationeryItem::all();

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

            $request = StockRequest::create([
                'request_number' => 'REQ-' . str_pad((string)$i, 8, '0', STR_PAD_LEFT),
                'requested_by' => $admin->id,
                'division_id' => $division->id,
                'type' => StockRequest::TYPE_INCREASE,
                'status' => [
                    StockRequest::STATUS_PENDING, 
                    StockRequest::STATUS_APPROVED_BY_HEAD, 
                    StockRequest::STATUS_REJECTED_BY_HEAD, 
                    StockRequest::STATUS_APPROVED_BY_IPC, 
                    StockRequest::STATUS_REJECTED_BY_IPC, 
                    StockRequest::STATUS_APPROVED_BY_IPC_HEAD,
                    StockRequest::STATUS_REJECTED_BY_IPC_HEAD,
                    StockRequest::STATUS_DELIVERED, 
                    StockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT,
                    StockRequest::STATUS_APPROVED_BY_GA_ADMIN,
                    StockRequest::STATUS_REJECTED_BY_GA_ADMIN,
                    StockRequest::STATUS_APPROVED_BY_GA_HEAD,
                    StockRequest::STATUS_REJECTED_BY_GA_HEAD,
                    StockRequest::STATUS_COMPLETED
                ][rand(0, 13)],
                'notes' => 'Sample request ' . $i,
            ]);

            // Set approval fields based on status
            if ($request->status === StockRequest::STATUS_APPROVED_BY_HEAD) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->save();
            } elseif($request->status === StockRequest::STATUS_APPROVED_BY_IPC) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now()->timezone('Asia/Jakarta');
                $request->save();
            } elseif($request->status === StockRequest::STATUS_APPROVED_BY_IPC_HEAD) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now()->timezone('Asia/Jakarta');
                $request->save();
            } elseif($request->status === StockRequest::STATUS_DELIVERED) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now()->timezone('Asia/Jakarta');
                $request->delivered_by = $ipc->id;
                $request->delivered_at = now()->timezone('Asia/Jakarta');
                $request->save();
            } elseif($request->status === StockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now()->timezone('Asia/Jakarta');
                $request->delivered_by = $ipc->id;
                $request->delivered_at = now()->timezone('Asia/Jakarta');
                $request->approval_stock_adjustment_id = $ipc->id;
                $request->approval_stock_adjustment_at = now()->timezone('Asia/Jakarta');
                $request->save();
            } elseif($request->status === StockRequest::STATUS_APPROVED_BY_GA_ADMIN) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now()->timezone('Asia/Jakarta');
                $request->delivered_by = $ipc->id;
                $request->delivered_at = now()->timezone('Asia/Jakarta');
                $request->approval_stock_adjustment_id = $ipc->id;
                $request->approval_stock_adjustment_at = now()->timezone('Asia/Jakarta');
                $request->approval_ga_admin_id = $gaAdmin->id;
                $request->approval_ga_admin_at = now()->timezone('Asia/Jakarta');
                $request->save();
            } elseif($request->status === StockRequest::STATUS_APPROVED_BY_GA_HEAD) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now()->timezone('Asia/Jakarta');
                $request->delivered_by = $ipc->id;
                $request->delivered_at = now()->timezone('Asia/Jakarta');
                $request->approval_stock_adjustment_id = $ipc->id;
                $request->approval_stock_adjustment_at = now()->timezone('Asia/Jakarta');
                $request->approval_ga_admin_id = $gaAdmin->id;
                $request->approval_ga_admin_at = now()->timezone('Asia/Jakarta');
                $request->approval_ga_head_id = $gaAdmin->id;
                $request->approval_ga_head_at = now()->timezone('Asia/Jakarta');
                $request->save();
            } elseif($request->status === StockRequest::STATUS_COMPLETED) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now()->timezone('Asia/Jakarta');
                $request->delivered_by = $ipc->id;
                $request->delivered_at = now()->timezone('Asia/Jakarta');
                $request->approval_stock_adjustment_id = $ipc->id;
                $request->approval_stock_adjustment_at = now()->timezone('Asia/Jakarta');
                $request->approval_ga_admin_id = $gaAdmin->id;
                $request->approval_ga_admin_at = now()->timezone('Asia/Jakarta');
                $request->approval_ga_head_id = $gaAdmin->id;
                $request->approval_ga_head_at = now()->timezone('Asia/Jakarta');
                $request->save();
            } elseif($request->status === StockRequest::STATUS_REJECTED_BY_HEAD) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->rejection_reason = 'Rejected by head due to budget constraints';
                $request->save();
            } elseif($request->status === StockRequest::STATUS_REJECTED_BY_IPC) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now()->timezone('Asia/Jakarta');
                $request->rejection_reason = 'Rejected by IPC due to stock availability';
                $request->save();
            } elseif($request->status === StockRequest::STATUS_REJECTED_BY_IPC_HEAD) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now()->timezone('Asia/Jakarta');
                $request->rejection_reason = 'Rejected by IPC Head due to policy violation';
                $request->save();
            } elseif($request->status === StockRequest::STATUS_REJECTED_BY_GA_ADMIN) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now()->timezone('Asia/Jakarta');
                $request->delivered_by = $ipc->id;
                $request->delivered_at = now()->timezone('Asia/Jakarta');
                $request->approval_stock_adjustment_id = $ipc->id;
                $request->approval_stock_adjustment_at = now()->timezone('Asia/Jakarta');
                $request->approval_ga_admin_id = $gaAdmin->id;
                $request->approval_ga_admin_at = now()->timezone('Asia/Jakarta');
                $request->rejection_reason = 'Rejected by GA Admin due to documentation issues';
                $request->save();
            } elseif($request->status === StockRequest::STATUS_REJECTED_BY_GA_HEAD) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now()->timezone('Asia/Jakarta');
                $request->delivered_by = $ipc->id;
                $request->delivered_at = now()->timezone('Asia/Jakarta');
                $request->approval_stock_adjustment_id = $ipc->id;
                $request->approval_stock_adjustment_at = now()->timezone('Asia/Jakarta');
                $request->approval_ga_admin_id = $gaAdmin->id;
                $request->approval_ga_admin_at = now()->timezone('Asia/Jakarta');
                $request->approval_ga_head_id = $gaAdmin->id;
                $request->approval_ga_head_at = now()->timezone('Asia/Jakarta');
                $request->rejection_reason = 'Rejected by GA Head due to budget approval';
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
                    $stock = OfficeStationeryStockPerDivision::where('division_id', $division->id)
                        ->where('item_id', $item->id)
                        ->first();

                    $currentStock = $stock?->current_stock ?? 0;

                    // Calculate allowed quantity for request
                    if ($request->type === StockRequest::TYPE_INCREASE) {
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

                    StockRequestItem::create([
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