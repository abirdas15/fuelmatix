<template>
    <div class="dlabnav">
        <div class="dlabnav-scroll">
            <ul class="metismenu" id="menu">
                <li style="text-align: center;">
                    <router-link :to="{name: 'Dashboard'}" class="sidemenu-item" aria-expanded="false"  @click.native="LoadLoader()">
                        <i class="fas fa-home me-0 icon-style"></i>
                        <p class="nav-text">Dashboard</p>
                    </router-link>
                </li>

                <li style="text-align: center;">
                    <a  class="has-arrow sidemenu-item px-4" href="javascript:void(0)"
                       aria-expanded="false">
                        <i class="fa-solid fa-cart-shopping icon-style me-0" aria-hidden="true"></i>
                        <span class="nav-text">Sales</span>
                    </a>
                    <ul aria-expanded="false">
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./shiftSaleStart.html">Shift Sale</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./sale.html">Sale</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./salesHistroy.html">Sale Histroy</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./#">Item Sale</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./#">Bill</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./creditSaleDetailEntry.html">Credit Sale
                            Detail Entry</router-link></li>
                    </ul>
                </li>


                <li style="text-align: center;">
                    <a class="has-arrow sidemenu-item px-4" href="javascript:void(0)" aria-expanded="false">
                        <i class="fa-solid fa-gas-pump icon-style me-0" aria-hidden="true"></i>
                        <span class="nav-text ">Fule</span>
                    </a>
                    <ul aria-expanded="false">
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./tankRefill.html">Refill</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./tankRefillHistory.html">Tank Refill
                            History</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./fuelRefillVoucher.html">Fuel Refill
                            Voucher</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./refillHistroyDetails.html">Refill Histroy
                            Details</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./nozzleReading.html">Nozzle Reading</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./tankReading.html">Tank Reading</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./tankReadingHistory.html">Tank Reading
                            History</router-link></li>
                    </ul>
                </li>

                <!-- <li>
                  <router-link :to={} href="./conversations.html" aria-expanded="false">
                    <i class="fas fa-sms"></i>
                    <span class="nav-text">Conversations</span>
                  </a>
                </li> -->
                <!-- <li>
                  <router-link :to={} class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-user"></i>
                    <span class="nav-text">Customer</span>
                  </a>
                  <ul aria-expanded="false">
                    <li><router-link :to={} href="./ui-accordion.html">Add</a></li>
                    <li><router-link :to={} href="./ui-alert.html">Edit</a></li>
                  </ul>
                </li> -->
                <li style="text-align: center;">
                    <a class="has-arrow sidemenu-item px-4" href="javascript:void(0)" aria-expanded="false">
                        <i class="fa-regular fa-money-bill-1 icon-style me-0"></i>
                        <span class="nav-text">Payment </span>
                    </a>
                    <ul aria-expanded="false">
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./#">Vendors</router-link></li>
                    </ul>
                </li>

                <li style="text-align: center;">
                    <a  class="has-arrow sidemenu-item px-4" href="javascript:void(0)" aria-expanded="false">
                        <i class="fa-solid fa-building-columns icon-style me-0"></i>
                        <span class="nav-text">Banks</span>
                    </a>
                    <ul aria-expanded="false">
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./#">Bnaks </router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./#">Personal Accounts</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./#">Transfer</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./#">Transaction</router-link></li>
                    </ul>
                </li>

                <li style="text-align: center;">
                    <a class="has-arrow sidemenu-item px-4" href="javascript:void(0)" aria-expanded="false">
                        <i class="	fas fa-users icon-style me-0"></i>
                        <span class="nav-text">Field</span>
                    </a>
                    <ul aria-expanded="false">
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./Field1.html">Field-1</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./Field217.html">Field-217</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./card.html">Shopping caed</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./cashierBalance.html">Cashier balance</router-link>
                        </li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./reading.html">Reading</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./createHeads.html">Create Heads</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./#">Transaction</router-link></li>
                    </ul>
                </li>

                <li style="text-align: center;">
                    <a class="has-arrow sidemenu-item px-4" href="javascript:void(0)" aria-expanded="false">
                        <i class="fa-solid fa-money-bill icon-style me-0"></i>
                        <span class="nav-text">Expenses</span>
                    </a>
                    <ul aria-expanded="false">
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./expense.html">Expense</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./#">Salary Expense</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./#">Machine Expense</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./#">Mics Expense</router-link></li>
                    </ul>
                </li>

                <!-- <li style="text-align: center;">
                  <router-link :to={} class=" sidemenu-item px-4" aria-expanded="false">
                    <i class="fas fa-phone me-0"></i>
                    <span class="nav-text">Customers</span>
                  </a>
                </li> -->
                <li style="text-align: center;">
                    <router-link :to="{name: 'Vendor'}" class=" sidemenu-item px-4" aria-expanded="false"  @click.native="LoadLoader()">
                        <i class="fas fa-phone icon-style me-0"></i>
                        <span class="nav-text">Vendors</span>
                    </router-link>
                </li>
                <li style="text-align: center;">
                    <router-link :to="{name: 'Vendor'}" class=" sidemenu-item px-4" aria-expanded="false"  @click.native="LoadLoader()">
                        <i class="fa-solid fa-user icon-style me-0"></i>
                        <span class="nav-text">Human</span>
                    </router-link>
                </li>
                <li style="text-align: center;">
                    <router-link :to="{name: 'Vendor'}" class=" sidemenu-item px-4" aria-expanded="false"  @click.native="LoadLoader()">
                        <i class="fa-solid fa-users icon-style me-0"></i>
                        <span class="nav-text">Customers</span>
                    </router-link>
                </li>

                <li style="text-align: center;">
                    <a class="has-arrow sidemenu-item px-4" href="javascript:void(0)" aria-expanded="false">
                        <i class="	fas fa-users icon-style me-0"></i>
                        <span class="nav-text">Setup</span>
                    </a>
                    <ul aria-expanded="false">
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./tank&NozzleSetup.html">Pump & Nozzle
                            Setup</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./tank.html">Tank Setup</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./creditCompanySetup.html">Credit Company
                            Setup </router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./posSetup.html">Pos Setup</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./#">Products</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./#">Sekkubg Price</router-link></li>
                    </ul>
                </li>

                <li style="text-align: center;">
                    <a  class="has-arrow sidemenu-item px-4" href="javascript:void(0)" aria-expanded="false">
                        <i class="	fas fa-users icon-style me-0"></i>
                        <span class="nav-text">Report</span>
                    </a>
                    <ul aria-expanded="false">
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./#">General</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./#">Income Statement</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./#">Porfit & Loss</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./#">A/C Receivable</router-link></li>
                        <li><router-link :to="{name: 'Vendor'}" class="sudo-ele-none px-4 font-w500" href="./#">A/C Payable</router-link></li>
                    </ul>
                </li>

                <li style="text-align: center;">
                    <router-link :to="{name: 'Vendor'}" href="salesReport.html" class="sidemenu-item px-4" aria-expanded="false"  @click.native="LoadLoader()">
                        <i class="fas fa-user-cog icon-style me-0"></i>
                        <span class="nav-text">Sale Report</span>
                    </router-link>
                </li>
                <li style="text-align: center;">
                    <router-link :to="{name: 'Vendor'}" class="sidemenu-item px-4" aria-expanded="false"  @click.native="LoadLoader()">
                        <i class="fas fa-user-cog icon-style"></i>
                        <span class="nav-text">consumption Report</span>
                    </router-link>
                </li>
                <li style="text-align: center;">
                    <a  class="has-arrow sidemenu-item px-4" aria-expanded="false" >
                        <i class="fa-solid fa-dollar-sign icon-style"></i>
                        <span class="nav-text">Accounting</span>
                    </a>
                    <ul aria-expanded="false">
                        <li><router-link :to="{name: 'Accounts'}" class="sudo-ele-none px-4 font-w500" >Accounting</router-link></li>
                        <li><router-link :to="{name: 'BalanceSheet'}" class="sudo-ele-none px-4 font-w500" >Balance Sheet</router-link></li>
                        <li><router-link :to="{name: 'ProfitLoss'}" class="sudo-ele-none px-4 font-w500" >Profit and loss</router-link></li>
                        <li><router-link :to="{name: 'IncomeStatement'}" class="sudo-ele-none px-4 font-w500" >Income Statement</router-link></li>
                        <li><router-link :to="{name: 'AccountPayable'}" class="sudo-ele-none px-4 font-w500" >Account Payable</router-link></li>
                        <li><router-link :to="{name: 'AccountReceivable'}" class="sudo-ele-none px-4 font-w500" >Account Receivable</router-link></li>
                        <li><router-link :to="{name: 'TrailBalance'}" class="sudo-ele-none px-4 font-w500" >Trail balance</router-link></li>
                        <li><router-link :to="{name: 'LedgerSheet'}" class="sudo-ele-none px-4 font-w500" >Ledger Sheet</router-link></li>
                    </ul>
                </li>

                <!-- <li>
                  <router-link :to={} href="settings.html" aria-expanded="false">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">Settings</span>
                  </a>
                </li> -->
<!--                <div class="avatar">
                    <img src="images/avatar/1.png" class="rounded-circle user_img" alt=""/>
                </div>-->
            </ul>
        </div>
    </div>
</template>
<script>

import ApiService from "../../../Services/ApiService";
import ApiRoutes from "../../../Services/ApiRoutes";

export default {
    components: {},
    data() {
        return {
            ticketData: {}
        };
    },
    mounted() {
    },
    created() {

    },
    computed: {
        Auth: function () {
            return this.$store.getters.GetAuth;
        },
        routeMatch: function () {
            return this.$route.name;
        },
    },
    methods: {
        Logout: function () {
            ApiService.POST(ApiRoutes.Logout, {}, res => {
                this.Loading = false;
                if (parseInt(res.status) === 200) {
                    localStorage.removeItem("userInfo");
                    window.location.reload();
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
        removeActive: function (e) {
            $('#menu li a').removeClass('router-link-exact-active')
            $('#menu li').removeClass('mm-active')
        },
        LoadLoader: function () {
            $('#menu li').removeClass('mm-active')
            jQuery('#preloader').show();
            setTimeout(() => {
                jQuery('#preloader').hide();
            }, 800)
        },

    },
};
</script>
