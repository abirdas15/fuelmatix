<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Daily Report</a></li>

                </ol>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Daily Log Report</h4>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-sm-3">
                            <label class="form-label">Date:</label>
                            <input type="text" class="form-control date bg-white" name="date" v-model="param.date">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-sm-3">
                            <button class="btn btn-primary" v-if="!loading" @click="getReport">Filter</button>
                            <button class="btn btn-primary" v-if="loading">Filtering....</button>
                        </div>
                        <div class="col-sm-2 ms-auto">
                            <button class="btn btn-primary" v-if="!loadingFile" @click="downloadPdf">Download PDF</button>
                            <button class="btn btn-primary" v-if="loadingFile">Downloading PDF...</button>
                        </div>
                    </div>
                </div>
            </div>
            <template v-if="data && !loading">
                <div class="row" >
                    <div class="col-xl-12">
                        <div class="card ">
                            <div class="card-header bg-secondary">
                                <h4 class="card-title">Product Sale</h4>
                            </div>
                            <div class="card-body">
                                <div>
                                    <div class="table-responsive">
                                        <table class="table table-responsive-md" >
                                            <thead>
                                            <tr>
                                                <th>Shift</th>
                                                <th>Quantity</th>
                                                <th style="text-align: right">Amount</th>
                                            </tr>
                                            </thead>
                                            <template v-for="$shiftSale in data['shift_sale']">
                                                <tbody>
                                                <tr>
                                                    <th colspan="3" style="text-align: center; background-color: #eae9e9">{{ $shiftSale['product_name'] }}</th>
                                                </tr>
                                                </tbody>
                                                <template v-for="$row in $shiftSale['data']">
                                                    <tbody >
                                                    <tr>
                                                        <td>{{ $row['time'] }}</td>
                                                        <td>{{ $row['quantity']+' '+$row['unit'] }}</td>
                                                        <td style="text-align: right">{{ $row['amount'] }}</td>
                                                    </tr>
                                                    </tbody>
                                                </template>
                                            </template>
                                            <template v-for="$posSale in data['pos_sale']">
                                                <tbody>
                                                <tr>
                                                    <th colspan="3" style="text-align: center; background-color: #eae9e9">{{ $posSale['product_name'] }}</th>
                                                </tr>
                                                <tr>
                                                    <td>{{ $posSale['time'] }}</td>
                                                    <td>{{ $posSale['quantity']+' '+$posSale['unit'] }}</td>
                                                    <td style="text-align: right">{{ $posSale['amount'] }}</td>
                                                </tr>
                                                </tbody>
                                            </template>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card ">
                            <div class="card-header bg-secondary">
                                <h4 class="card-title">Refill</h4>
                            </div>
                            <div class="card-body">
                                <div>
                                    <div class="table-responsive">
                                        <table class="table table-responsive-md">
                                            <thead>
                                            <tr >
                                                <th >Product</th>
                                                <th >Time</th>
                                                <th >Litres</th>
                                                <th >Loss/Gain</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="$row in data['tank_refill']">
                                                <td>{{ $row['product_name'] }}</td>
                                                <td>{{ $row['date'] }}</td>
                                                <td>{{ $row['quantity']+' '+$row['unit'] }}</td>
                                                <td>{{ $row['net_profit']+' '+$row['unit'] }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card ">
                            <div class="card-header bg-secondary">
                                <h4 class="card-title">Stock</h4>
                            </div>
                            <div class="card-body">
                                <div>
                                    <div class="table-responsive">
                                        <table class="table table-responsive-md">
                                            <thead>
                                            <tr>
                                                <th >Product</th>
                                                <th >Start</th>
                                                <th >End</th>

                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="$row in data['stock']">
                                                <td>{{ $row['name'] }}</td>
                                                <td>{{ $row['opening_stock']+' '+$row['unit'] }}</td>
                                                <td>{{ $row['closing_stock']+' '+$row['unit'] }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card ">
                            <div class="card-header bg-secondary">
                                <h4 class="card-title">Expenses  </h4>
                            </div>
                            <div class="card-body">
                                <div>
                                    <div class="table-responsive">
                                        <table class="table table-responsive-md" >
                                            <tbody>
                                            <tr>
                                                <td>Salary</td>
                                                <td class="text-end">{{ data['expense']['salary'] }}</td>
                                            </tr>
                                            <tr v-for="$row in data['expense']['cost_of_good_sold']">
                                                <td>COGS ({{ $row['category_name'] }})</td>
                                                <td class="text-end">{{ $row['amount'] }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card ">
                            <div class="card-header bg-secondary">
                                <h4 class="card-title">Asset Balance</h4>
                            </div>
                            <div class="card-body">
                                <div>
                                    <div class="table-responsive">
                                        <table class="table table-responsive-md" >
                                            <tbody>
                                            <tr v-for="$row in data['asset_balance']['cash']">
                                                <td>{{ $row['category_name'] }}</td>
                                                <td class="text-end">{{ $row['amount'] }}</td>
                                            </tr>
                                            <tr v-for="$row in data['asset_balance']['bank']">
                                                <td>{{ $row['category_name'] }}</td>
                                                <td class="text-end">{{ $row['amount'] }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card ">
                            <div class="card-header bg-secondary">
                                <h4 class="card-title">Due Payments </h4>
                            </div>
                            <div class="card-body">
                                <div>
                                    <div class="table-responsive">
                                        <table class="table table-responsive-md" >
                                            <thead>
                                            <tr>
                                                <th>Provider</th>
                                                <th>Amount</th>

                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="$row in data['due_payments']">
                                                <td >{{ $row['category_name'] }}</td>
                                                <td style="text-align: right">{{ $row['amount'] }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card ">
                            <div class="card-header bg-secondary">
                                <h4 class="card-title">Due Invoices</h4>
                            </div>
                            <div class="card-body">
                                <div class=" mt-4">
                                    <div class="table-responsive">
                                        <table class="table table-responsive-md">
                                            <thead>
                                            <tr>
                                                <th>Party</th>
<!--                                                <th>Billed/Invoiced</th>
                                                <th>Due </th>
                                                <th>Overdue</th>-->
                                                <th>Total</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="$row in data['due_invoice']">
                                                <td>{{ $row['category_name'] }}</td>
                                                <td style="text-align: right">{{ $row['amount'] }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
<!--                <div class="row">-->
<!--                    <div class="col-xl-12">-->
<!--                        <div class="card ">-->
<!--                            <div class="card-header bg-secondary">-->
<!--                                <h4 class="card-title">Attendance</h4>-->
<!--                            </div>-->
<!--                            <div class="card-body">-->
<!--                                <div>-->
<!--                                    <div class="table-responsive">-->
<!--                                        <table class="table table-responsive-md" >-->
<!--                                            <thead>-->
<!--                                            <tr>-->
<!--                                                <th >Shift 1</th>-->
<!--                                                <th >Shift 1</th>-->
<!--                                            </tr>-->
<!--                                            </thead>-->
<!--                                            <tbody>-->
<!--                                            <tr>-->
<!--                                                <td>Fuelman -3</td>-->
<!--                                                <td>Fuelman -3</td>-->

<!--                                            </tr>-->
<!--                                            <tr>-->
<!--                                                <td>Guard -2</td>-->
<!--                                                <td>Guard -2</td>-->
<!--                                            </tr>-->
<!--                                            <tr>-->
<!--                                                <td>Suprevisor (suvo)-1</td>-->
<!--                                                <td>Suprevisor (suvo)-1</td>-->
<!--                                            </tr>-->
<!--                                            <tr>-->
<!--                                                <td>Engineer (Yasin) -1</td>-->
<!--                                                <td>Engineer (Yasin) -1</td>-->
<!--                                            </tr>-->
<!--                                            </tbody>-->
<!--                                        </table>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
            </template>
            <div v-else class="text-center">Please Press filter button to get Report</div>
            <div v-if="loading" class="text-center">
                <i class="fas fa-spinner fa-5x fa-spin"></i>
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
                }
            })
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
