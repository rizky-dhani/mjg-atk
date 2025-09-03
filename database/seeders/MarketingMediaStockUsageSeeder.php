<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MarketingMediaStockUsage;
use App\Models\MarketingMediaStockUsageItem;
use App\Models\User;
use App\Models\CompanyDivision;
use App\Models\MarketingMediaItem;

class MarketingMediaStockUsageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users with Head role
        $headUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'Head');
        })->get();
        
        if ($headUsers->isEmpty()) {
            $headUsers = User::limit(5)->get();
        }
        
        // Get divisions
        $divisions = CompanyDivision::all();
        
        if ($divisions->isEmpty()) {
            return;
        }
        
        foreach (range(1, 20) as $i) {
            // Pick a random division
            $division = $divisions->random();
            
            // Get users in this division
            $usersInDivision = User::where('division_id', $division->id)->get();
            
            if ($usersInDivision->isEmpty()) {
                continue;
            }
            
            // Pick a random user in this division
            $user = $usersInDivision->random();
            
            // Create a stock usage
            $stockUsage = MarketingMediaStockUsage::create([
                'division_id' => $division->id,
                'requested_by' => $user->id,
                'type' => MarketingMediaStockUsage::TYPE_DECREASE,
                'status' => [
                    MarketingMediaStockUsage::STATUS_PENDING,
                    MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD,
                    MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD,
                    MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN,
                    MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN,
                    MarketingMediaStockUsage::STATUS_APPROVED_BY_MKT_HEAD,
                    MarketingMediaStockUsage::STATUS_REJECTED_BY_MKT_HEAD,
                    MarketingMediaStockUsage::STATUS_COMPLETED,
                ][rand(0, 7)],
                'notes' => 'Sample marketing media stock usage ' . ($i + 1),
            ]);
            
            // Set approval fields based on status
            if ($stockUsage->status === MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD) {
                $headUser = $headUsers->random();
                $stockUsage->approval_head_id = $headUser->id;
                $stockUsage->approval_head_at = now();
                $stockUsage->save();
            } elseif($stockUsage->status === MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN) {
                $headUser = $headUsers->random();
                $stockUsage->approval_head_id = $headUser->id;
                $stockUsage->approval_head_at = now();
                
                // Get GA admin
                $gaAdmin = User::whereHas('roles', function ($query) {
                    $query->where('name', 'Admin');
                })->whereHas('division', function ($query) {
                    $query->where('initial', 'GA');
                })->first();
                
                if ($gaAdmin) {
                    $stockUsage->approval_ga_admin_id = $gaAdmin->id;
                    $stockUsage->approval_ga_admin_at = now();
                    $stockUsage->save();
                }
            } elseif($stockUsage->status === MarketingMediaStockUsage::STATUS_APPROVED_BY_MKT_HEAD) {
                $headUser = $headUsers->random();
                $stockUsage->approval_head_id = $headUser->id;
                $stockUsage->approval_head_at = now();
                
                // Get GA admin
                $gaAdmin = User::whereHas('roles', function ($query) {
                    $query->where('name', 'Admin');
                })->whereHas('division', function ($query) {
                    $query->where('initial', 'GA');
                })->first();
                
                if ($gaAdmin) {
                    $stockUsage->approval_ga_admin_id = $gaAdmin->id;
                    $stockUsage->approval_ga_admin_at = now();
                }
                
                // Get Marketing Support Head
                $marketingSupportHead = User::whereHas('roles', function ($query) {
                    $query->where('name', 'Head');
                })->whereHas('division', function ($query) {
                    $query->where('initial', 'Marketing Support');
                })->first();
                
                if ($marketingSupportHead) {
                    $stockUsage->approval_MKT_HEAD_id = $marketingSupportHead->id;
                    $stockUsage->approval_MKT_HEAD_at = now();
                }
                
                $stockUsage->save();
            } elseif($stockUsage->status === MarketingMediaStockUsage::STATUS_COMPLETED) {
                $headUser = $headUsers->random();
                $stockUsage->approval_head_id = $headUser->id;
                $stockUsage->approval_head_at = now();
                
                // Get GA admin
                $gaAdmin = User::whereHas('roles', function ($query) {
                    $query->where('name', 'Admin');
                })->whereHas('division', function ($query) {
                    $query->where('initial', 'GA');
                })->first();
                
                if ($gaAdmin) {
                    $stockUsage->approval_ga_admin_id = $gaAdmin->id;
                    $stockUsage->approval_ga_admin_at = now();
                }
                
                // Get Marketing Support Head
                $marketingSupportHead = User::whereHas('roles', function ($query) {
                    $query->where('name', 'Head');
                })->whereHas('division', function ($query) {
                    $query->where('initial', 'Marketing Support');
                })->first();
                
                if ($marketingSupportHead) {
                    $stockUsage->approval_MKT_HEAD_id = $marketingSupportHead->id;
                    $stockUsage->approval_MKT_HEAD_at = now();
                }
                
                $stockUsage->save();
            } elseif($stockUsage->status === MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD) {
                $headUser = $headUsers->random();
                $stockUsage->rejection_head_id = $headUser->id;
                $stockUsage->rejection_head_at = now();
                $stockUsage->rejection_reason = 'Rejected by head due to insufficient stock';
                $stockUsage->save();
            } elseif($stockUsage->status === MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN) {
                $headUser = $headUsers->random();
                $stockUsage->approval_head_id = $headUser->id;
                $stockUsage->approval_head_at = now();
                
                // Get GA admin
                $gaAdmin = User::whereHas('roles', function ($query) {
                    $query->where('name', 'Admin');
                })->whereHas('division', function ($query) {
                    $query->where('initial', 'GA');
                })->first();
                
                if ($gaAdmin) {
                    $stockUsage->rejection_ga_admin_id = $gaAdmin->id;
                    $stockUsage->rejection_ga_admin_at = now();
                    $stockUsage->rejection_reason = 'Rejected by GA admin due to documentation issues';
                    $stockUsage->save();
                }
            } elseif($stockUsage->status === MarketingMediaStockUsage::STATUS_REJECTED_BY_MKT_HEAD) {
                $headUser = $headUsers->random();
                $stockUsage->approval_head_id = $headUser->id;
                $stockUsage->approval_head_at = now();
                
                // Get GA admin
                $gaAdmin = User::whereHas('roles', function ($query) {
                    $query->where('name', 'Admin');
                })->whereHas('division', function ($query) {
                    $query->where('initial', 'GA');
                })->first();
                
                if ($gaAdmin) {
                    $stockUsage->approval_ga_admin_id = $gaAdmin->id;
                    $stockUsage->approval_ga_admin_at = now();
                }
                
                // Get Marketing Support Head
                $marketingSupportHead = User::whereHas('roles', function ($query) {
                    $query->where('name', 'Head');
                })->whereHas('division', function ($query) {
                    $query->where('initial', 'Marketing Support');
                })->first();
                
                if ($marketingSupportHead) {
                    $stockUsage->rejection_MKT_HEAD_id = $marketingSupportHead->id;
                    $stockUsage->rejection_MKT_HEAD_at = now();
                    $stockUsage->rejection_reason = 'Rejected by Marketing Support Head due to budget concerns';
                }
                
                $stockUsage->save();
            }
            
            // Get some marketing media items
            $items = MarketingMediaItem::inRandomOrder()->limit(3)->get();
            
            foreach ($items as $item) {
                MarketingMediaStockUsageItem::create([
                    'stock_usage_id' => $stockUsage->id,
                    'item_id' => $item->id,
                    'category_id' => $item->category_id,
                    'quantity' => rand(5, 50),
                    'notes' => 'Sample usage note for ' . $item->name,
                ]);
            }
        }
    }
}