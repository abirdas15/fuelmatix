<template>
    <div class="content-body">
        <div class="container-fluid" >
            <div class="text-end">
<!--                <button class="btn btn-primary" @click="print">Print</button>-->
            </div>
            <div id="print_area">
                <div class="text-center mb-3" >
                    <h2>Balance Sheet</h2>
                    <input type="text" class="date form-control m-auto w-15" placeholder="Date" v-model="param.date">
                </div>
                <div class="w-35 balance-sheet">
                    <div class="asset-value" v-if="assets.length > 0">
                        <h4>Assets</h4>
                        <TreeNode v-for="data in assets" :key="data.id" :node="data"/>
                        <div class="d-flex align-items-center justify-content-between">
                            <h4>Total Assets</h4>
                            <strong>
                                <span v-if="balance.total_asset < 0" class="text-danger">({{formatPrice(Math.abs(balance.total_asset))}})</span>
                                <span v-else>{{formatPrice(balance.total_asset)}}</span>
                            </strong>
                        </div>
                        <hr>
                        <hr>
                    </div>
                    <div class="liability-value" v-if="liabilities.length > 0">
                        <h4>Liabilities</h4>
                        <TreeNode v-for="data in liabilities" :key="data.id" :node="data"/>
                        <div class="d-flex align-items-center justify-content-between">
                            <h4>Total Liabilities</h4>
                            <strong>
                                <span v-if="balance.total_liabilities < 0" class="text-danger">({{formatPrice(Math.abs(balance.total_liabilities))}})</span>
                                <span v-else>{{formatPrice(balance.total_liabilities)}}</span>
                            </strong>
                        </div>
                        <hr>
                    </div>
                    <div class="equity-value" v-if="equity.length > 0">
                        <h4>Equity</h4>
                        <TreeNode v-for="data in equity" :key="data.id" :node="data"/>
                        <div class="d-flex align-items-center justify-content-between">
                            <h4>Retained Earnings</h4>
                            <strong>
                                <span v-if="balance.retain_earning < 0" class="text-danger">({{formatPrice(Math.abs(balance.retain_earning))}})</span>
                                <span v-else>{{formatPrice(balance.retain_earning)}}</span>
                            </strong>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <h4>Total Equity</h4>
                            <strong>
                                <span v-if="balance.total_equity < 0" class="text-danger">({{formatPrice(Math.abs(balance.total_equity))}})</span>
                                <span v-else>{{formatPrice(balance.total_equity)}}</span>
                            </strong>
                        </div>
                        <hr>

                        <div class="d-flex align-items-center justify-content-between">
                            <h4>Total Liabilities and Equity </h4>
                            <strong>
                                <span v-if="balance.total_equity_and_liabilities < 0" class="text-danger">({{formatPrice(Math.abs(balance.total_equity_and_liabilities))}})</span>
                                <span v-else>{{formatPrice(balance.total_equity_and_liabilities)}}</span>
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";
import TreeNode from "./TreeNode";

export default {
    components: {TreeNode},
    data: function () {
        return {
            balance: {},
            assets: [],
            liabilities: [],
            equity: [],
            param: {
                date: ''
            }
        }
    },
    methods: {
        getBalanceSheet: function () {
            if (this.param.date == '') {
                this.param.date = moment().format('YYYY-MM-DD')
            }
            ApiService.POST(ApiRoutes.BalanceSheetGet, this.param, res => {
                if (parseInt(res.status) === 200) {
                    this.assets = res.data.assets;
                    this.liabilities = res.data.liabilities;
                    this.liabilities = res.data.liabilities;
                    this.equity = res.data.equity;
                    this.balance = res.data;
                }
            });
        },
        print() {
            $('#print_area').print()
        }
    },
    mounted() {
        setTimeout(() => {
            $('.date').flatpickr({
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d",
                defaultDate: 'today',
                onChange: (dateStr) => {
                    this.param.date = dateStr
                    this.getBalanceSheet()
                }
            })
            this.getBalanceSheet()
        }, 1000)
    }
}
</script>

<style scoped lang="scss">

.balance-sheet{
    margin: auto;
    padding: 10px;
    border: 1px solid #d1cfcf;
}
</style>
