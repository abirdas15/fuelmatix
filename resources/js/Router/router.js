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
import Pos from "../Pages/Pos/Pos";
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
                { path: ROOT_URL + "/accounts", name: "Accounts", component: Category},
                { path: ROOT_URL + "/transaction/:id", name: "Transaction", component: Transaction},
                { path: ROOT_URL + "/balance-sheet", name: "BalanceSheet", component: BalanceSheet},
                { path: ROOT_URL + "/profit-loss", name: "ProfitLoss", component: ProfitLoss},
                { path: ROOT_URL + "/income-statement", name: "IncomeStatement", component: IncomeStatement},
                { path: ROOT_URL + "/account-payable", name: "AccountPayable", component: AccountPayable},
                { path: ROOT_URL + "/account-receivable", name: "AccountReceivable", component: AccountReceivable},
                { path: ROOT_URL + "/trial-balance", name: "TrailBalance", component: TrailBalance},
                { path: ROOT_URL + "/ledger-sheet", name: "LedgerSheet", component: LedgerSheet},
                { path: ROOT_URL + "/product", name: "Product", component: Product},
                { path: ROOT_URL + "/product/add", name: "ProductAdd", component: ProductAdd},
                { path: ROOT_URL + "/product/edit/:id", name: "ProductEdit", component: ProductEdit},
                { path: ROOT_URL + "/dispenser", name: "Dispenser", component: Dispenser},
                { path: ROOT_URL + "/dispenser/add", name: "DispenserAdd", component: DispenserAdd},
                { path: ROOT_URL + "/dispenser/edit/:id", name: "DispenserEdit", component: DispenserEdit},
                { path: ROOT_URL + "/dispenser/reading", name: "DispenserReading", component: DispenserReading},
                { path: ROOT_URL + "/dispenser/reading/add", name: "DispenserReadingAdd", component: DispenserReadingAdd},
                { path: ROOT_URL + "/dispenser/reading/edit/:id", name: "DispenserReadingEdit", component: DispenserReadingEdit},
                { path: ROOT_URL + "/nozzle", name: "Nozzle", component: Nozzle},
                { path: ROOT_URL + "/nozzle/add", name: "NozzleAdd", component: NozzleAdd},
                { path: ROOT_URL + "/nozzle/edit/:id", name: "NozzleEdit", component: NozzleEdit},
                { path: ROOT_URL + "/nozzle/reading", name: "NozzleReading", component: NozzleReading},
                { path: ROOT_URL + "/nozzle/reading/add", name: "NozzleReadingAdd", component: NozzleReadingAdd},
                { path: ROOT_URL + "/nozzle/reading/edit/:id", name: "NozzleReadingEdit", component: NozzleReadingEdit},
                { path: ROOT_URL + "/shift/sale/start", name: "ShiftSaleStart", component: ShiftSaleStart},
                { path: ROOT_URL + "/pos", name: "Pos", component: Pos},
                { path: ROOT_URL + "/expense", name: "Expense", component: Expense},
                { path: ROOT_URL + "/expense/add", name: "ExpenseAdd", component: ExpenseAdd},
                { path: ROOT_URL + "/expense/edit/:id", name: "ExpenseEdit", component: ExpenseEdit},
                { path: ROOT_URL + "/tank", name: "Tank", component: Tank},
                { path: ROOT_URL + "/tank/visual", name: "TankVisual", component: TankVisual},
                { path: ROOT_URL + "/tank/add", name: "TankAdd", component: TankAdd},
                { path: ROOT_URL + "/tank/edit/:id", name: "TankEdit", component: TankEdit},
                { path: ROOT_URL + "/tank/reading", name: "TankReading", component: TankReading},
                { path: ROOT_URL + "/tank/reading/add", name: "TankReadingAdd", component: TankReadingAdd},
                { path: ROOT_URL + "/tank/reading/edit/:id", name: "TankReadingEdit", component: TankReadingEdit},
            ],
        },
    ],
});


export default router;
