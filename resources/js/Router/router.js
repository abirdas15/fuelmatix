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
import DispenserReading from "../Pages/Fuel/DispenserReading/List";
import DispenserReadingAdd from "../Pages/Fuel/DispenserReading/Add";
import DispenserReadingEdit from "../Pages/Fuel/DispenserReading/Edit";
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
import Pos from "../Pages/Pos/Pos";
import PosList from "../Pages/Pos/List.vue";
import PosEdit from "../Pages/Pos/Edit.vue";
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
import UnauthorizedBill from "../Pages/UnauthorizedBill/UnauthorizedBill.vue";
import salesReport from "../Pages/SalesReport/List.vue";

const ROOT_URL = "";
const router = new VueRouter({
    scrollBehavior() {
        return { x: 0, y: 0 };
    },
    mode: "history",
    routes: [
        { path: ROOT_URL + "/auth/login", name: "Login", component: Login},
        {
            path: ROOT_URL + "/",
            name: "Layout",
            component: Layout,
            children: [
                { path: ROOT_URL + "/dashboard", name: "Dashboard", component: Dashboard},
                {
                    path: ROOT_URL + "/accounts", name: "Accounts", component: Category,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.ACCOUNTING + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/transaction/:id", name: "Transaction", component: Transaction,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.ACCOUNTING + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/balance-sheet", name: "BalanceSheet", component: BalanceSheet, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.BALANCE_SHEET + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/profit-loss", name: "ProfitLoss", component: ProfitLoss, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.PROFIT_AND_LOSS + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/income-statement", name: "IncomeStatement", component: IncomeStatement, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.INCOME_STATEMENT + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/account-payable", name: "AccountPayable", component: AccountPayable, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.ACCOUNT_PAYABLE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/account-receivable", name: "AccountReceivable", component: AccountReceivable, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.ACCOUNT_RECEIVABLE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/trial-balance", name: "TrailBalance", component: TrailBalance, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TRAIL_BALANCE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/ledger-sheet", name: "LedgerSheet", component: LedgerSheet, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.LEDGER + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/product", name: "Product", component: Product, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.PRODUCT + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/product/add", name: "ProductAdd", component: ProductAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.PRODUCT + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/product/edit/:id", name: "ProductEdit", component: ProductEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.PRODUCT + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/dispenser", name: "Dispenser", component: Dispenser, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.DISPENSER + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/dispenser/add", name: "DispenserAdd", component: DispenserAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.DISPENSER + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/dispenser/edit/:id", name: "DispenserEdit", component: DispenserEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.DISPENSER + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/nozzle", name: "Nozzle", component: Nozzle, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.NOZZLE_READING + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/nozzle/add", name: "NozzleAdd", component: NozzleAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.NOZZLE_READING + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/nozzle/edit/:id", name: "NozzleEdit", component: NozzleEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.NOZZLE_READING + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/nozzle/reading", name: "NozzleReading", component: NozzleReading, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.NOZZLE_READING + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/nozzle/reading/add", name: "NozzleReadingAdd", component: NozzleReadingAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.NOZZLE_READING + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/nozzle/reading/edit/:id", name: "NozzleReadingEdit", component: NozzleReadingEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.NOZZLE_READING + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/shift/sale/start", name: "ShiftSaleAdd", component: ShiftSaleStart,  beforeEnter: (to, from, next) => {
                    CheckPermission(to, from, next, Section.SHIFT_SALE + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/shift/sale/list", name: "ShiftSaleList", component: ShiftSaleList, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.SHIFT_SALE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/shift/sale/list/start", name: "ShiftSaleListStart", component: ShiftSaleListStart, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.SHIFT_SALE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/shift/sale/edit/:id", name: "ShiftSaleEdit", component: ShiftSaleEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.SHIFT_SALE + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/pos", name: "Pos", component: Pos, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.POS + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/pos/list", name: "PosList", component: PosList, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.POS + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/pos/edit/:id", name: "PosEdit", component: PosEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.POS + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/expense", name: "Expense", component: Expense, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.EXPENSE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/expense/add", name: "ExpenseAdd", component: ExpenseAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.EXPENSE + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/expense/edit/:id", name: "ExpenseEdit", component: ExpenseEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.EXPENSE + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/tank", name: "Tank", component: Tank, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/tank/visual", name: "TankVisual", component: TankVisual, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK_VISUAL + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/tank/add", name: "TankAdd", component: TankAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/tank/edit/:id", name: "TankEdit", component: TankEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/tank/reading", name: "TankReading", component: TankReading, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK_READING + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/tank/reading/add", name: "TankReadingAdd", component: TankReadingAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK_READING + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/tank/reading/edit/:id", name: "TankReadingEdit", component: TankReadingEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK_READING + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/tank/refill", name: "TankRefill", component: TankRefill, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK_REFILL + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/tank/refill/add", name: "TankRefillAdd", component: TankRefillAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK_REFILL + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/tank/refill/edit/:id", name: "TankRefillEdit", component: TankRefillEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TANK_REFILL + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/bank", name: "Bank", component: Bank, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.BANK + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/bank/add", name: "BankAdd", component: BankAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.BANK + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/bank/edit/:id", name: "BankEdit", component: BankEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.BANK + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/vendor", name: "Vendor", component: Vendor, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.VENDOR + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/vendor/add", name: "VendorAdd", component: VendorAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.VENDOR + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/vendor/edit/:id", name: "VendorEdit", component: VendorEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.VENDOR + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/pay/order", name: "PayOrder", component: PayOrder, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.PAY_ORDER + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/pay/order/add", name: "PayOrderAdd", component: PayOrderAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.PAY_ORDER + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/pay/order/edit/:id", name: "PayOrderEdit", component: PayOrderEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.PAY_ORDER + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/credit/company", name: "CreditCompany", component: CreditCompany, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.CREDIT_COMPANY + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/credit/company/add", name: "CreditCompanyAdd", component: CreditCompanyAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.CREDIT_COMPANY + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/credit/company/edit/:id", name: "CreditCompanyEdit", component: CreditCompanyEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.CREDIT_COMPANY + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/pos/machine", name: "posMachine", component: posMachine, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.POS_MACHINE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/pos/machine/add", name: "posMachineAdd", component: posMachineAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.POS_MACHINE + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/pos/machine/edit/:id", name: "posMachineEdit", component: posMachineEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.POS_MACHINE + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/employee", name: "employee", component: employee, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.EMPLOYEE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/employee/add", name: "employeeAdd", component: employeeAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.EMPLOYEE + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/employee/edit/:id", name: "employeeEdit", component: employeeEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.EMPLOYEE + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/salary", name: "salary", component: salary, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.SALARY + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/salary/add", name: "salaryAdd", component: salaryAdd, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.SALARY + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/salary/edit/:id", name: "salaryEdit", component: salaryEdit, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.SALARY + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/company/sale", name: "CompanySale", component: CompanySale, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.COMPANY_SALE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/invoices", name: "Invoices", component: Invoice, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.INVOICE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/invoices/view/:id", name: "InvoicesView", component: InvoiceView, beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.INVOICE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/daily/report", name: "dailyReport", component: dailyReport,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.DAILY_REPORT + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/user", name: "user", component: user,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.USER + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/user/add", name: "userAdd", component: userAdd,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.USER + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/user/edit/:id", name: "userEdit", component: userEdit,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.USER + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/balanceTransfer", name: "balanceTransfer", component: balanceTransfer,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TRANSFER + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/balanceTransfer/add", name: "balanceTransferAdd", component: balanceTransferAdd,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TRANSFER + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/balanceTransfer/edit/:id", name: "balanceTransferEdit", component: balanceTransferEdit,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.TRANSFER + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/system/setup", name: "system", component: system,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.SYSTEM_SETTING + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/voucher", name: "voucher", component: Voucher,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.VOUCHER + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/driver", name: "driver", component: Driver,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.DRIVER + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/role", name: "role", component: Role,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.ROLE + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/role/create", name: "createRole", component: createRole,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.ROLE + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/role/edit/:id", name: "roleEdit", component: roleEdit,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.ROLE + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/fuel/adjustment/add", name: "fuelAdjustment", component: fuelAdjustment,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.FUEL_ADJUSTMENT + '-' + Action.CREATE)
                    },
                },
                {
                    path: ROOT_URL + "/fuel/adjustment/list", name: "adjustment", component: fuelAdjustmentList,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.FUEL_ADJUSTMENT + '-' + Action.VIEW)
                    },
                },
                {
                    path: ROOT_URL + "/unauthorizedBill", name: "unauthorizedBill", component: UnauthorizedBill,  beforeEnter: (to, from, next) => {
                        CheckPermission(to, from, next, Section.UNAUTHORIZED_BILL + '-' + Action.EDIT)
                    },
                },
                {
                    path: ROOT_URL + "/sales/report", name: "salesReport", component: salesReport
                }
            ],
        },
    ],
});
function CheckPermission(to, from, next, sectionName) {
    let auth = JSON.parse(localStorage.getItem('userInfo'));
    let permission = auth.permission ?? [];
    if (permission.includes(sectionName)) {
        next();
    } else {
        next('/dashboard');
    }
}
import Section from "../Helpers/Section";
import Action from "../Helpers/Action";
export default router;
