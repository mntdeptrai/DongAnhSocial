<?php

namespace App\Services;

use App\Helpers\R2Helper;
use App\Models\DailyFoodLog;
use App\Models\Eatery;
use App\Models\FoodSafetyCertificate;
use App\Models\FoodSupplyContract;
use App\Models\PurchaseInvoice;

class TrustHubService
{
    public function storeCertificate(array $data, $file = null): ?FoodSafetyCertificate
    {
        if ($file) {
            $data['image_path'] = R2Helper::upload($file, 'trust/certificates');
        }
        return $this->storeFoodSafetyCertificate($data);
    }

    public function storeFoodSafetyCertificate(array $data): ?FoodSafetyCertificate
    {
        $eatery = Eatery::find($data['eatery_id'] ?? null);
        if (!$eatery) return null;

        return FoodSafetyCertificate::create($data);
    }

    public function storeDailyLog(array $data, $file = null): ?DailyFoodLog
    {
        if ($file) {
            $data['image_path'] = R2Helper::upload($file, 'trust/logs');
        }
        return $this->storeDailyFoodLog($data);
    }

    public function storeDailyFoodLog(array $data): ?DailyFoodLog
    {
        $eatery = Eatery::find($data['eatery_id'] ?? null);
        if (!$eatery) return null;

        return DailyFoodLog::create($data);
    }

    public function deleteDailyLog(int $id): bool
    {
        $log = DailyFoodLog::find($id);
        if (!$log) return false;

        return (bool) $log->delete();
    }

    public function deleteDailyFoodLog(int $id): bool
    {
        return $this->deleteDailyLog($id);
    }

    public function storeContract(array $data, $file = null): ?FoodSupplyContract
    {
        if ($file) {
            $data['image_path'] = R2Helper::upload($file, 'trust/contracts');
        }
        return $this->storeFoodSupplyContract($data);
    }

    public function storeFoodSupplyContract(array $data): ?FoodSupplyContract
    {
        $eatery = Eatery::find($data['eatery_id'] ?? null);
        if (!$eatery) return null;

        return FoodSupplyContract::create($data);
    }

    public function deleteContract(int $id): bool
    {
        $contract = FoodSupplyContract::find($id);
        if (!$contract) return false;

        return (bool) $contract->delete();
    }

    public function deleteFoodSupplyContract(int $id): bool
    {
        return $this->deleteContract($id);
    }

    public function storeInvoice(array $data, $file = null): ?PurchaseInvoice
    {
        if ($file) {
            $data['image_path'] = R2Helper::upload($file, 'trust/invoices');
        }
        return $this->storePurchaseInvoice($data);
    }

    public function storePurchaseInvoice(array $data): ?PurchaseInvoice
    {
        $eatery = Eatery::find($data['eatery_id'] ?? null);
        if (!$eatery) return null;

        return PurchaseInvoice::create($data);
    }

    public function deleteInvoice(int $id): bool
    {
        $invoice = PurchaseInvoice::find($id);
        if (!$invoice) return false;

        return (bool) $invoice->delete();
    }

    public function deletePurchaseInvoice(int $id): bool
    {
        return $this->deleteInvoice($id);
    }
}
