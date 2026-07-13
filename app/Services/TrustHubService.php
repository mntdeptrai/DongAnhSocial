<?php

namespace App\Services;

use App\Helpers\R2Helper;
use App\Services\EateryApiService;

class TrustHubService
{
    public function storeCertificate(array $data, $file)
    {
        if ($file) {
            $data['certificate_path'] = R2Helper::upload($file, 'trust/certificates');
        }
        return EateryApiService::storeFoodSafetyCertificate($data);
    }

    public function storeDailyLog(array $data, $file)
    {
        if ($file) {
            $data['image_path'] = R2Helper::upload($file, 'trust/logs');
        }
        return EateryApiService::storeDailyFoodLog($data);
    }

    public function deleteDailyLog($id): bool
    {
        return EateryApiService::deleteDailyFoodLog($id);
    }

    public function storeContract(array $data, $file)
    {
        if ($file) {
            $data['image_path'] = R2Helper::upload($file, 'trust/contracts');
        }
        return EateryApiService::storeFoodSupplyContract($data);
    }

    public function deleteContract($id): bool
    {
        return EateryApiService::deleteFoodSupplyContract($id);
    }

    public function storeInvoice(array $data, $file)
    {
        if ($file) {
            $data['image_path'] = R2Helper::upload($file, 'trust/invoices');
        }
        return EateryApiService::storePurchaseInvoice($data);
    }

    public function deleteInvoice($id): bool
    {
        return EateryApiService::deletePurchaseInvoice($id);
    }
}
