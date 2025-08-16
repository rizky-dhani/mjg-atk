<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OfficeStationeryStockRequest;

class UpdateStockRequestRejectionFieldsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Move existing rejection data to new rejection fields
        $rejectedRequests = OfficeStationeryStockRequest::where('status', 'LIKE', 'rejected%')->get();
        
        foreach ($rejectedRequests as $request) {
            // For simplicity, we'll use the same user for both approval and rejection
            // In a real application, you would want to properly map these
            switch ($request->status) {
                case 'rejected_by_head':
                    $request->rejection_head_id = $request->approval_head_id;
                    $request->rejection_head_at = $request->approval_head_at;
                    break;
                case 'rejected_by_ipc':
                    $request->rejection_ipc_id = $request->approval_ipc_id;
                    $request->rejection_ipc_at = $request->approval_ipc_at;
                    break;
                case 'rejected_by_ipc_head':
                    $request->rejection_ipc_head_id = $request->approval_ipc_head_id;
                    $request->rejection_ipc_head_at = $request->approval_ipc_head_at;
                    break;
                case 'rejected_by_ga_admin':
                    $request->rejection_ga_admin_id = $request->approval_ga_admin_id;
                    $request->rejection_ga_admin_at = $request->approval_ga_admin_at;
                    break;
                case 'rejected_by_ga_head':
                    $request->rejection_ga_head_id = $request->approval_ga_head_id;
                    $request->rejection_ga_head_at = $request->approval_ga_head_at;
                    break;
            }
            
            // Clear the approval fields for rejected requests
            if ($request->status === 'rejected_by_head') {
                $request->approval_head_id = null;
                $request->approval_head_at = null;
            } elseif ($request->status === 'rejected_by_ipc') {
                $request->approval_ipc_id = null;
                $request->approval_ipc_at = null;
            } elseif ($request->status === 'rejected_by_ipc_head') {
                $request->approval_ipc_head_id = null;
                $request->approval_ipc_head_at = null;
            } elseif ($request->status === 'rejected_by_ga_admin') {
                $request->approval_ga_admin_id = null;
                $request->approval_ga_admin_at = null;
            } elseif ($request->status === 'rejected_by_ga_head') {
                $request->approval_ga_head_id = null;
                $request->approval_ga_head_at = null;
            }
            
            $request->save();
        }
    }
}
