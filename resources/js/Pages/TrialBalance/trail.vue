<template>
    <div class="content-body">
        <div class="container-fluid" >
            <div class="text-end">
                <!--                <button class="btn btn-primary" @click="print">Print</button>-->
            </div>
            <div id="print_area">
                <div class="text-center mb-3" >
                    <h2>Trial Balance</h2>
                    <input type="text" class="date form-control m-auto w-15" placeholder="Date">
                </div>

                <div class="w-35 balance-sheet" v-if="!loading">
                    <table class="table mb-0">
                        <thead>
                        <tr>
                            <th class="bg-secondary text-white border-0">General Ledger Accounts</th>
                            <th class="bg-secondary text-white text-end border-0">Dr.-Debit</th>
                            <th class="bg-secondary text-white text-end border-0">Cr.-Credit</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="data in balance">
                            <td class="border-0">{{data.category}}</td>
                            <td class="text-end border-0">{{data.debit_amount}}</td>
                            <td class="text-end border-0">{{data.credit_amount}}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="text-success border-0"><strong>Total</strong></td>
                            <td class="text-end border-0">{{total_debit_amount}}</td>
                            <td class="text-end border-0">{{total_credit_amount}}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="w-35 balance-sheet text-center" v-if="loading">
                    <i class="fas fa-spinner fa-5x fa-spin"></i>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";

export default {
    data: function () {
        return {
            balance: {},
            total_credit_amount: 0,
            total_debit_amount: 0,
            param: {
                start_date: '',
                end_date: ''
            },
            loading: false
        }
    },
    methods: {
        getTrialBalance: function () {
            this.loading = true
            ApiService.POST(ApiRoutes.TrailBalanceGet, this.param, res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.balance = res.data;
                    this.total_credit_amount = res.total_credit_amount;
                    this.total_debit_amount = res.total_debit_amount;
                }
            });
        },
    },
    mounted() {
        $('#dashboard_bar').text('Trial Statement')
        this.loading = true
        this.param.start_date = new Date().getFullYear() + '-01-01'
        this.param.end_date = new Date().getFullYear() + '-12-31'
        $('.date').val(this.param.start_date +' to ' + this.param.end_date)
        setTimeout(() => {
            $('.date').flatpickr({
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d",
                mode: 'range',
                onChange: (date, dateStr) => {
                    let dateArr = dateStr.split('to')
                    if (dateArr.length == 2) {
                        this.param.start_date = dateArr[0]
                        this.param.end_date = dateArr[1]
                        this.getTrialBalance()
                    }
                }
            })
            this.getTrialBalance()
        }, 1000)
    }
}
</script>

<style scoped lang="scss">
.balance-sheet{
    background-color: #ffffff;
    margin: auto;
    border: 1px solid #d1cfcf;
}
</style>
