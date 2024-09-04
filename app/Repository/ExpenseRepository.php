<?php

namespace App\Repository;

use App\Common\FuelMatixDateTimeFormat;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Expense;

class ExpenseRepository
{
    public static function list($filter)
    {
        $sessionUser = SessionUser::getUser();
        // Build the query to fetch expenses
        $result = Expense::select('expense.id', 'expense.date', 'expense.amount', 'expense.paid_to', 'expense.remarks', 'c.name as expense', 'c1.name as payment', 'expense.status', 'users.name as approve_by', 'expense.file')
            ->leftJoin('categories as c', 'c.id', 'expense.category_id')
            ->leftJoin('categories as c1', 'c1.id', 'expense.payment_id')
            ->leftJoin('users', 'users.id', '=', 'expense.approve_by')
            ->where('expense.client_company_id', $sessionUser['client_company_id']);

        // Apply keyword filtering if provided
        if (!empty($filter['keyword'])) {
            $result->where(function($q) use ($filter) {
                $q->where('c.name', 'LIKE', '%'.$filter['keyword'].'%')
                    ->orWhere('c1.name', 'LIKE', '%'.$filter['keyword'].'%');
            });
        }

        if (!empty($filter['start_date']) && !empty($filter['end_date'])) {
            $result->where(function($q) use ($filter) {
                $q->whereBetween('date', [$filter['start_date'], $filter['end_date']]);
            });
        }

        // Order by 'id' in descending order and paginate the results
        $result = $result->orderBy('id', 'DESC')->paginate($filter['limit']);

        // Format the results
        foreach ($result as &$data) {
            $data['amount_format'] = number_format($data['amount'], 2);
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE);
        }
        return $result->toArray();
    }
}
