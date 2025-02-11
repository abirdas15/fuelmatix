import Vue from "vue";
import VueRouter from "vue-router";
import Vuex from "vuex";
import store from "../Store/store";

Vue.use(Vuex);

import Layout from "../Pages/Layout/Layout.vue";
import Login from "../Pages/Auth/Login";
import Dashboard from "../Pages/Dashboard/Dashboard.vue";
import Category from "../Pages/Category/Category.vue";
import Transaction from "../Pages/Transaction/Transaction.vue";
import BalanceSheet from "../Pages/BalanceSheet/Balance";
import ProfitLoss from "../Pages/ProfitLoss/profit-loss";
import IncomeStatement from "../Pages/IncomeStatement/income-statement";
import AccountPayable from "../Pages/AccountPayable/payable";
import AccountReceivable from "../Pages/AccountReceivable/receivable";
import TrailBalance from "../Pages/TrialBalance/trail";
import LedgerSheet from "../Pages/Ledger/ledger";
import Product from "../Pages/Fuel/Product/List";
import ProductAdd from "../Pages/Fuel/Product/Add";
import ProductEdit from "../Pages/Fuel/Product/Edit";
import Dispenser from "../Pages/Fuel/Dispenser/List";
import DispenserAdd from "../Pages/Fuel/Dispenser/Add";
import DispenserEdit from "../Pages/Fuel/Dispenser/Edit";
import Nozzle from "../Pages/Fuel/Nozzle/List";
import NozzleAdd from "../Pages/Fuel/Nozzle/Add";
import NozzleEdit from "../Pages/Fuel/Nozzle/Edit";
import NozzleReading from "../Pages/Fuel/NozzleReading/List";
import NozzleReadingAdd from "../Pages/Fuel/NozzleReading/Add";
import NozzleReadingEdit from "../Pages/Fuel/NozzleReading/Edit";
import ShiftSaleStart from "../Pages/ShiftSale/ShiftSaleAdd";
import ShiftSaleList from "../Pages/ShiftSale/List.vue";
import ShiftSaleListStart from "../Pages/ShiftSale/ListStart.vue";
import ShiftSaleEdit from "../Pages/ShiftSale/ShiftSaleEdit.vue";
import ShiftSaleView from "../Pages/ShiftSale/ShiftSaleView.vue";
import Pos from "../Pages/Pos/Pos";
import PosList from "../Pages/Pos/List.vue";
import PosEdit from "../Pages/Pos/Edit.vue";
import PosView from "../Pages/Pos/View.vue";
import Expense from "../Pages/Expenses/List";
import ExpenseAdd from "../Pages/Expenses/Add";
import ExpenseEdit from "../Pages/Expenses/Edit";
import Tank from "../Pages/Fuel/Tank/List";
import TankVisual from "../Pages/Fuel/TankVisual/List";
import TankAdd from "../Pages/Fuel/Tank/Add";
import TankEdit from "../Pages/Fuel/Tank/Edit";
import TankReading from "../Pages/Fuel/TankReading/List";
import TankReadingAdd from "../Pages/Fuel/TankReading/Add";
import TankReadingEdit from "../Pages/Fuel/TankReading/Edit";
import TankRefill from "../Pages/Fuel/TankRefill/List";
import TankRefillAdd from "../Pages/Fuel/TankRefill/Add";
import TankRefillEdit from "../Pages/Fuel/TankRefill/Edit";
import Bank from "../Pages/Banks/List";
import BankAdd from "../Pages/Banks/Add";
import BankEdit from "../Pages/Banks/Edit";
import Vendor from "../Pages/Vendors/List";
import VendorAdd from "../Pages/Vendors/Add";
import VendorEdit from "../Pages/Vendors/Edit";
import PayOrder from "../Pages/PayOrder/List";
import PayOrderAdd from "../Pages/PayOrder/Add";
import PayOrderEdit from "../Pages/PayOrder/Edit";
import CreditCompany from "../Pages/CreditCompany/List.vue";
import CreditCompanyAdd from "../Pages/CreditCompany/Add.vue";
import CreditCompanyEdit from "../Pages/CreditCompany/Edit.vue";
import posMachine from "../Pages/posMachine/List.vue";
import posMachineAdd from "../Pages/posMachine/Add.vue";
import posMachineEdit from "../Pages/posMachine/Edit.vue";
import employee from "../Pages/Employee/List.vue";
import employeeAdd from "../Pages/Employee/Add.vue";
import employeeEdit from "../Pages/Employee/Edit.vue";
import balanceTransfer from "../Pages/AssetTransfer/List.vue";
import balanceTransferAdd from "../Pages/AssetTransfer/Add.vue";
import balanceTransferEdit from "../Pages/AssetTransfer/Edit.vue";
import user from "../Pages/Users/List.vue";
import userAdd from "../Pages/Users/Add.vue";
import userEdit from "../Pages/Users/Edit.vue";
import salary from "../Pages/Salary/List.vue";
import salaryAdd from "../Pages/Salary/Add.vue";
import salaryEdit from "../Pages/Salary/Edit.vue";
import CompanySale from "../Pages/CompanySale/List.vue";
import Invoice from "../Pages/Invoices/List.vue";
import InvoiceView from "../Pages/Invoices/View.vue";
import dailyReport from "../Pages/DailyReport/dailyReport.vue";
import system from "../Pages/System/Company.vue";
import Voucher from "../Pages/Voucher/Voucher.vue";
import Driver from "../Pages/Driver/Driver.vue";
import Role from "../Pages/Role/Role.vue";
import createRole from "../Pages/Role/Create.vue";
import roleEdit from "../Pages/Role/Edit.vue";
import fuelAdjustment from "../Pages/FuelAdjustment/Adjustment.vue";
import fuelAdjustmentList from "../Pages/FuelAdjustment/List.vue";
import fuelAdjustmentEdit from "../Pages/FuelAdjustment/Edit.vue";
import fuelAdjustmentView from "../Pages/FuelAdjustment/AdjustmentView.vue";
import UnauthorizedBill from "../Pages/UnauthorizedBill/UnauthorizedBill.vue";
import salesReport from "../Pages/SalesReport/List.vue";
import companyBills from "../Pages/CompanyBills/List.vue";
import ShiftSalePrevious from "../Pages/ShiftSale/ShiftSalePrevious.vue";
import car from "../Pages/Car/Car.vue";
import purchase from "../Pages/PurchaseBill/List.vue";
import purchaseAdd from "../Pages/PurchaseBill/Add.vue";
import purchaseEdit from "../Pages/PurchaseBill/Edit.vue";
import InvoicePayment from "../Pages/InvoicePayment/InvoicePayment.vue"
import VendorReport from "../Pages/VendorReport/VendorReport.vue";
import ExpenseReport from "../Pages/ExpenseReport/ExpenseReport.vue";
import NozzleStatus from "../Pages/NozzleStatus/NozzleStatus.vue";
import WindfallReport from "../Pages/Report/Windfall.vue";
import CreditCompanyReport from "../Pages/Report/CreditCompany.vue";
import DriverReport from "../Pages/Report/Driver.vue";
import StockSummary from "../Pages/Report/StockSummary.vue";
import BulkSaleAdd from "../Pages/BulkSale/Add.vue";
import BulkSale from "../Pages/BulkSale/List.vue";
import CompanySummary from "../Pages/Report/CompanySummary.vue";
import CompanyBillDetails from "../Pages/Report/CompanyBillDetails.vue";
import DummySaleCreate from "../Pages/DummySale/Create.vue";
import DummySaleList from "../Pages/DummySale/List.vue";
import DummySaleView from "../Pages/DummySale/View.vue";
import PosReport from "../Pages/Report/PosReport.vue";
import StaffLoanList from "../Pages/StaffLoan/List.vue";
import StaffLoanAdd from "../Pages/StaffLoan/Add.vue";
import StaffLoanView from "../Pages/StaffLoan/View.vue";
import ChallanList from "../Pages/Challan/List.vue";
import ChallanAdd from "../Pages/Challan/Add.vue";
import CompanyLoanAdd from "../Pages/CompanyLoan/Add.vue";
import CompanyLoanList from "../Pages/CompanyLoan/List.vue";
import CompanyLoanView from "../Pages/CompanyLoan/View.vue";
import LoanEntityAdd from "../Pages/LoanEntity/Add.vue";
import LoanEntityList from "../Pages/LoanEntity/List.vue";
import LoanEntityEdit from "../Pages/LoanEntity/Edit.vue";

const ROOT_URL = "";
const router = new VueRouter({
    scrollBehavior() {
        return { x: 0, y: 0 };
    },
    mode: "history",
    routes: [
        { path: ROOT_URL + "/auth/login", meta: { title: 'Login - FuelMatix' }, name: "Login", component: Login, beforeEnter: authCheck},
        {
            path: ROOT_URL + "/",
            name: "Layout",
            component: Layout,
            beforeEnter: authRequestCheck,
            children: [
                { path: ROOT_URL + "/dashboard", meta: { title: 'Dashboard - FuelMatix' }, name: "Dashboard", component: Dashboard},

                { path: ROOT_URL + "/nozzle/status", meta: { title: 'Nozzle Status - FuelMatix' }, name: "NozzleStatus", component: NozzleStatus},
                {
                    path: ROOT_URL + "/accounts", name: "Accounts", component: Category, meta: { title: 'Accounts - FuelMatix' },  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.ACCOUNTING + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/transaction/:id", meta: { title: 'Accounts Create - FuelMatix' }, name: "Transaction", component: Transaction,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.ACCOUNTING + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/balance-sheet", meta: { title: 'Balance Sheet - FuelMatix' }, name: "BalanceSheet", component: BalanceSheet, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.BALANCE_SHEET + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/profit-loss", meta: { title: 'Profit & Loss - FuelMatix' }, name: "ProfitLoss", component: ProfitLoss, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.PROFIT_AND_LOSS + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/income-statement", meta: { title: 'Income Statement - FuelMatix' }, name: "IncomeStatement", component: IncomeStatement, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.INCOME_STATEMENT + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/account-payable", meta: { title: 'Payable - FuelMatix' }, name: "AccountPayable", component: AccountPayable, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.ACCOUNT_PAYABLE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/account-receivable", meta: { title: 'Receivable - FuelMatix' }, name: "AccountReceivable", component: AccountReceivable, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.ACCOUNT_RECEIVABLE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/trial-balance", meta: { title: 'Trail Balance - FuelMatix' },  name: "TrailBalance", component: TrailBalance, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TRAIL_BALANCE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/ledger-sheet", meta: { title: 'Ledger Sheet - FuelMatix' }, name: "LedgerSheet", component: LedgerSheet, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.LEDGER + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/product", meta: { title: 'Product - FuelMatix' }, name: "Product", component: Product, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.PRODUCT + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/product/add", meta: { title: 'Product Add - FuelMatix' }, name: "ProductAdd", component: ProductAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.PRODUCT + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/product/edit/:id", meta: { title: 'Product Edit - FuelMatix' }, name: "ProductEdit", component: ProductEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.PRODUCT + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/dispenser", meta: { title: 'Dispenser - FuelMatix' }, name: "Dispenser", component: Dispenser, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.DISPENSER + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/dispenser/add", meta: { title: 'Dispenser Add - FuelMatix' }, name: "DispenserAdd", component: DispenserAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.DISPENSER + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/dispenser/edit/:id", meta: { title: 'Dispenser Edit - FuelMatix' }, name: "DispenserEdit", component: DispenserEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.DISPENSER + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/nozzle", meta: { title: 'Nozzle - FuelMatix' }, name: "Nozzle", component: Nozzle, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.NOZZLE_READING + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/nozzle/add", meta: { title: 'Nozzle Add - FuelMatix' }, name: "NozzleAdd", component: NozzleAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.NOZZLE_READING + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/nozzle/edit/:id", meta: { title: 'Nozzle Edit - FuelMatix' }, name: "NozzleEdit", component: NozzleEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.NOZZLE_READING + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/nozzle/reading", meta: { title: 'Nozzle Reading - FuelMatix' }, name: "NozzleReading", component: NozzleReading, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.NOZZLE_READING + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/nozzle/reading/add", meta: { title: 'Nozzle Reading Add - FuelMatix' }, name: "NozzleReadingAdd", component: NozzleReadingAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.NOZZLE_READING + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/nozzle/reading/edit/:id", meta: { title: 'Nozzle Reading Edit - FuelMatix' }, name: "NozzleReadingEdit", component: NozzleReadingEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.NOZZLE_READING + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/shift/sale/start", meta: { title: 'Shift Sale Start - FuelMatix' }, name: "ShiftSaleAdd", component: ShiftSaleStart,  beforeEnter: (to, from, next) => {
                    CheckPermission(to, from, next, Section.SHIFT_SALE + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/shift/sale/previous", meta: { title: 'Previous Shift Sale - FuelMatix' }, name: "ShiftSalePrevious", component: ShiftSalePrevious,  beforeEnter: (to, from, next) => {
                    CheckPermission(to, from, next, Section.SHIFT_SALE + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/shift/sale/list", meta: { title: 'Shift Sale - FuelMatix' }, name: "ShiftSaleList", component: ShiftSaleList, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.SHIFT_SALE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/shift/sale/list/start", meta: { title: 'Shift Sale - FuelMatix' }, name: "ShiftSaleListStart", component: ShiftSaleListStart, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.SHIFT_SALE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/shift/sale/edit/:id", meta: { title: 'Shift Sale Edit - FuelMatix' }, name: "ShiftSaleEdit", component: ShiftSaleEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.SHIFT_SALE + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/shift/sale/view/:id", meta: { title: 'Shift Sale View - FuelMatix' }, name: "ShiftSaleView", component: ShiftSaleView, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.SHIFT_SALE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/pos", meta: { title: 'POS - FuelMatix' }, name: "Pos", component: Pos, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.POS + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/pos/list", meta: { title: 'POS List - FuelMatix' }, name: "PosList", component: PosList, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.POS + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/pos/edit/:id", meta: { title: 'POS Edit - FuelMatix' }, name: "PosEdit", component: PosEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.POS + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/pos/view/:id", meta: { title: 'POS View - FuelMatix' }, name: "PosView", component: PosView
                },
                {
                    path: ROOT_URL + "/expense", meta: { title: 'Expense - FuelMatix' }, name: "Expense", component: Expense, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.EXPENSE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/expense/add", meta: { title: 'Expense Add - FuelMatix' }, name: "ExpenseAdd", component: ExpenseAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.EXPENSE + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/expense/edit/:id", meta: { title: 'Expense Edit - FuelMatix' }, name: "ExpenseEdit", component: ExpenseEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.EXPENSE + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/tank", name: "Tank", meta: { title: 'Tank - FuelMatix' }, component: Tank, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/tank/visual", meta: { title: 'Tank Visual - FuelMatix' }, name: "TankVisual", component: TankVisual, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK_VISUAL + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/tank/add", meta: { title: 'Tank Add - FuelMatix' }, name: "TankAdd", component: TankAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/tank/edit/:id", meta: { title: 'Tank Edit - FuelMatix' }, name: "TankEdit", component: TankEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/tank/reading", meta: { title: 'Tank Reading - FuelMatix' }, name: "TankReading", component: TankReading, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK_READING + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/tank/reading/add", meta: { title: 'Tank Reading Add - FuelMatix' }, name: "TankReadingAdd", component: TankReadingAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK_READING + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/tank/reading/edit/:id", meta: { title: 'Tank Reading Edit - FuelMatix' }, name: "TankReadingEdit", component: TankReadingEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK_READING + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/tank/refill", meta: { title: 'Tank Refill - FuelMatix' }, name: "TankRefill", component: TankRefill, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK_REFILL + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/tank/refill/add", meta: { title: 'Tank Refill Add - FuelMatix' }, name: "TankRefillAdd", component: TankRefillAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK_REFILL + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/tank/refill/edit/:id", meta: { title: 'Tank Refill Edit - FuelMatix' }, name: "TankRefillEdit", component: TankRefillEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK_REFILL + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/bank", meta: { title: 'Bank - FuelMatix' }, name: "Bank", component: Bank, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.BANK + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/bank/add", meta: { title: 'Bank Add - FuelMatix' }, name: "BankAdd", component: BankAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.BANK + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/bank/edit/:id", meta: { title: 'Bank Edit - FuelMatix' }, name: "BankEdit", component: BankEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.BANK + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/vendors", meta: { title: 'Vendor - FuelMatix' }, name: "Vendor", component: Vendor, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.VENDOR + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/vendor/add", meta: { title: 'Vendor Add - FuelMatix' }, name: "VendorAdd", component: VendorAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.VENDOR + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/vendor/edit/:id", meta: { title: 'Vendor Edit - FuelMatix' }, name: "VendorEdit", component: VendorEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.VENDOR + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/pay/order", meta: { title: 'Pay Order - FuelMatix' }, name: "PayOrder", component: PayOrder, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.PAY_ORDER + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/pay/order/add", meta: { title: 'Pay Order Add - FuelMatix' }, name: "PayOrderAdd", component: PayOrderAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.PAY_ORDER + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/pay/order/edit/:id", meta: { title: 'Pay Order Edit - FuelMatix' }, name: "PayOrderEdit", component: PayOrderEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.PAY_ORDER + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/credit/company", meta: { title: 'Credit Company - FuelMatix' }, name: "CreditCompany", component: CreditCompany, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.CREDIT_COMPANY + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/credit/company/add", meta: { title: 'Credit Company Add - FuelMatix' }, name: "CreditCompanyAdd", component: CreditCompanyAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.CREDIT_COMPANY + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/credit/company/edit/:id", meta: { title: 'Credit Company Edit - FuelMatix' }, name: "CreditCompanyEdit", component: CreditCompanyEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.CREDIT_COMPANY + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/pos/machine", meta: { title: 'POS Machine - FuelMatix' }, name: "posMachine", component: posMachine, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.POS_MACHINE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/pos/machine/add", meta: { title: 'POS Machine Add - FuelMatix' }, name: "posMachineAdd", component: posMachineAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.POS_MACHINE + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/pos/machine/edit/:id", meta: { title: 'POS Machine Edit - FuelMatix' }, name: "posMachineEdit", component: posMachineEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.POS_MACHINE + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/employee", meta: { title: 'Employee - FuelMatix' }, name: "employee", component: employee, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.EMPLOYEE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/employee/add", meta: { title: 'Employee Add - FuelMatix' }, name: "employeeAdd", component: employeeAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.EMPLOYEE + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/employee/edit/:id", meta: { title: 'Employee Edit - FuelMatix' }, name: "employeeEdit", component: employeeEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.EMPLOYEE + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/salary", meta: { title: 'Salary - FuelMatix' }, name: "salary", component: salary, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.SALARY + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/salary/add", meta: { title: 'Salary Add - FuelMatix' }, name: "salaryAdd", component: salaryAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.SALARY + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/salary/edit/:id", meta: { title: 'Salary Edit - FuelMatix' }, name: "salaryEdit", component: salaryEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.SALARY + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/company/sale", meta: { title: 'Company Sale - FuelMatix' }, name: "CompanySale", component: CompanySale, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.COMPANY_SALE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/invoices", meta: { title: 'Invoice - FuelMatix' }, name: "Invoices", component: Invoice, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.INVOICE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/invoices/view/:id", meta: { title: 'Invoice View - FuelMatix' }, name: "InvoicesView", component: InvoiceView, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.INVOICE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/daily/report", meta: { title: 'Daily Report - FuelMatix' }, name: "dailyReport", component: dailyReport,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.DAILY_REPORT + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/user", meta: { title: 'User - FuelMatix' }, name: "user", component: user,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.USER + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/user/add", meta: { title: 'User Add - FuelMatix' }, name: "userAdd", component: userAdd,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.USER + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/user/edit/:id", meta: { title: 'User Edit - FuelMatix' }, name: "userEdit", component: userEdit,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.USER + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/balanceTransfer", meta: { title: 'Balance Transfer - FuelMatix' }, name: "balanceTransfer", component: balanceTransfer,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TRANSFER + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/balanceTransfer/add", meta: { title: 'Balance Transfer Edit - FuelMatix' }, name: "balanceTransferAdd", component: balanceTransferAdd,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TRANSFER + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/balanceTransfer/edit/:id", meta: { title: 'Balance Transfer Edit - FuelMatix' }, name: "balanceTransferEdit", component: balanceTransferEdit,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TRANSFER + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/system/setup", meta: { title: 'System Setup - FuelMatix' }, name: "system", component: system,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.SYSTEM_SETTING + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/voucher", meta: { title: 'Voucher - FuelMatix' }, name: "voucher", component: Voucher,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.VOUCHER + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/driver", meta: { title: 'Driver - FuelMatix' }, name: "driver", component: Driver,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.DRIVER + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/role", meta: { title: 'Role - FuelMatix' }, name: "role", component: Role,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.ROLE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/role/create", meta: { title: 'Role Add - FuelMatix' }, name: "createRole", component: createRole,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.ROLE + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/role/edit/:id", meta: { title: 'Role Edit - FuelMatix' }, name: "roleEdit", component: roleEdit,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.ROLE + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/fuel/adjustment/add", meta: { title: 'Fuel Adjustment Add - FuelMatix' }, name: "fuelAdjustment", component: fuelAdjustment,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.FUEL_ADJUSTMENT + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/fuel/adjustment/list", meta: { title: 'Fuel Adjustment - FuelMatix' }, name: "adjustment", component: fuelAdjustmentList,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.FUEL_ADJUSTMENT + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/fuel/adjustment/view/:id", meta: { title: 'Fuel Adjustment View - FuelMatix' }, name: "adjustmentView", component: fuelAdjustmentView, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.FUEL_ADJUSTMENT + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/unauthorizedBill", meta: { title: 'Unauthorized Bill - FuelMatix' }, name: "unauthorizedBill", component: UnauthorizedBill,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.UNAUTHORIZED_BILL + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/sales/report", meta: { title: 'Sales Report - FuelMatix' }, name: "salesReport", component: salesReport,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.SALES_REPORT + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/company/bills", meta: { title: 'Company Bill - FuelMatix' }, name: "CompanyBills", component: companyBills,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.COMPANY_BILL + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/invoice/payment/list", meta: { title: 'Invoice Payment - FuelMatix' }, name: "InvoicePayment", component: InvoicePayment,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.INVOICE_PAYMENT + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/cars", meta: { title: 'Car - FuelMatix' }, name: "car", component: car, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.CAR + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/purchase", meta: { title: 'Purchase - FuelMatix' }, name: "purchase", component: purchase,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.PURCHASE + '-' + Action.VIEW)
                    }
                },
                {
                    path: ROOT_URL + "/purchase/add", meta: { title: 'Purchase Add - FuelMatix' }, name: "purchaseAdd", component: purchaseAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.PURCHASE + '-' + Action.CREATE)
                    }
                },
                {
                    path: ROOT_URL + "/purchase/edit", meta: { title: 'Purchase Edit - FuelMatix' }, name: "purchaseEdit", component: purchaseEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.PURCHASE + '-' + Action.EDIT)
                    }
                },
                {
                    path: ROOT_URL + "/report/stockSummary", meta: { title: 'Stock Summary - FuelMatix' }, name: "StockSummary", component: StockSummary, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.SALES_STOCK + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/report/vendor", meta: { title: 'Vendor Report - FuelMatix' }, name: "VendorReport", component: VendorReport, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.VENDOR_REPORT + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/report/expense", meta: { title: 'Expense Report - FuelMatix' }, name: "ExpenseReport", component: ExpenseReport,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.EXPENSE_REPORT + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/report/windfall", meta: { title: 'Windfall Report - FuelMatix' }, name: "WindfallReport", component: WindfallReport,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.WINDFALL_REPORT + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/report/creditCompany", meta: { title: 'Credit Company Report - FuelMatix' }, name: "CreditCompanyReport", component: CreditCompanyReport,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.CREDIT_COMPANY_REPORT + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/report/driver", meta: { title: 'Driver Report - FuelMatix' }, name: "DriverReport", component: DriverReport,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.DRIVER_REPORT + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/report/pos", meta: { title: 'POS Report - FuelMatix' }, name: "PosReport", component: PosReport,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.POS_REPORT + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/bulkSale/add", meta: { title: 'Bulk Sale Add - FuelMatix' }, name: "BulkSaleAdd", component: BulkSaleAdd,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.BULK_SALE + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/bulkSale", meta: { title: 'Bulk Sale - FuelMatix' }, name: "BulkSale", component: BulkSale,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.BULK_SALE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/company/bill", meta: { title: 'Company Bill - FuelMatix' }, name: "CompanySummary", component: CompanySummary,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.BILL_SUMMARY + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/company/bill/:id/:start_date/:end_date", meta: { title: 'Company Bill - FuelMatix' }, name: "CompanyBillDetails", component: CompanyBillDetails
                },
                {
                    path: ROOT_URL + "/dummySale/create", meta: { title: 'Dummy Sale Add - FuelMatix' }, name: "DummySaleCreate", component: DummySaleCreate,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.DUMMY_SALE + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/dummySale/list", meta: { title: 'Dummy Sale - FuelMatix' }, name: "DummySaleList", component: DummySaleList,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.DUMMY_SALE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/dummySale/:id", meta: { title: 'Dummy Sale View - FuelMatix' }, name: "DummySaleView", component: DummySaleView,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.DUMMY_SALE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/staff-loan/list", meta: { title: 'Staff Loan View - FuelMatix' }, name: "StaffLoanList", component: StaffLoanList, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.STAFF_LOAN + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/staff-loan/add", meta: { title: 'Staff Loan Add - FuelMatix' }, name: "StaffLoanAdd", component: StaffLoanAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.STAFF_LOAN + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/staff-loan/view/:id", meta: { title: 'Staff Loan View - FuelMatix' }, name: "StaffLoanView", component: StaffLoanView, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.STAFF_LOAN + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/challan", meta: { title: 'Challan - FuelMatix' }, name: "challanList", component: ChallanList, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.CHALLAN + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/challan/add", meta: { title: 'Challan - FuelMatix' }, name: "challanAdd", component: ChallanAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.CHALLAN + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/loan-entity/list", meta: { title: 'Entity List - FuelMatix' }, name: "LoanEntityList", component: LoanEntityList, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.STAFF_LOAN + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/loan-entity/add", meta: { title: 'Entity Add - FuelMatix' }, name: "LoanEntityAdd", component: LoanEntityAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.STAFF_LOAN + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/loan-entity/edit/:id", meta: { title: 'Entity Edit - FuelMatix' }, name: "LoanEntityEdit", component: LoanEntityEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.STAFF_LOAN + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/company-loan/list", meta: { title: 'Company Loan List - FuelMatix' }, name: "CompanyLoanList", component: CompanyLoanList, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.STAFF_LOAN + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/company-loan/add", meta: { title: 'Company Loan Add - FuelMatix' }, name: "CompanyLoanAdd", component: CompanyLoanAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.STAFF_LOAN + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/company-loan/view/:id", meta: { title: 'Company Loan View - FuelMatix' }, name: "CompanyLoanView", component: CompanyLoanView, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.STAFF_LOAN + '-' + Action.CREATE)
                    },
                },
            ],
        },
    ],
});
function authCheck(to, from, next) {
    // Fetch token from Vuex or localStorage
    const token = store?.getters?.GetAccessToken || localStorage.getItem('FuelMatixAccessToken');

    if (token) {
        if (to.path === '/auth/login') {
            return next('/dashboard'); // Redirect logged-in user to dashboard
        }
    }
    next(); // Proceed to intended route
}

function authRequestCheck(to, from, next) {
    // Fetch token from Vuex or localStorage
    const token = store?.getters?.GetAccessToken || localStorage.getItem('FuelMatixAccessToken');

    if (!token) {
        return next('/auth/login'); // Redirect to login if no token
    }

    if (to.path === '/' || to.path === '/auth/login') {
        return next('/dashboard'); // Redirect logged-in user to dashboard
    }

    next(); // Allow navigation to other paths
}

function CheckPermission(to, from, next, sectionName) {
    let auth = JSON.parse(localStorage.getItem('userInfo'));
    let permission = auth.permission ?? [];
    if (permission.includes(sectionName)) {
        next();
    } else {
        next('/dashboard');
    }
}
router.beforeEach((to, from, next) => {
    document.title = to.meta.title || 'FuelMatix';
    next();
});
import Section from "../Helpers/Section";
import Action from "../Helpers/Action";
export default router;
