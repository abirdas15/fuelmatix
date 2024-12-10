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
                <li>
                    <router-link :to="{name: 'NozzleStatus'}" aria-expanded="false"  @click.native="LoadLoader()">
                        <i class="fas fa-home"></i>
                        <span class="nav-text">Nozzle Status</span>
                    </router-link>
                </li>

                <li><a class="has-arrow " href="javascript:void(0)" aria-expanded="false">
                    <i class="fa-solid fa-cart-shopping icon-style" aria-hidden="true"></i>
                    <span class="nav-text">Sales</span>
                </a>
                    <ul aria-expanded="false" class="bichi">
                        <li v-if="CheckPermission(Section.SHIFT_SALE + '-' + Action.VIEW)"><router-link :to="{name: 'ShiftSaleListStart'}">Shift Sale</router-link></li>
                        <li v-if="CheckPermission(Section.SHIFT_SALE + '-' + Action.VIEW)"><router-link :to="{name: 'ShiftSalePrevious'}">Previous Shift Sale</router-link></li>
                        <li v-if="CheckPermission(Section.POS + '-' + Action.VIEW)"><router-link :to="{name: 'Pos'}" >POS</router-link></li>
                        <li v-if="CheckPermission(Section.POS_HISTORY + '-' + Action.VIEW)"><router-link :to="{name: 'PosList'}" >POS History</router-link></li>
                        <li v-if="CheckPermission(Section.DUMMY_SALE + '-' + Action.CREATE)"><router-link :to="{name: 'DummySaleCreate'}" >Dummy Sale</router-link></li>
                        <li v-if="CheckPermission(Section.DUMMY_SALE + '-' + Action.VIEW)"><router-link :to="{name: 'DummySaleList'}" >Dummy Sale History</router-link></li>
                        <li v-if="CheckPermission(Section.BULK_SALE + '-' + Action.VIEW)"><router-link :to="{name: 'BulkSale'}">Bulk Sale</router-link></li>
                        <li v-if="CheckPermission(Section.COMPANY_SALE + '-' + Action.VIEW)"><router-link :to="{name: 'CompanySale'}">Company Sale </router-link></li>
                        <li v-if="CheckPermission(Section.COMPANY_SALE + '-' + Action.VIEW)"><router-link :to="{name: 'unauthorizedBill'}">Unauthorized Bill </router-link></li>
                    </ul>
                </li>
                <li><a class="has-arrow " href="javascript:void(0)" aria-expanded="false">
                    <i class="fa-solid fa-gas-pump"></i>
                    <span class="nav-text">Refill</span>
                </a>
                    <ul aria-expanded="false" class="bichi">
                        <li v-if="CheckPermission(Section.TANK_REFILL + '-' + Action.VIEW)"><router-link :to="{name: 'TankRefill'}">Tank Refill</router-link></li>
                        <li v-if="CheckPermission(Section.TANK_VISUAL + '-' + Action.VIEW)"><router-link :to="{name: 'TankVisual'}"> Tank Visual</router-link></li>
                    </ul>
                </li>
                <li><a class="has-arrow " href="javascript:void(0)" aria-expanded="false">
                    <i class="fa-solid fa-ruler"></i>
                    <span class="nav-text">Reading</span>
                </a>
                    <ul aria-expanded="false" class="bichi">
                        <li v-if="CheckPermission(Section.TANK_READING + '-' + Action.VIEW)"><router-link :to="{name: 'TankReading'}">Tank</router-link></li>
                        <li v-if="CheckPermission(Section.NOZZLE_READING + '-' + Action.VIEW)"><router-link :to="{name: 'NozzleReading'}">Nozzle</router-link></li>
                    </ul>
                </li>
                <li><a class="has-arrow " href="javascript:void(0)" aria-expanded="false">
                    <i class="fa-regular fa-money-bill-1"></i>
                    <span class="nav-text">Payment</span>
                </a>
                    <ul aria-expanded="false" class="bichi">
                        <li v-if="CheckPermission(Section.PAY_ORDER + '-' + Action.VIEW)"><router-link :to="{name: 'PayOrder'}">Pay Orders</router-link></li>
                        <li v-if="CheckPermission(Section.TRANSFER + '-' + Action.VIEW)"><router-link :to="{name:'balanceTransfer'}">Transfer</router-link></li>
                        <li v-if="CheckPermission(Section.EXPENSE + '-' + Action.VIEW)"><router-link :to="{name:'Expense'}">Expense</router-link></li>
                        <li v-if="CheckPermission(Section.BILL + '-' + Action.VIEW)"><router-link :to="{name: 'Invoices'}">Invoices </router-link></li>
                        <li v-if="CheckPermission(Section.INVOICE_PAYMENT + '-' + Action.VIEW)"><router-link :to="{name: 'InvoicePayment'}">Invoice Payment </router-link></li>
                        <li v-if="CheckPermission(Section.COMPANY_BILL + '-' + Action.VIEW)"><router-link :to="{name: 'CompanyBills'}">Company Bills </router-link></li>
                        <li v-if="CheckPermission(Section.PURCHASE + '-' + Action.VIEW)"><router-link :to="{name: 'purchase'}">Purchase</router-link></li>
                    </ul>

                </li>
                <li><a class="has-arrow " href="javascript:void(0)" aria-expanded="false">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">HR</span>
                </a>
                    <ul aria-expanded="false" class="bichi">
                        <li v-if="CheckPermission(Section.EMPLOYEE + '-' + Action.VIEW)"><router-link :to="{name: 'employee'}">Employees </router-link></li>
                        <li v-if="CheckPermission(Section.SALARY + '-' + Action.VIEW)"><router-link :to="{name: 'salary'}">Salary </router-link></li>
                        <li v-if="CheckPermission(Section.ATTENDANCE + '-' + Action.VIEW)"><a href="javascript:void(0)">Attendance</a></li>
                        <li v-if="CheckPermission(Section.STAFF_LOAN + '-' + Action.VIEW)"><router-link :to="{name: 'StaffLoanList'}">Loan</router-link></li>
                    </ul>
                </li>
                <li><a class="has-arrow " href="javascript:void(0)" aria-expanded="false">
                    <i class="fa-solid fa-gear"></i>
                    <span class="nav-text">Setup</span>
                </a>
                    <ul aria-expanded="false" class="bichi">
                        <li v-if="CheckPermission(Section.TANK + '-' + Action.VIEW)"><router-link :to="{name: 'Tank'}"> Tank</router-link></li>
                        <li v-if="CheckPermission(Section.DISPENSER + '-' + Action.VIEW)"><router-link :to="{name: 'Dispenser'}">Dispenser</router-link></li>
                        <li v-if="CheckPermission(Section.NOZZLE + '-' + Action.VIEW)"><router-link :to="{name: 'Nozzle'}">Nozzle</router-link></li>
                        <li v-if="CheckPermission(Section.PRODUCT + '-' + Action.VIEW)"><router-link :to="{name: 'Product'}">Product</router-link></li>
                        <li v-if="CheckPermission(Section.BANK + '-' + Action.VIEW)"><router-link :to="{name: 'Bank'}">Banks </router-link></li>
                        <li v-if="CheckPermission(Section.VENDOR + '-' + Action.VIEW)"><router-link :to="{name: 'Vendor'}">Vendors</router-link></li>
                        <li v-if="CheckPermission(Section.CREDIT_COMPANY + '-' + Action.VIEW)"><router-link :to="{name: 'CreditCompany'}">Credit Company </router-link></li>
                        <li v-if="CheckPermission(Section.POS + '-' + Action.VIEW)"><router-link :to="{name: 'posMachine'}">POS</router-link></li>
                        <li v-if="CheckPermission(Section.USER + '-' + Action.VIEW)"><router-link :to="{name: 'user'}">Users</router-link></li>
                        <li v-if="CheckPermission(Section.SYSTEM_SETTING + '-' + Action.VIEW)"><router-link :to="{name: 'system'}">System setting</router-link></li>
                        <li v-if="CheckPermission(Section.VOUCHER + '-' + Action.VIEW)"><router-link :to="{name: 'voucher'}">Voucher</router-link></li>
                        <li v-if="CheckPermission(Section.DRIVER + '-' + Action.VIEW)"><router-link :to="{name: 'driver'}">Driver</router-link></li>
                        <li v-if="CheckPermission(Section.FUEL_ADJUSTMENT + '-' + Action.VIEW)"><router-link :to="{name: 'adjustment'}">Fuel Adjustment</router-link></li>
                        <li v-if="CheckPermission(Section.CAR + '-' + Action.VIEW)"><router-link :to="{name: 'car'}">Car</router-link></li>
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
                        <li v-if="CheckPermission(Section.DAILY_REPORT + '-' + Action.VIEW)">
                            <router-link :to="{name: 'StockSummary'}" >Daily Report
                            </router-link>
                        </li>
                        <li v-if="CheckPermission(Section.BALANCE_SHEET + '-' + Action.VIEW)">
                            <router-link :to="{name: 'BalanceSheet'}" >Balance
                                Sheet
                            </router-link>
                        </li>
                        <li v-if="CheckPermission(Section.PROFIT_AND_LOSS + '-' + Action.VIEW)">
                            <router-link :to="{name: 'ProfitLoss'}" >Profit and
                                loss
                            </router-link>
                        </li>
                        <li v-if="CheckPermission(Section.INCOME_STATEMENT + '-' + Action.VIEW)">
                            <router-link :to="{name: 'IncomeStatement'}" >Income
                                Statement
                            </router-link>
                        </li>
                        <li v-if="CheckPermission(Section.ACCOUNT_PAYABLE + '-' + Action.VIEW)">
                            <router-link :to="{name: 'AccountPayable'}" >A/C Payable
                            </router-link>
                        </li>
                        <li v-if="CheckPermission(Section.ACCOUNT_RECEIVABLE + '-' + Action.VIEW)">
                            <router-link :to="{name: 'AccountReceivable'}" >A/C Receivable
                            </router-link>
                        </li>
                        <li v-if="CheckPermission(Section.TRAIL_BALANCE + '-' + Action.VIEW)">
                            <router-link :to="{name: 'TrailBalance'}" >Trial Balance
                            </router-link>
                        </li>
                        <li v-if="CheckPermission(Section.UNAUTHORIZED_BILL + '-' + Action.VIEW)">
                            <router-link :to="{name: 'LedgerSheet'}" >Un authorize Bill
                            </router-link>
                        </li>
                        <li v-if="CheckPermission(Section.SALES_REPORT + '-' + Action.VIEW)">
                            <router-link :to="{name: 'salesReport'}" >Sales Report
                            </router-link>
                        </li>
<!--                        <li v-if="CheckPermission(Section.SALES_STOCK + '-' + Action.VIEW)">-->
<!--                            <router-link :to="{name: 'StockSummary'}" >Sale Stock & Sale Summary-->
<!--                            </router-link>-->
<!--                        </li>-->
                        <li v-if="CheckPermission(Section.VENDOR_REPORT + '-' + Action.VIEW)">
                            <router-link :to="{name: 'VendorReport'}" >Vendor Report
                            </router-link>
                        </li>
                        <li v-if="CheckPermission(Section.EXPENSE_REPORT + '-' + Action.VIEW)">
                            <router-link :to="{name: 'ExpenseReport'}" >Expense Report
                            </router-link>
                        </li>
                        <li v-if="CheckPermission(Section.WINDFALL_REPORT + '-' + Action.VIEW)">
                            <router-link :to="{name: 'WindfallReport'}" >WindFall Report
                            </router-link>
                        </li>
                        <li v-if="CheckPermission(Section.CREDIT_COMPANY_REPORT + '-' + Action.VIEW)">
                            <router-link :to="{name: 'CreditCompanyReport'}" >Credit Company Report
                            </router-link>
                        </li>
                        <li v-if="CheckPermission(Section.DRIVER_REPORT + '-' + Action.VIEW)">
                            <router-link :to="{name: 'DriverReport'}" >Driver Report
                            </router-link>
                        </li>
                        <li v-if="CheckPermission(Section.BILL_SUMMARY + '-' + Action.VIEW)">
                            <router-link :to="{name: 'CompanySummary'}" >Bill Summary
                            </router-link>
                        </li>
                        <li v-if="CheckPermission(Section.POS_REPORT + '-' + Action.VIEW)">
                            <router-link :to="{name: 'PosReport'}" >POS Report
                            </router-link>
                        </li>
                    </ul>
                </li>
                <li v-if="CheckPermission(Section.ROLE + '-' + Action.VIEW)">
                    <router-link :to="{name: 'role'}"  aria-expanded="false">
                        <i class="fa-solid fa-user-shield"></i>
                        <span class="nav-text">Role</span>
                    </router-link>
                </li>
            </ul>
        </div>
    </div>

</template>
<script>

import ApiService from "../../../Services/ApiService";
import ApiRoutes from "../../../Services/ApiRoutes";
import Section from "../../../Helpers/Section"
import Action from "../../../Helpers/Action"

export default {
    components: {},
    data() {
        return {
            ticketData: {},
            Section: Section,
            Action: Action
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
