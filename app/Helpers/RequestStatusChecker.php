<?php

namespace App\Helpers;

use App\Models\OfficeStationeryStockRequest;
use App\Models\OfficeStationeryStockUsage;
use App\Models\MarketingMediaStockRequest;
use App\Models\MarketingMediaStockUsage;

class RequestStatusChecker
{
    // ATK Stock Request Approval
    /**
     * Check if an Office Stationery Stock Request need approval from Division Head
     *
     * @param OfficeStationeryStockRequest $record
     * @return bool
     */
    public static function atkStockRequestNeedApprovalFromDivisionHead($record): bool
    {
        return $record->status === OfficeStationeryStockRequest::STATUS_PENDING && UserRoleChecker::isDivisionHead();
    }
    
    /**
     * Check if an Office Stationery Stock Request need approval from IPC Admin
     *
     * @param OfficeStationeryStockRequest $record
     * @return bool
     */
    public static function atkStockRequestNeedApprovalFromIpcAdmin($record): bool
    {
        return $record->status === OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD && UserRoleChecker::isIpcAdmin();
    }
    
    /**
     * Check if an Office Stationery Stock Request need approval from IPC Head
     *
     * @param OfficeStationeryStockRequest $record
     * @return bool
     */
    public static function atkStockRequestNeedApprovalFromIpcHead($record): bool
    {
        return $record->status === OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC && UserRoleChecker::isIpcHead();
    }
    
    /**
     * Check if an Office Stationery Stock Request need stock adjustment approval from IPC Admin
     *
     * @param OfficeStationeryStockRequest $record
     * @return bool
     */
    public static function atkStockRequestNeedStockAdjustmentApprovalFromIpcAdmin($record): bool
    {
        return $record->status === OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD && UserRoleChecker::isIpcAdmin();
    }
    
    /**
     * Check if an Office Stationery Stock Request need second approval from IPC Head after Stock Adjustment approved
     *
     * @param OfficeStationeryStockRequest $record
     * @return bool
     */
    public static function atkStockRequestNeedSecondApprovalFromIpcHead($record): bool
    {
        return $record->status === OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT && UserRoleChecker::isIpcHead();
    }
    
    /**
     * Check if an Office Stationery Stock Request need approval from GA Admin
     *
     * @param OfficeStationeryStockRequest $record
     * @return bool
     */
    public static function atkStockRequestNeedApprovalFromGaAdmin($record): bool
    {
        return $record->status === OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD && UserRoleChecker::isGaAdmin();
    }
    
    /**
     * Check if an Office Stationery Stock Request need approval from HCG Head
     *
     * @param OfficeStationeryStockRequest $record
     * @return bool
     */
    public static function atkStockRequestNeedApprovalFromHcgHead($record): bool
    {
        return $record->status === OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN && UserRoleChecker::isHcgHead();
    }

    // ATK Stock Request Rejection
    /**
     * Check if an Office Stationery Stock Request is rejected by Division Head
     *
     * @param OfficeStationeryStockRequest $record
     * @return bool
     */
    public static function atkStockRequestRejectedByDivHead($record): bool
    {
        return $record->status === OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD && $record->rejection_head_id;
    }
    
    /**
     * Check if an Office Stationery Stock Request is rejected by IPC Admin
     *
     * @param OfficeStationeryStockRequest $record
     * @return bool
     */
    public static function atkStockRequestRejectedByIpcAdmin($record): bool
    {
        return $record->status === OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC && $record->rejection_ipc_id;
    }

    /**
     * Check if an Office Stationery Stock Request is rejected by IPC Head
     *
     * @param OfficeStationeryStockRequest $record
     * @return bool
     */
    public static function atkStockRequestRejectedByIpcHead($record): bool
    {
        return $record->status === OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD && $record->rejection_ipc_head_id;
    }
    
    /**
     * Check if an Office Stationery Stock Request is rejected by GA Admin
     *
     * @param OfficeStationeryStockRequest $record
     * @return bool
     */
    public static function atkStockRequestRejectedByGaAdmin($record): bool
    {
        return $record->status === OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN && $record->rejection_ga_admin_id;
    }
    
    /**
     * Check if an Office Stationery Stock Request is rejected by HCG Head
     *
     * @param OfficeStationeryStockRequest $record
     * @return bool
     */
    public static function atkStockRequestRejectedByHcgHead($record): bool
    {
        return $record->status === OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD && $record->rejection_hcg_head_id;
    }
    
    // ATK Stock Usage Approval
    /**
     * Check if an Office Stationery Stock Usage need approval from Division Head
     *
     * @param OfficeStationeryStockUsage $record
     * @return bool
     */
    public static function atkStockUsageNeedApprovalFromDivisionHead($record): bool
    {
        return $record->status === OfficeStationeryStockUsage::STATUS_PENDING && UserRoleChecker::isDivisionHead();
    }
    
    /**
     * Check if an Office Stationery Stock Usage need approval from GA Admin
     *
     * @param OfficeStationeryStockUsage $record
     * @return bool
     */
    public static function atkStockUsageNeedApprovalFromGaAdmin($record): bool
    {
        return $record->status === OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD && UserRoleChecker::isGaAdmin();
    }
    
    /**
     * Check if an Office Stationery Stock Usage need approval from HCG Head
     *
     * @param OfficeStationeryStockUsage $record
     * @return bool
     */
    public static function atkStockUsageNeedApprovalFromHcgHead($record): bool
    {
        return $record->status === OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN && UserRoleChecker::isHcgHead();
    }
    
    // ATK Stock Usage Rejection
    /**
     * Check if an Office Stationery Stock Usage rejected by Division Head
     *
     * @param OfficeStationeryStockUsage $record
     * @return bool
     */
    public static function atkStockUsageRejectedByDivisionHead($record): bool
    {
        return $record->status === OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD && $record->rejection_head_id;
    }
    
    /**
     * Check if an Office Stationery Stock Usage rejected by GA Admin
     *
     * @param OfficeStationeryStockUsage $record
     * @return bool
     */
    public static function atkStockUsageRejectedByGaAdmin($record): bool
    {
        return $record->status === OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN && $record->rejection_ga_admin_id;
    }
    
    /**
     * Check if an Office Stationery Stock Usage rejected by HCG Head
     *
     * @param OfficeStationeryStockUsage $record
     * @return bool
     */
    public static function atkStockUsageRejectedByHcgHead($record): bool
    {
        return $record->status === OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD && $record->rejection_head_id;
    }

    // Marketing Media Stock Request Approval
    /**
     * Check if a Marketing Media Stock Request need approval from Division Head
     *
     * @param MarketingMediaStockRequest $record
     * @return bool
     */
    public static function marketingMediaStockRequestNeedApprovalFromDivisionHead($record): bool
    {
        return $record->status === MarketingMediaStockRequest::STATUS_PENDING && UserRoleChecker::isDivisionHead();
    }
    
    /**
     * Check if a Marketing Media Stock Request need approval from IPC Admin
     *
     * @param MarketingMediaStockRequest $record
     * @return bool
     */
    public static function marketingMediaStockRequestNeedApprovalFromIpcAdmin($record): bool
    {
        return $record->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD && UserRoleChecker::isIpcAdmin();
    }
    
    /**
     * Check if a Marketing Media Stock Request need approval from IPC Head
     *
     * @param MarketingMediaStockRequest $record
     * @return bool
     */
    public static function marketingMediaStockRequestNeedApprovalFromIpcHead($record): bool
    {
        return $record->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC && UserRoleChecker::isIpcHead();
    }
    
    /**
     * Check if a Marketing Media Stock Request need stock adjustment approval from IPC Admin
     *
     * @param MarketingMediaStockRequest $record
     * @return bool
     */
    public static function marketingMediaStockRequestNeedStockAdjustmentApprovalFromIpcAdmin($record): bool
    {
        return $record->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD && UserRoleChecker::isIpcAdmin();
    }
    
    /**
     * Check if a Marketing Media Stock Request need second approval from IPC Head after Stock Adjustment approved
     *
     * @param MarketingMediaStockRequest $record
     * @return bool
     */
    public static function marketingMediaStockRequestNeedSecondApprovalFromIpcHead($record): bool
    {
        return $record->status === MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT && UserRoleChecker::isIpcHead();
    }
    
    /**
     * Check if a Marketing Media Stock Request need approval from GA Admin
     *
     * @param MarketingMediaStockRequest $record
     * @return bool
     */
    public static function marketingMediaStockRequestNeedApprovalFromGaAdmin($record): bool
    {
        return $record->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD && UserRoleChecker::isGaAdmin();
    }
    
    /**
     * Check if a Marketing Media Stock Request need approval from MKS Head
     *
     * @param MarketingMediaStockRequest $record
     * @return bool
     */
    public static function marketingMediaStockRequestNeedApprovalFromMksHead($record): bool
    {
        return $record->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN && UserRoleChecker::isMksHead();
    }

    // Marketing Media Stock Request Rejection
    /**
     * Check if an Office Stationery Stock Request is rejected by Division Head
     *
     * @param MarketingMediaStockRequest $record
     * @return bool
     */
    public static function marketingMediaStockRequestRejectedByDivHead($record): bool
    {
        return $record->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD && $record->rejection_head_id;
    }
    
    /**
     * Check if an Office Stationery Stock Request is rejected by IPC Admin
     *
     * @param MarketingMediaStockRequest $record
     * @return bool
     */
    public static function marketingMediaStockRequestRejectedByIpcAdmin($record): bool
    {
        return $record->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC && $record->rejection_ipc_id;
    }

    /**
     * Check if an Office Stationery Stock Request is rejected by IPC Head
     *
     * @param MarketingMediaStockRequest $record
     * @return bool
     */
    public static function marketingMediaStockRequestRejectedByIpcHead($record): bool
    {
        return $record->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD && $record->rejection_ipc_head_id;
    }
    
    /**
     * Check if an Office Stationery Stock Request is rejected by GA Admin
     *
     * @param MarketingMediaStockRequest $record
     * @return bool
     */
    public static function marketingMediaStockRequestRejectedByGaAdmin($record): bool
    {
        return $record->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN && $record->rejection_ga_admin_id;
    }
    
    /**
     * Check if an Office Stationery Stock Request is rejected by HCG Head
     *
     * @param MarketingMediaStockRequest $record
     * @return bool
     */
    public static function marketingMediaStockRequestRejectedByMksHead($record): bool
    {
        return $record->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD && $record->rejection_marketing_head_id;
    }
    
    // Marketing Media Stock Usage Approval
    /**
     * Check if an Office Stationery Stock Usage need approval from Division Head
     *
     * @param MarketingMediaStockUsage $record
     * @return bool
     */
    public static function marketingMediaStockUsageNeedApprovalFromDivisionHead($record): bool
    {
        return $record->status === MarketingMediaStockUsage::STATUS_PENDING && UserRoleChecker::isDivisionHead();
    }
    
    /**
     * Check if an Office Stationery Stock Usage need approval from GA Admin
     *
     * @param MarketingMediaStockUsage $record
     * @return bool
     */
    public static function marketingMediaStockUsageNeedApprovalFromGaAdmin($record): bool
    {
        return $record->status === MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD && UserRoleChecker::isGaAdmin();
    }
    
    /**
     * Check if an Office Stationery Stock Usage need approval from HCG Head
     *
     * @param MarketingMediaStockUsage $record
     * @return bool
     */
    public static function marketingMediaStockUsageNeedApprovalFromMksHead($record): bool
    {
        return $record->status === MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN && UserRoleChecker::isMksHead();
    }
    
    // Marketing Media Stock Usage Rejection
    /**
     * Check if an Office Stationery Stock Usage rejected by Division Head
     *
     * @param MarketingMediaStockUsage $record
     * @return bool
     */
    public static function marketingMediaStockUsageRejectedByDivisionHead($record): bool
    {
        return $record->status === MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD && $record->rejection_head_id;
    }
    
    /**
     * Check if an Office Stationery Stock Usage rejected by GA Admin
     *
     * @param MarketingMediaStockUsage $record
     * @return bool
     */
    public static function marketingMediaStockUsageRejectedByGaAdmin($record): bool
    {
        return $record->status === MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN && $record->rejection_ga_admin_id;
    }
    
    /**
     * Check if an Office Stationery Stock Usage rejected by HCG Head
     *
     * @param MarketingMediaStockUsage $record
     * @return bool
     */
    public static function marketingMediaStockUsageRejectedByMksHead($record): bool
    {
        return $record->status === MarketingMediaStockUsage::STATUS_REJECTED_BY_MKT_HEAD && $record->rejection_marketing_head_id;
    }
    /**
     * Check if a Marketing Media Stock Request is rejected by Marketing Support Head
     *
     * @param MarketingMediaStockRequest $record
     * @return bool
     */
    public static function rejectedByMarketingHead($record): bool
    {
        return $record->rejection_marketing_head_id;
    }

    /**
     * Check if an Office Stationery Stock Request is Rejected
     *
     * @param OfficeStationeryStockRequest $record
     * @return bool
     */
    public static function stockRequestIsRejected($record)
    {
        return $record->rejection_reason && $record->rejection_head_id || $record->rejection_ipc_id || $record->rejection_ipc_head_id || $record->rejection_ga_admin_id || $record->rejection_hcg_head_id;
    }
    
    /**
     * Check if an Office Stationery Stock Usage is Rejected
     *
     * @param OfficeStationeryStockUsage $record
     * @return bool
     */
    public static function stockUsageIsRejected($record)
    {
        return $record->rejection_reason && $record->rejection_head_id || $record->rejection_ipc_id || $record->rejection_ipc_head_id || $record->rejection_ga_admin_id || $record->rejection_hcg_head_id;
    }

    /**
     * Check if an Office Stationery Stock Request can be resubmitted
     *
     * @param OfficeStationeryStockRequest $request
     * @return bool
     */
    public static function canResubmitOfficeStationeryStockRequest(OfficeStationeryStockRequest $request): bool
    {
        return in_array($request->status, [
            OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD,
            OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC,
            OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD,
        ]);
    }

    /**
     * Check if an Office Stationery Stock Usage can be resubmitted
     *
     * @param OfficeStationeryStockUsage $usage
     * @return bool
     */
    public static function canResubmitOfficeStationeryStockUsage(OfficeStationeryStockUsage $usage): bool
    {
        return in_array($usage->status, [
            OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD,
            OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN,
            OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD,
        ]);
    }

    /**
     * Check if a Marketing Media Stock Request can be resubmitted
     *
     * @param MarketingMediaStockRequest $request
     * @return bool
     */
    public static function canResubmitMarketingMediaStockRequest(MarketingMediaStockRequest $request): bool
    {
        return $request->rejection_reason && in_array($request->status, [
            MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD,
            MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC,
            MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD,
        ]);
    }

    /**
     * Check if a Marketing Media Stock Usage can be resubmitted
     *
     * @param MarketingMediaStockUsage $usage
     * @return bool
     */
    public static function canResubmitMarketingMediaStockUsage(MarketingMediaStockUsage $usage): bool
    {
        return $usage->rejection_reason && in_array($usage->status, [
            MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD,
            MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN,
            MarketingMediaStockUsage::STATUS_REJECTED_BY_MKT_HEAD,
        ]);
    }

    /**
     * Check if a stock request (either Office Stationery or Marketing Media) can be resubmitted
     *
     * @param mixed $request
     * @return bool
     */
    public static function canResubmitStockRequest($request): bool
    {
        if ($request instanceof OfficeStationeryStockRequest) {
            return self::canResubmitOfficeStationeryStockRequest($request);
        }

        if ($request instanceof MarketingMediaStockRequest) {
            return self::canResubmitMarketingMediaStockRequest($request);
        }

        return false;
    }

    /**
     * Check if a stock usage (either Office Stationery or Marketing Media) can be resubmitted
     *
     * @param mixed $usage
     * @return bool
     */
    public static function canResubmitStockUsage($usage): bool
    {
        if ($usage instanceof OfficeStationeryStockUsage) {
            return self::canResubmitOfficeStationeryStockUsage($usage);
        }

        if ($usage instanceof MarketingMediaStockUsage) {
            return self::canResubmitMarketingMediaStockUsage($usage);
        }

        return false;
    }
}
