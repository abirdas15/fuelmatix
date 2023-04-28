<template>
    <div class="content-body">
        <div class="container-fluid" >
            <div class="text-end">
                <!--                <button class="btn btn-primary" @click="print">Print</button>-->
            </div>
            <div id="print_area">
                <div class="text-center mb-3" >
                    <h2>Income Statement</h2>
                    <input type="text" class="date form-control m-auto w-15" placeholder="Date">
                </div>
                <div class="w-35 balance-sheet" v-if="!loading">
                    <strong class="text-success">Revenues</strong>
                    <div class="d-flex align-items-center justify-content-between line" v-for="expense in balance.revenue">
                        <div>{{ expense.category }}</div>
                        <div>
                            <span v-if="expense.balance < 0" class="text-danger">({{formatPrice(Math.abs(expense.balance))}})</span>
                            <span v-else>{{formatPrice(expense.balance)}}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between line">
                        <strong>Total Revenue</strong>
                        <strong>
                            <span v-if="balance.total_revenue < 0" class="text-danger">({{formatPrice(Math.abs(balance.total_revenue))}})</span>
                            <span v-else>{{formatPrice(balance.total_revenue)}}</span>
                        </strong>
                    </div>
                    <strong class="text-success">Expenses</strong>
                    <div class="d-flex align-items-center justify-content-between line" v-for="expense in balance.expense">
                        <div>{{ expense.category }}</div>
                        <div>
                            <span v-if="expense.balance < 0" class="text-danger">({{formatPrice(Math.abs(expense.balance))}})</span>
                            <span v-else>{{formatPrice(expense.balance)}}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between line">
                        <strong>Total Expense</strong>
                        <strong>
                            <span v-if="balance.total_expense < 0" class="text-danger">({{formatPrice(Math.abs(balance.total_expense))}})</span>
                            <span v-else>{{formatPrice(balance.total_expense)}}</span>
                        </strong>
                    </div>
                    <hr>
                    <div class="d-flex align-items-center justify-content-between line">
                        <strong class="text-success">Net Profit</strong>
                        <strong>
                            <span v-if="balance.net_profit < 0" class="text-danger">({{formatPrice(Math.abs(balance.net_profit))}})</span>
                            <span v-else>{{formatPrice(balance.net_profit)}}</span>
                        </strong>
                    </div>
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
            param: {
                start_date: '',
                end_date: ''
            },
            loading: false
        }
    },
    methods: {
        getIncomeStatement: function () {
            this.loading = true
            ApiService.POST(ApiRoutes.IncomeStatement, this.param, res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.balance = res.data;
                }
            });
        },
    },
    mounted() {
        $('#dashboard_bar').text('Income Statement')
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
                        this.getIncomeStatement()
                    }
                }
            })
            this.getIncomeStatement()
        }, 1000)
    }
}
</script>

<style scoped lang="scss">
.balance-sheet{
    background-color: #ffffff;
    margin: auto;
    padding: 10px;
    border: 1px solid #d1cfcf;
    .line{
        padding: 8px 10px;
        &:nth-child(even) {
            background-color: #f0f5f5;
        }
    }
}
</style>
