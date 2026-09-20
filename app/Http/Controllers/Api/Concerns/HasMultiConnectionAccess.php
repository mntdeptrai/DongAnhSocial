<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Eatery;
use Illuminate\Support\Facades\Auth;

/**
 * Trait HasMultiConnectionAccess — Trait hỗ trợ truy vấn và kiểm tra quyền đa database connection
 */
trait HasMultiConnectionAccess
{
    protected function findEateryAndConnection($eateryId)
    {
        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
        foreach ($connections as $conn) {
            $eatery = Eatery::on($conn)->find($eateryId);
            if ($eatery) {
                return [$eatery, $conn];
            }
        }
        return [null, null];
    }

    protected function findModelAndConnection($modelClass, $id)
    {
        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
        foreach ($connections as $conn) {
            $model = $modelClass::on($conn)->find($id);
            if ($model) {
                return [$model, $conn];
            }
        }
        return [null, null];
    }

    protected function checkAccess($eateryId)
    {
        $role = session('user_role') ?? (Auth::check() ? Auth::user()->role : 'user');
        $userId = session('user_id') ?? (Auth::check() ? Auth::user()->id : null);

        if ($role === 'admin') {
            return true;
        }

        if ($role === 'seller') {
            list($eatery, $conn) = $this->findEateryAndConnection($eateryId);
            if ($eatery && (int)$eatery->user_id === (int)$userId) {
                return true;
            }
        }

        return false;
    }

    protected function checkModelAccess($modelClass, $id)
    {
        list($model, $conn) = $this->findModelAndConnection($modelClass, $id);
        if (!$model) {
            return false;
        }

        if (isset($model->eatery_id)) {
            return $this->checkAccess($model->eatery_id);
        }

        return false;
    }
}
