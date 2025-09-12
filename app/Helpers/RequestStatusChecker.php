<?php

namespace App\Helpers;

use App\Models\OfficeStationeryStockRequest;
use App\Models\OfficeStationeryStockUsage;
use App\Models\MarketingMediaStockRequest;
use App\Models\MarketingMediaStockUsage;

class RequestStatusChecker
{
    /**
     * Check if an Office Stationery Stock Request is rejected by Division Head
     *
     * @param OfficeStationeryStockRequest $request
     * @return bool
     */
    public static function rejectedByDivHead($record): bool
    {
        return $record->rejection_head_id;
    }
    
    /**
     * Check if an Office Stationery Stock Request is rejected by IPC Admin
     *
     * @param OfficeStationeryStockRequest $request
     * @return bool
     */
    public static function rejectedByIpcAdmin($record): bool
    {
        return $record->rejection_ipc_id;
    }

    /**
     * Check if an Office Stationery Stock Request is rejected by IPC Head
     *
     * @param OfficeStationeryStockRequest $request
     * @return bool
     */
    public static function rejectedByIpcHead($record): bool
    {
        return $record->rejection_ipc_head_id;
    }
    

    /**
     * Check if an Office Stationery Stock Request is rejected by GA Admin
     *
     * @param OfficeStationeryStockRequest $request
     * @return bool
     */
    public static function rejectedByGaAdmin($record): bool
    {
        return $record->rejection_ga_admin_id;
    }
    

    /**
     * Check if an Office Stationery Stock Request is rejected by HCG Head
     *
     * @param OfficeStationeryStockRequest $request
     * @return bool
     */
    public static function rejectedByHcgHead($record): bool
    {
        return $record->rejection_hcg_head_id;
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
