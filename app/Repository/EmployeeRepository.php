<?php

namespace App\Repository;

use App\Common\AccountCategory;
use App\Models\Category;

class EmployeeRepository
{
    public static function list($inputData)
    {
        $limit = $inputData['limit'] ?? 10;
        $keyword = $inputData['keyword'] ?? '';
        $order_by = $inputData['order_by'] ?? 'id';
        $order_mode = $inputData['order_mode'] ?? 'DESC';
        $salaryExpense = Category::select('id')->where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', strtolower(AccountCategory::SALARY_EXPENSE))->first();
        $result = Category::select('id', 'name', 'rfid', 'others')
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->where('parent_category', $salaryExpense->id);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('name', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $others = json_decode($data['others']);
            $data['position'] = $others != null ? $others->position : null;
            $data['salary'] = $others != null ? $others->salary : null;
            unset($data['others']);
        }
        return $result;
    }
}
