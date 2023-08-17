<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Daily Report</a></li>

                </ol>
            </div>
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Daily Report</h4>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-end justify-content-between">
                            <div class="col-sm-3">
                                <label class="form-label">Date:</label>
                                <input type="text" class="form-control date bg-white" name="date" v-model="param.date">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-primary" v-if="!loadingFile" @click="downloadPdf">Download PDF</button>
                                <button class="btn btn-primary" v-if="loadingFile">Downloading PDF...</button>
                            </div>
                        </div>
                        <div  v-if="data">
                            <h3 class="text-center mb-4">Product Sale</h3>
                            <table class="table table-bordered mb-5">
                                <thead>
                                <tr>
                                    <th rowspan="2">Sale</th>
                                    <th :colspan="data['shift_sale']['totalShift'] + 1" class="text-center" v-for="index in data['shift_sale']['totalShift']" :key="index">
                                        Shift {{ index }}
                                    </th>
                                    <th colspan="2" class="text-center">Total</th>
                                </tr>
                                <tr>
                                    <template  v-for="index in data['shift_sale']['totalShift']">
                                        <th class="">Quantity</th>
                                        <th class="text-end">Amount</th>
                                    </template>

                                    <th class="">Quantity</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="row in data['shift_sale']['data']">
                                    <td>{{ row?.name }}</td>
                                    <template v-for="value in row?.value">
                                        <td>{{ value['quantity'] }}liters</td>
                                        <td class="text-end">
                                            <div>{{ value['amount'] }}</div>
                                        </td>
                                    </template>
                                    <td>
                                        <div>{{ row?.total?.quantity }} liters</div>
                                        <div><i class="fa-solid" :class="getClass(row?.total?.percent)"></i></div>
                                        <div v-html="getPercent(row?.total?.percent)"></div>

                                    </td>
                                    <td class="text-end">
                                        <div>{{ row?.total?.amount }}</div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <h3 class="text-center mb-4">Refill</h3>
                            <table class="table table-bordered mb-5">
                                <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Time</th>
                                    <th>Liters</th>
                                    <th>Loss/Gain</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="row in data['tank_refill']">
                                    <td>{{ row['product_name'] }}</td>
                                    <td>{{ row['date'] }}</td>
                                    <td>{{ row['quantity'] }}litres</td>
                                    <td>{{ row['net_profit'] }} litres</td>
                                </tr>
                                </tbody>
                            </table>
                            <h3 class="text-center mb-4">Stock (tank_log)</h3>
                            <table class="table table-bordered mb-5">
                                <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Start</th>
                                    <th>End</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="row in data['stock']">
                                    <td>{{ row?.name }}</td>
                                    <td>{{ row['opening_stock'] }} litres</td>
                                    <td>{{ row['closing_stock'] }} litres</td>
                                </tr>
                                </tbody>
                            </table>
                            <h3 class="text-center mb-4">Expenses</h3>
                            <table class="table table-bordered mb-5">
                                <tr>
                                    <th>Salary</th>
                                    <td class="text-end">{{ data['expense']['salary'] }}</td>
                                </tr>
                                <tr v-for="row in data['expense']['cost_of_good_sold']">
                                    <th>COGS ({{ row['category_name'] }})</th>
                                    <td class="text-end">{{ row['amount'] }}</td>
                                </tr>
                            </table>
                            <h3 class="text-center mb-4">Due Payments</h3>
                            <table class="table table-bordered mb-5">
                                <thead>
                                <tr>
                                    <th>Provider</th>
<!--                                    <th>Date Submitted</th>
                                    <th>Due Date</th>-->
                                    <th>Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="row in data['due_payments']">
                                    <td class="">{{ row['category_name'] }}</td>
                                    <td class="text-end">{{ row['amount'] }}</td>
                                </tr>
                                </tbody>
                            </table>
                            <h3 class="text-center mb-4">Due Invoices</h3>
                            <table class="table table-bordered mb-5">
                                <thead>
                                <tr>
                                    <th>Party</th>
<!--                                    <th>Billed/Invoiced</th>
                                    <th>Due</th>
                                    <th>Overdue </th>-->
                                    <th>Total </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="row in data['due_invoice']">
                                    <td class="">{{ row['category_name'] }}</td>
                                    <td class="text-end">{{ row['amount'] }}</td>
                                </tr>
                                </tbody>
                            </table>
                            <h3 class="text-center mb-4">Asset Balance </h3>
                            <table class="table table-bordered mb-5">
                                <tr v-for="row in data['asset_balance']['cash']">
                                    <th>{{ row['category_name'] }}</th>
                                    <td class="text-end">{{ row['amount'] }}</td>
                                </tr>
                                <tr v-for="row in data['asset_balance']['bank']">
                                    <th>{{ row['category_name'] }}</th>
                                    <td class="text-end">{{ row['amount'] }}</td>
                                </tr>
                            </table>
                            <h3 class="text-center mb-4">Attendance</h3>
                            <table class="table table-bordered mb-5">
                                <thead>
                                <tr>
                                    <th>Shift 1</th>
                                    <th>Shift 2</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        <div>Fuelman -3</div>
                                        <div>Guard -2</div>
                                        <div>Suprevisor (suvo)-1</div>
                                        <div>Engineer (Yasin) -1</div>
                                    </td>
                                    <td>
                                        <div>Fuelman -3</div>
                                        <div>Guard -2</div>
                                        <div>Suprevisor (suvo)-1</div>
                                        <div>Engineer (Yasin) -1</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Leave
                                    </td>
                                    <td></td>
                                </tr>
                                </tbody>
                            </table>
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

export default {
    data() {
        return {
            param: {
                date: ''
            },
            data: null,
            loadingFile: false,
            loading: false,
        }
    },
    methods: {
        getClass: function (text) {
            if (text[0] == '-') {
                return `fa-circle-down text-danger`
            } else {
                return `fa-circle-up text-success`
            }

        },
        getPercent: function (text) {
            if (text[0] == '-') {
                return `<div class="text-danger">${text} %</div>`
            } else {
                return `<div class="text-success">${text} %</div>`
            }
        },
        getReport: function () {
            this.loading = true
            if (this.param.date == '') {
                this.param.date = moment().format('YYYY-MM-DD')
            }
            ApiService.POST(ApiRoutes.dailyLog, this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.data = res.data
                }
            });
        },
        downloadPdf: function () {
            this.loadingFile = true
            ApiService.DOWNLOAD(ApiRoutes.dailyLogPdf, this.param,'',res => {
                this.loadingFile = false
                let blob = new Blob([res], {type: 'pdf'});
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'Daily Log.pdf';
                link.click();
            });
        }
    },
    created() {

    },
    mounted() {
        setTimeout(() => {
            $('.date').flatpickr({
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d",
                defaultDate: 'today',
                onChange: (dateStr, date) => {
                    this.param.date = date
                    this.getReport()
                }
            })
            this.getReport()
        }, 1000)
        $('#dashboard_bar').text('Daily Report')
    }
}
</script>

<style lang="scss" scoped>
table{
    thead{
        tr{
            background-color: rgb(72, 134, 238);
        }
    }
}
</style>
