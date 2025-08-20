<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CompanyDivision;
use Illuminate\Database\Seeder;
use App\Models\MarketingMediaItem;
use App\Models\MarketingMediaCategory;
use App\Models\MarketingMediaStockRequest;
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
        $divisions = CompanyDivision::all();
        $items = MarketingMediaItem::all();

        // Check if we have users with required roles
        if ($userAdmin->isEmpty() || $userHead->isEmpty() || $userIPC->isEmpty()) {
            echo "Missing required users with specific roles. Skipping MarketingMediaStockRequestSeeder.\n";
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

            $request = MarketingMediaStockRequest::create([
                'request_number' => strtoupper($division->initial).'-MM-REQ-' . str_pad((string)($i + 1), 8, '0', STR_PAD_LEFT),
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
                    MarketingMediaStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD,
                    MarketingMediaStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD,
                    MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN,
                    MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN,
                    MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD,
                    MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD,
                    MarketingMediaStockRequest::STATUS_COMPLETED
                ][rand(0, 15)],
                'notes' => 'Sample marketing media request ' . $i,
            ]);

            // Set approval fields based on status
            if ($request->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now()->timezone('Asia/Jakarta');
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now()->timezone('Asia/Jakarta');
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_DELIVERED) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_head_id = $ipc->id;
                $request->approval_ipc_head_at = now()->timezone('Asia/Jakarta');
                $request->delivered_by = $ipc->id;
                $request->delivered_at = now()->timezone('Asia/Jakarta');
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT) {
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
            } elseif($request->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD) {
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
            } elseif($request->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN) {
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
            } elseif($request->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD) {
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
                $request->approval_mkt_head_id = $gaAdmin->id;
                $request->approval_mkt_head_at = now()->timezone('Asia/Jakarta');
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_COMPLETED) {
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
                $request->approval_mkt_head_id = $gaAdmin->id;
                $request->approval_mkt_head_at = now()->timezone('Asia/Jakarta');
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD) {
                $request->rejection_head_id = $head->id;
                $request->rejection_head_at = now()->timezone('Asia/Jakarta');
                $request->rejection_reason = 'Rejected by head due to budget constraints';
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->rejection_ipc_id = $ipc->id;
                $request->rejection_ipc_at = now()->timezone('Asia/Jakarta');
                $request->rejection_reason = 'Rejected by IPC due to stock availability';
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD) {
                $request->approval_head_id = $head->id;
                $request->approval_head_at = now()->timezone('Asia/Jakarta');
                $request->approval_ipc_id = $ipc->id;
                $request->approval_ipc_at = now()->timezone('Asia/Jakarta');
                $request->rejection_ipc_head_id = $ipc->id;
                $request->rejection_ipc_head_at = now()->timezone('Asia/Jakarta');
                $request->rejection_reason = 'Rejected by IPC Head due to policy violation';
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD) {
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
                $request->rejection_ga_admin_id = $gaAdmin->id;
                $request->rejection_ga_admin_at = now()->timezone('Asia/Jakarta');
                $request->rejection_reason = 'Rejected by Second IPC Head due to budget approval';
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN) {
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
                $request->rejection_ga_admin_id = $gaAdmin->id;
                $request->rejection_ga_admin_at = now()->timezone('Asia/Jakarta');
                $request->rejection_reason = 'Rejected by GA Admin due to documentation issues';
                $request->save();
            } elseif($request->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD) {
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
                $request->rejection_mkt_head_id = $gaAdmin->id;
                $request->rejection_mkt_head_at = now()->timezone('Asia/Jakarta');
                $request->rejection_reason = 'Rejected by Marketing Head due to campaign changes';
                $request->save();
            }

            // Attach 2-4 random items to this request
            if ($items->count() >= 2) {
                foreach ($items->random(rand(2, min(4, $items->count()))) as $item) {
                    MarketingMediaStockRequestItem::create([
                        'stock_request_id' => $request->id,
                        'marketing_media_id' => $item->id,
                        'category_id' => $item->category->id,
                        'quantity' => rand(10, 100),
                        'adjusted_quantity' => rand(10, 100), // For simplicity, we'll set adjusted quantity to a random value
                        'notes' => 'Item for marketing media request ' . (string)$i,
                    ]);
                }
            }
        }
    }
}
