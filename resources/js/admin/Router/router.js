import Vue from "vue";
import VueRouter from "vue-router";
import Vuex from "vuex";

Vue.use(Vuex);

import Login from "../Pages/Auth/Login.vue";
import Layout from "../Pages/Layouts/Layout.vue";
import Dashboard from "../Pages/Dashboard/Dashboard.vue";
import Company from "../Pages/Company/Compnay.vue";
import CompanyCreate from "../Pages/Company/Create.vue";
import CompanyEdit from "../Pages/Company/Edit.vue";
import User from "../Pages/User/User.vue";
import UserEdit from "../Pages/User/Edit.vue";



const ROOT_URL = "/admin";
const router = new VueRouter({
    mode: "history",
    routes: [
        { path: ROOT_URL + "/auth/login", name: "Login", component: Login},
        {
            path: ROOT_URL + '/',
            name: 'Layout', component: Layout,
            children: [
                {path: ROOT_URL + '/dashboard', name: 'Dashboard', component: Dashboard},
                {path: ROOT_URL + '/company', name: 'Company', component: Company},
                {path: ROOT_URL + '/company/create', name: 'CompanyCreate', component: CompanyCreate},
                {path: ROOT_URL + '/company/edit/:id', name: 'CompanyEdit', component: CompanyEdit},
                {path: ROOT_URL + '/user', name: 'User', component: User},
                {path: ROOT_URL + '/user/edit/:id', name: 'UserEdit', component: UserEdit},
            ]
        }
    ],
});
export default router;
