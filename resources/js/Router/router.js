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
            ],
        },
    ],
});


export default router;
