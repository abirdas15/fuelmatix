<?php

namespace App\Common;

class Section
{
    const SHIFT_SALE = 'Shift-Sale';
    const POS = 'Pos';
    const POS_HISTORY = 'Pos-History';
    const COMPANY_SALE = 'Company-Sale';
    const TANK_REFILL = 'Tank-Refill';
    const TANK_VISUAL = 'Tank-Visual';
    const TANK_READING = 'Tank-Reading';
    const NOZZLE_READING = 'Nozzle-Reading';
    const PAY_ORDER = 'Pay-Order';
    const TRANSFER = 'Transfer';
    const EXPENSE = 'Expense';
    const BILL = 'Bill';
    const EMPLOYEE = 'Employee';
    const SALARY = 'Salary';
    const ATTENDANCE = 'Attendance';
    const TANK = 'Tank';
    const DISPENSER = 'Dispenser';
    const NOZZLE = 'Nozzle';
    const PRODUCT = 'Product';
    const BANK = 'Bank';
    const VENDOR = 'Vendor';
    const CREDIT_COMPANY = 'Credit-Company';
    const USER = 'User';
    const SYSTEM_SETTING = 'System-Setting';
    const VOUCHER = 'Voucher';
    const DRIVER = 'Driver';
    const ACCOUNTING = 'Accounting';
    const DAILY_REPORT = 'Daily-Report';
    const BALANCE_SHEET = 'Balance-Sheet';
    const PROFIT_AND_LOSS = 'Profit-and-Loss';
    const INCOME_STATEMENT = 'Income-Statement';
    const ACCOUNT_PAYABLE = 'Account-Payable';
    const ACCOUNT_RECEIVABLE = 'Account-Receivable';
    const TRAIL_BALANCE = 'Trail-Balance';
    const LEDGER = 'Ledger';
    const ROLE = 'Role';
    const POS_MACHINE = 'Pos-Machine';
    const INVOICE = 'Invoice';
    const FUEL_ADJUSTMENT = 'Fue-Adjustment';
    const UNAUTHORIZED_BILL = 'Unauthorized-Bill';
    const SALES_REPORT = 'Sales-Report';
    const COMPANY_BILL = 'Company-Bill';
    const INVOICE_PAYMENT = 'Invoice-Payment';
    const PURCHASE = 'Purchase';
    const CAR = 'Car';
    const DUMMY_SALE = 'Dummy-Sale';
    const DUMMY_SALE_HISTORY = 'Dummy-Sale-History';
    const BULK_SALE = 'Bulk-Sale';
    const SALES_STOCK = 'Sales-Stock';
    const VENDOR_REPORT = 'Vendor-Report';
    const EXPENSE_REPORT = 'Expense-Report';
    const WINDFALL_REPORT = 'Windfall-Report';
    const CREDIT_COMPANY_REPORT = 'Credit-Company-Report';
    const DRIVER_REPORT = 'Driver-Report';
    const BILL_SUMMARY = 'Bill-Summary';
    const POS_REPORT = 'Pos-Report';
    const STAFF_LOAN = 'Staff-Loan';

    /**
     * @return array
     */
    public static function getArray(): array
    {
        return [
            'SHIFT_SALE' => self::SHIFT_SALE,
            'POS' => self::POS,
            'POS_HISTORY' => self::POS_HISTORY,
            'COMPANY_SALE' => self::COMPANY_SALE,
            'TANK_REFILL' => self::TANK_REFILL,
            'TANK_VISUAL' => self::TANK_VISUAL,
            'TANK_READING' => self::TANK_READING,
            'NOZZLE_READING' => self::NOZZLE_READING,
            'PAY_ORDER' => self::PAY_ORDER,
            'TRANSFER' => self::TRANSFER,
            'EXPENSE' => self::EXPENSE,
            'BILL' => self::BILL,
            'EMPLOYEE' => self::EMPLOYEE,
            'SALARY' => self::SALARY,
            'ATTENDANCE' => self::ATTENDANCE,
            'TANK' => self::TANK,
            'DISPENSER' => self::DISPENSER,
            'NOZZLE' => self::NOZZLE,
            'PRODUCT' => self::PRODUCT,
            'BANK' => self::BANK,
            'VENDOR' => self::VENDOR,
            'CREDIT_COMPANY' => self::CREDIT_COMPANY,
            'USER' => self::USER,
            'SYSTEM_SETTING' => self::SYSTEM_SETTING,
            'VOUCHER' => self::VOUCHER,
            'DRIVER' => self::DRIVER,
            'ACCOUNTING' => self::ACCOUNTING,
            'DAILY_REPORT' => self::DAILY_REPORT,
            'BALANCE_SHEET' => self::BALANCE_SHEET,
            'PROFIT_AND_LOSS' => self::PROFIT_AND_LOSS,
            'INCOME_STATEMENT' => self::INCOME_STATEMENT,
            'ACCOUNT_PAYABLE' => self::ACCOUNT_PAYABLE,
            'ACCOUNT_RECEIVABLE' => self::ACCOUNT_RECEIVABLE,
            'TRAIL_BALANCE' => self::TRAIL_BALANCE,
            'LEDGER' => self::LEDGER,
            'ROLE' => self::ROLE,
            'POS_MACHINE' => self::POS_MACHINE,
            'INVOICE' => self::INVOICE,
            'FUEL_ADJUSTMENT' => self::FUEL_ADJUSTMENT,
            'UNAUTHORIZED_BILL' => self::UNAUTHORIZED_BILL,
            'SALES_REPORT' => self::SALES_REPORT,
            'COMPANY_BILL' => self::COMPANY_BILL,
            'INVOICE_PAYMENT' => self::INVOICE_PAYMENT,
            'PURCHASE' => self::PURCHASE,
            'CAR' => self::CAR,
            'DUMMY_SALE' => self::DUMMY_SALE,
            'DUMMY_SALE_HISTORY' => self::DUMMY_SALE_HISTORY,
            'BULK_SALE' => self::BULK_SALE,
            'SALES_STOCK' => self::SALES_STOCK,
            'VENDOR_REPORT' => self::VENDOR_REPORT,
            'EXPENSE_REPORT' => self::EXPENSE_REPORT,
            'WINDFALL_REPORT' => self::WINDFALL_REPORT,
            'CREDIT_COMPANY_REPORT' => self::CREDIT_COMPANY_REPORT,
            'DRIVER_REPORT' => self::DRIVER_REPORT,
            'BILL_SUMMARY' => self::BILL_SUMMARY,
            'POS_REPORT' => self::POS_REPORT,
            'STAFF_LOAN' => self::STAFF_LOAN,
        ];
    }
}
