<template>
    <div class="dlabnav sidenav">
        <div class="dlabnav-scroll">
            <ul class="metismenu" id="menu">
                <li>
                    <router-link :to="{name: 'Dashboard'}" aria-expanded="false"  @click.native="LoadLoader()">
                        <i class="fas fa-home"></i>
                        <span class="nav-text">Dashboard</span>
                    </router-link>
                </li>

                <li><a class="has-arrow " href="javascript:void(0)" aria-expanded="false">
                    <i class="fa-solid fa-cart-shopping icon-style" aria-hidden="true"></i>
                    <span class="nav-text">Sales</span>
                </a>
                    <ul aria-expanded="false" class="bichi">
                        <li><router-link :to="{name: 'ShiftSaleListStart'}">Start Shift Sale</router-link></li>
                        <li><router-link :to="{name: 'ShiftSaleList'}">End Shift Sale</router-link></li>
                        <li><router-link :to="{name: 'Pos'}" >POS</router-link></li>
                        <li><router-link :to="{name: 'PosList'}" >POS History</router-link></li>
                        <li><router-link :to="{name: 'CompanySale'}">Company Sale </router-link></li>
                    </ul>
                </li>
                <li><a class="has-arrow " href="javascript:void(0)" aria-expanded="false">
                    <i class="fa-solid fa-gas-pump"></i>
                    <span class="nav-text">Refill</span>
                </a>
                    <ul aria-expanded="false" class="bichi">
                        <li><router-link :to="{name: 'TankRefill'}">Tank Refill</router-link></li>
                        <li><router-link :to="{name: 'TankVisual'}"> Tank Visual</router-link></li>
                    </ul>
                </li>
                <li><a class="has-arrow " href="javascript:void(0)" aria-expanded="false">
                    <i class="fa-solid fa-ruler"></i>
                    <span class="nav-text">Reading</span>
                </a>
                    <ul aria-expanded="false" class="bichi">
                        <li><router-link :to="{name: 'TankReading'}">Tank</router-link></li>
                        <li><router-link :to="{name: 'NozzleReading'}">Nozzle</router-link></li>
                    </ul>
                </li>
                <li><a class="has-arrow " href="javascript:void(0)" aria-expanded="false">
                    <i class="fa-regular fa-money-bill-1"></i>
                    <span class="nav-text">Payment</span>
                </a>
                    <ul aria-expanded="false" class="bichi">
                        <li><router-link :to="{name: 'PayOrder'}">Pay Orders</router-link></li>
                        <li><a href="javascript:void(0)">Transfer</a></li>
                        <li><router-link :to="{name:'Expense'}">Expense</router-link></li>
                        <li><router-link :to="{name: 'Invoices'}">Bills </router-link></li>
                    </ul>

                </li>
                <li><a class="has-arrow " href="javascript:void(0)" aria-expanded="false">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">HR</span>
                </a>
                    <ul aria-expanded="false" class="bichi">
                        <li><router-link :to="{name: 'employee'}">Employees </router-link></li>
                        <li><router-link :to="{name: 'salary'}">Salary </router-link></li>
                        <li><a href="javascript:void(0)">Attendance</a></li>
                    </ul>

                </li>
                <li><a class="has-arrow " href="javascript:void(0)" aria-expanded="false">
                    <i class="fa-solid fa-gear"></i>
                    <span class="nav-text">Setup</span>
                </a>
                    <ul aria-expanded="false" class="bichi">
                        <li><router-link :to="{name: 'Tank'}"> Tank</router-link></li>
                        <li><router-link :to="{name: 'Dispenser'}">Dispenser</router-link></li>
                        <li><router-link :to="{name: 'Nozzle'}">Nozzle</router-link></li>
                        <li><router-link :to="{name: 'Product'}">Product</router-link></li>
                        <li><router-link :to="{name: 'Bank'}">Banks </router-link></li>
                        <li><router-link :to="{name: 'Vendor'}">Vendors</router-link></li>
                        <li><router-link :to="{name: 'CreditCompany'}">Credit Company </router-link></li>
                        <li><router-link :to="{name: 'posMachine'}">POS</router-link></li>
                        <li><router-link :to="{name: 'user'}">Users</router-link></li>
                    </ul>
                </li>
                <li>
                    <router-link :to="{name: 'Accounts'}"  aria-expanded="false">
                        <i class="fas fa-dollar-sign"></i>
                        <span class="nav-text">Accounting</span>
                    </router-link>
                </li>
                <li>
                    <a class="has-arrow " href="javascript:void(0)" aria-expanded="false">
                        <i class="fa-solid fa-chart-line"></i>
                        <span class="nav-text">Report</span>
                    </a>
                    <ul aria-expanded="false" class="bichi">
                        <li>
                            <router-link :to="{name: 'dailyReport'}" >Daily Report
                            </router-link>
                        </li>
                        <li>
                            <router-link :to="{name: 'BalanceSheet'}" >Balance
                                Sheet
                            </router-link>
                        </li>
                        <li>
                            <router-link :to="{name: 'ProfitLoss'}" >Profit and
                                loss
                            </router-link>
                        </li>
                        <li>
                            <router-link :to="{name: 'IncomeStatement'}" >Income
                                Statement
                            </router-link>
                        </li>
                        <li>
                            <router-link :to="{name: 'AccountPayable'}" >A/C Payable
                            </router-link>
                        </li>
                        <li>
                            <router-link :to="{name: 'AccountReceivable'}" >A/C Receivable
                            </router-link>
                        </li>
                        <li>
                            <router-link :to="{name: 'TrailBalance'}" >Trial Balance
                            </router-link>
                        </li>
                        <li>
                            <router-link :to="{name: 'LedgerSheet'}" >Ledger Sheet
                            </router-link>
                        </li>
                    </ul>
                </li>

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
