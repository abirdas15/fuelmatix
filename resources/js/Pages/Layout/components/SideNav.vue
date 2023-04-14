<template>
    <div class="dlabnav">
        <div class="dlabnav-scroll">
            <ul class="metismenu" id="menu">
                <li style="text-align: center;">
                    <a href="index.html" class="sidemenu-item" aria-expanded="false">
                        <i class="fas fa-home me-0 icon-style"></i>
                        <p class="nav-text">Dashboard</p>
                    </a>
                </li>

                <li style="text-align: center;">
                    <a class="has-arrow sidemenu-item px-4" class="sidemenu-item" href="javascript:void()" aria-expanded="false">
                        <i class="fa-solid fa-cart-shopping icon-style me-0" aria-hidden="true"></i>
                        <span class="nav-text">Sales</span>
                    </a>
                    <ul aria-expanded="false">
                        <li><a class="sudo-ele-none px-4 font-w500" href="./shiftSaleStart.html">Shift Sale</a></li>
                        <li><a class="sudo-ele-none px-4 font-w500" href="./sale.html">Sale</a></li>
                        <li><a class="sudo-ele-none px-4 font-w500" href="./salesHistroy.html">Sale Histroy</a></li>
                        <li><a class="sudo-ele-none px-4 font-w500" href="./#">Item Sale</a></li>
                        <li><a class="sudo-ele-none px-4 font-w500" href="./#">Bill</a></li>
                        <li><a class="sudo-ele-none px-4 font-w500" href="./creditSaleDetailEntry.html">Credit Sale Detail Entry</a></li>
                </li>
            </ul>
            </li>



            <li style="text-align: center;">
                <a class="has-arrow sidemenu-item px-4" href="javascript:void()" aria-expanded="false">
                    <i class="fa-solid fa-gas-pump icon-style me-0" aria-hidden="true"></i>
                    <span class="nav-text ">Fule</span>
                </a>
                <ul aria-expanded="false">
                    <li><a class="sudo-ele-none px-4 font-w500" href="./tankRefill.html">Refill</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./tankRefillHistory.html">Tank Refill History</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./fuelRefillVoucher.html">Fuel Refill Voucher</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./refillHistroyDetails.html">Refill Histroy Details</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./nozzleReading.html">Nozzle Reading</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./tankReading.html">Tank Reading</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./tankReadingHistory.html">Tank Reading History</a></li>
            </li>
            </ul>
            <!-- <li>
              <a href="./conversations.html" aria-expanded="false">
                <i class="fas fa-sms"></i>
                <span class="nav-text">Conversations</span>
              </a>
            </li> -->
            <!-- <li>
              <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                <i class="fas fa-user"></i>
                <span class="nav-text">Customer</span>
              </a>
              <ul aria-expanded="false">
                <li><a href="./ui-accordion.html">Add</a></li>
                <li><a href="./ui-alert.html">Edit</a></li>
              </ul>
            </li> -->
            <li style="text-align: center;">
                <a class="has-arrow sidemenu-item px-4" href="javascript:void()" aria-expanded="false">
                    <i class="fa-regular fa-money-bill-1 icon-style me-0"></i>
                    <span class="nav-text">Payment </span>
                </a>
                <ul aria-expanded="false">
                    <li><a class="sudo-ele-none px-4 font-w500" href="./#">Vendors</a></li>
                </ul>
            </li>

            <li style="text-align: center;">
                <a class="has-arrow sidemenu-item px-4" href="javascript:void()" aria-expanded="false">
                    <i class="fa-solid fa-building-columns icon-style me-0"></i>
                    <span class="nav-text">Banks</span>
                </a>
                <ul aria-expanded="false">
                    <li><a class="sudo-ele-none px-4 font-w500" href="./#">Bnaks </a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./#">Personal Accounts</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./#">Transfer</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./#">Transaction</a></li>
                </ul>
            </li>

            <li style="text-align: center;">
                <a class="has-arrow sidemenu-item px-4" href="javascript:void()" aria-expanded="false">
                    <i class="	fas fa-users icon-style me-0"></i>
                    <span class="nav-text">Field</span>
                </a>
                <ul aria-expanded="false">
                    <li><a class="sudo-ele-none px-4 font-w500" href="./Field1.html">Field-1</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./Field217.html">Field-217</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./card.html">Shopping caed</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./cashierBalance.html">Cashier balance</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./reading.html">Reading</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./createHeads.html">Create Heads</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./#">Transaction</a></li>
                </ul>
            </li>

            <li style="text-align: center;">
                <a class="has-arrow sidemenu-item px-4" href="javascript:void()" aria-expanded="false">
                    <i class="fa-solid fa-money-bill icon-style me-0"></i>
                    <span class="nav-text">Expenses</span>
                </a>
                <ul aria-expanded="false">
                    <li><a class="sudo-ele-none px-4 font-w500" href="./expense.html">Expense</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./#">Salary Expense</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./#">Machine Expense</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./#">Mics Expense</a></li>
                </ul>
            </li>

            <!-- <li style="text-align: center;">
              <a href="#" class=" sidemenu-item px-4" aria-expanded="false">
                <i class="fas fa-phone me-0"></i>
                <span class="nav-text">Customers</span>
              </a>
            </li> -->
            <li style="text-align: center;">
                <a href="#" class=" sidemenu-item px-4" aria-expanded="false">
                    <i class="fas fa-phone icon-style me-0"></i>
                    <span class="nav-text">Vendors</span>
                </a>
            </li>
            <li style="text-align: center;">
                <a href="#" class=" sidemenu-item px-4" aria-expanded="false">
                    <i class="fa-solid fa-user icon-style me-0"></i>
                    <span class="nav-text">Human</span>
                </a>
            </li>
            <li style="text-align: center;">
                <a href="#" class=" sidemenu-item px-4" aria-expanded="false">
                    <i class="fa-solid fa-users icon-style me-0"></i>
                    <span class="nav-text">Customers</span>
                </a>
            </li>

            <li style="text-align: center;">
                <a class="has-arrow sidemenu-item px-4" href="javascript:void()" aria-expanded="false">
                    <i class="	fas fa-users icon-style me-0"></i>
                    <span class="nav-text">Setup</span>
                </a>
                <ul aria-expanded="false">
                    <li><a class="sudo-ele-none px-4 font-w500" href="./tank&NozzleSetup.html">Pump & Nozzle Setup</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./tank.html">Tank Setup</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./creditCompanySetup.html">Credit Company Setup </a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./posSetup.html">Pos Setup</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./#">Products</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./#">Sekkubg Price</a></li>
                </ul>
            </li>

            <li style="text-align: center;">
                <a class="has-arrow sidemenu-item px-4" href="javascript:void()" aria-expanded="false">
                    <i class="	fas fa-users icon-style me-0"></i>
                    <span class="nav-text">Report</span>
                </a>
                <ul aria-expanded="false">
                    <li><a class="sudo-ele-none px-4 font-w500" href="./#">General</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./#">Income Statement</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./#">Porfit & Loss</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./#">A/C Receivable</a></li>
                    <li><a class="sudo-ele-none px-4 font-w500" href="./#">A/C Payable</a></li>
                </ul>
            </li>

            <li style="text-align: center;">
                <a href="salesReport.html" class="sidemenu-item px-4" aria-expanded="false">
                    <i class="fas fa-user-cog icon-style me-0"></i>
                    <span class="nav-text">Sale Report</span>
                </a>
            </li>
            <li style="text-align: center;">
                <a href="#" class="sidemenu-item px-4" aria-expanded="false">
                    <i class="fas fa-user-cog icon-style"></i>
                    <span class="nav-text">consumption Report</span>
                </a>
            </li>
            <!-- <li>
              <a href="settings.html" aria-expanded="false">
                <i class="fas fa-cog"></i>
                <span class="nav-text">Settings</span>
              </a>
            </li> -->
            <div class="avatar">
                <img src="images/avatar/1.png" class="rounded-circle user_img" alt="" />
            </div>
            </ul>
        </div>
    </div>
</template>
<script>

import ApiService from "../../../Services/ApiService";
import ApiRoutes from "../../../Services/ApiRoutes";

export default {
    components: {

    },
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
        removeActive: function(e) {
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
