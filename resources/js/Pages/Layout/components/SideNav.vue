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
                    <ul aria-expanded="false">
                        <li><router-link :to="{name: 'ShiftSaleStart'}">Shift Sale</router-link></li>
                        <li><a href="./sale.html">Sale</a></li>
                        <li><a href="./salesHistroy.html">Sale Histroy</a></li>
                        <li><a href="#">Item Sale</a></li>
                        <li><a href="#">Bill</a></li>
                        <li><a href="./creditSaleDetailEntry.html">Credit Sale Detail Entry</a></li>
                        <li><router-link :to="{name: 'Pos'}" >POS</router-link></li>
                    </ul>
                </li>
                <li><a class="has-arrow " href="javascript:void()" aria-expanded="false">
                    <i class="fa-solid fa-gas-pump"></i>
                    <span class="nav-text">Fule</span>
                </a>
                    <ul aria-expanded="false">
                        <li><a href="./tankRefill.html">Refill</a></li>
                        <li><a href="./tankRefillHistory.html">Tank Refill History</a></li>
                        <li><a href="./fuelRefillVoucher.html">Fuel Refill Voucher</a></li>
                        <li><a href="./refillHistroyDetails.html">Refill Histroy Details</a></li>
                        <li><router-link :to="{name: 'Dispenser'}">Dispenser</router-link></li>
                        <li><router-link :to="{name: 'DispenserReading'}">Dispenser Reading</router-link></li>
                        <li><router-link :to="{name: 'Nozzle'}">Nozzle</router-link></li>
                        <li><router-link :to="{name: 'NozzleReading'}">Nozzle Reading</router-link></li>
                        <li><router-link :to="{name: 'Tank'}"> Tank</router-link></li>
                        <li><router-link :to="{name: 'TankVisual'}"> Tank Visual</router-link></li>
                        <li><router-link :to="{name: 'TankReading'}">Tank Reading</router-link></li>
                        <li><router-link :to="{name: 'Product'}">Product</router-link></li>
                    </ul>
                </li>
                <li><a class="has-arrow " href="javascript:void()" aria-expanded="false">
                    <i class="fa-regular fa-money-bill-1"></i>
                    <span class="nav-text">Payment</span>
                </a>
                    <ul aria-expanded="false">
                        <li><a href="./#">Vendors</a></li>
                    </ul>
                </li>
                <li><a href="widget-basic.html" class="" aria-expanded="false">
                    <i class="fas fa-user-check"></i>
                    <span class="nav-text">Widget</span>
                </a>
                </li>
                <li><a class="has-arrow " href="javascript:void()" aria-expanded="false">
                    <i class="fa-solid fa-building-columns"></i>
                    <span class="nav-text">Banks</span>
                </a>
                    <ul aria-expanded="false">
                        <li><a href="./#">Bnaks </a></li>
                        <li><a href="./#">Personal Accounts</a></li>
                        <li><a href="./#">Transfer</a></li>
                        <li><a href="./#">Transaction</a></li>
                    </ul>
                </li>
                <li><a class="has-arrow " href="javascript:void()" aria-expanded="false">
                    <i class="	fas fa-users"></i>
                    <span class="nav-text">Field</span>
                </a>
                    <ul aria-expanded="false">
                        <li><a href="./Field1.html">Field-1</a></li>
                        <li><a href="./Field217.html">Field-217</a></li>
                        <li><a href="./card.html">Shopping caed</a></li>
                        <li><a href="./cashierBalance.html">Cashier balance</a></li>
                        <li><a href="./reading.html">Reading</a></li>
                        <li><a href="./createHeads.html">Create Heads</a></li>
                        <li><a href="./#">Transaction</a></li>
                    </ul>
                </li>
                <li><a class="has-arrow " href="javascript:void()" aria-expanded="false">
                    <i class="fa-solid fa-money-bill"></i>
                    <span class="nav-text">Expenses</span>
                </a>
                    <ul aria-expanded="false">
                        <li><router-link :to="{name:'Expense'}">Expense</router-link></li>
                    </ul>
                </li>
                <li><a class="has-arrow " href="javascript:void()" aria-expanded="false">
                    <i class="	fas fa-users"></i>
                    <span class="nav-text">Setup</span>
                </a>
                    <ul aria-expanded="false">
                        <li><a href="./tank&NozzleSetup.html">Pump & Nozzle Setup</a></li>
                        <li><a href="./tank.html">Tank Setup</a></li>
                        <li><a href="./creditCompanySetup.html">Credit Company Setup </a></li>
                        <li><a href="./posSetup.html">Pos Setup</a></li>
                        <li><a href="./#">Products</a></li>
                        <li><a href="./#">Sekkubg Price</a></li>
                    </ul>
                </li>
                <li><a class="has-arrow " href="javascript:void()" aria-expanded="false">
                    <i class="	fas fa-users"></i>
                    <span class="nav-text">Report</span>
                </a>
                    <ul aria-expanded="false">
                        <li><a href="./#">General</a></li>
                        <li><a href="./#">Income Statement</a></li>
                        <li><a href="./#">Porfit & Loss</a></li>
                        <li><a href="./#">A/C Receivable</a></li>
                        <li><a href="./#">A/C Payable</a></li>
                    </ul>
                </li>
                <li><a class="has-arrow " href="javascript:void(0)" aria-expanded="false">
                    <i class="fas fa-dollar-sign"></i>
                    <span class="nav-text">Accounting</span>
                </a>
                    <ul aria-expanded="false">
                        <li>
                            <router-link :to="{name: 'Accounts'}" >Accounting
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
                            <router-link :to="{name: 'AccountPayable'}" >Account
                                Payable
                            </router-link>
                        </li>
                        <li>
                            <router-link :to="{name: 'AccountReceivable'}" >Account
                                Receivable
                            </router-link>
                        </li>
                        <li>
                            <router-link :to="{name: 'TrailBalance'}" >Trail
                                balance
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
