import Vue from "vue";
import VueRouter from "vue-router";
import Vuex from "vuex";
import store from "../Store/store";

Vue.use(Vuex);

import Layout from "../Pages/Layout/Layout.vue";
import Login from "../Pages/Auth/Login";
import Dashboard from "../Pages/Dashboard/Dashboard.vue";
import Accounts from "../Pages/Accounts/Accounts";

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
                { path: ROOT_URL + "/accounts", name: "Accounts", component: Accounts},
            ],
        },
    ],
});


export default router;
