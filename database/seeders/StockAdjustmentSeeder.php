<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StockRequest;
use App\Models\StockRequestItem;

class StockAdjustmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all stock requests that have been delivered
        $requests = StockRequest::where('status', 'delivered')->get();
        
        foreach ($requests as $request) {
            // For each request, adjust the quantities to simulate actual delivery
            foreach ($request->items as $item) {
                // Adjust quantity to be 90-100% of requested quantity to simulate
                // cases where delivered quantity might differ from requested
                $adjustedQuantity = rand(90, 100) / 100 * $item->quantity;
                $adjustedQuantity = round($adjustedQuantity);
                
                // Update the item with adjusted quantity
                $item->update([
                    'adjusted_quantity' => $adjustedQuantity
                ]);
            }
            
            // Update the request status to reflect stock adjustment approval
            $request->update([
                'status' => 'approved_stock_adjustment',
                'approval_stock_adjustment_id' => $request->delivered_by,
                'approval_stock_adjustment_at' => now()->timezone('Asia/Jakarta'),
            ]);
        }
    }
}
