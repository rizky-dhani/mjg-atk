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
        $divisions = CompanyDivision::all();
        $items = OfficeStationeryItem::all();

        foreach (range(1, 25) as $i) {
            // Pick a random division first
            $division = $divisions->random();

            // Pick a random admin in this division
            $admin = $userAdmin->where('division_id', $division->id)->random();

            // Pick a random head in this division
            $head = $userHead->where('division_id', $division->id)->random();

            // Pick a random IPC in this division (if needed)
            $ipc = $userIPC->random();

            $request = StockRequest::create([
                'request_number' => 'REQ-' . str_pad((string)$i, 8, '0', STR_PAD_LEFT),
                'requested_by' => $admin->id,
                'division_id' => $division->id,
                'type' => StockRequest::TYPE_INCREASE,
                'status' => [StockRequest::STATUS_PENDING, StockRequest::STATUS_APPROVED_BY_HEAD, StockRequest::STATUS_REJECTED_BY_HEAD, StockRequest::STATUS_APPROVED_BY_IPC, StockRequest::STATUS_REJECTED_BY_IPC, StockRequest::STATUS_DELIVERED, StockRequest::STATUS_COMPLETED][rand(0, 6)],
                'notes' => 'Sample request ' . $i,
            ]);

            // if $request status is approve by head, set approval_head_id
            if ($request->status === StockRequest::STATUS_APPROVED_BY_HEAD) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now();
                $request->save();
            }elseif($request->status === StockRequest::STATUS_APPROVED_BY_IPC) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now();
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now();
                $request->save();
            }elseif($request->status === StockRequest::STATUS_DELIVERED) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now();
                $request->delivered_by = $ipc->id;
                $request->delivered_at = now();
                $request->save();
            }elseif($request->status === StockRequest::STATUS_REJECTED_BY_IPC) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now();
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now();
                $request->rejection_reason = 'Rejected because stock exceed/under stock limit';
                $request->save();
            }

            // Attach 2-4 random items to this request
            foreach ($items->random(rand(2, 4)) as $item) {
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
                    'notes' => 'Item for request ' . (string)$i,
                ]);
            }
        }
    }
}