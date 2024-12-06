<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Stock Summary</a></li>

                </ol>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Filter</h4>
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
                            <button class="btn btn-primary" v-if="!loadingFile" @click="downloadPdf"><i class="fa fa-print" aria-hidden="true"></i>&nbsp;Print</button>
                            <button class="btn btn-primary" v-if="loadingFile"><i class="fa fa-print" aria-hidden="true"></i>&nbsp;Print...</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card" v-if="data != null">
                <div class="text-center mt-2"><h2>Stock Summary</h2></div>
                <div class="card-body mt-0">
                    <template v-for="product in data">
                        <table class="table table-bordered">
                            <tbody>
                            <tr class="bg-custom">
                                <th colspan="6" class="text-center" v-text="product.product_name"></th>
                            </tr>
                            </tbody>
                            <tbody>
                            <tr>
                                <th>Nozzle</th>
                                <th class="text-center">Current Meter</th>
                                <th class="text-center">Previous Meter</th>
                                <th class="text-center">Sale</th>
                                <th class="text-center">Unit Price</th>
                                <th class="text-center">Amount</th>
                            </tr>
                            </tbody>
                            <tbody>
                                <template v-for="tanks in product.tanks">
                                    <template v-for="dispensers in tanks.dispensers">
                                        <template v-for="nozzle in dispensers.nozzle">
                                            <tr>
                                                <th v-text="nozzle.nozzle_name"></th>
                                                <td class="text-end" v-text="nozzle.end_reading_format"></td>
                                                <td class="text-end" v-text="nozzle.start_reading_format"></td>
                                                <td class="text-end" v-text="nozzle.sale_format"></td>
                                                <td class="text-end" v-text="nozzle.unit_price_format"></td>
                                                <td class="text-end" v-text="nozzle.amount_format"></td>
                                            </tr>
                                        </template>
                                    </template>
                                </template>
                            </tbody>
                            <tbody>
                                <tr>
                                    <th colspan="3" class="text-end">Sub Total:</th>
                                    <th v-text="product.total" class="text-end"></th>
                                    <th></th>
                                    <th v-text="product.subtotal_amount" class="text-end"></th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end">Less: Meter Test</th>
                                    <th v-text="product.adjustment" class="text-end"></th>
                                    <th></th>
                                    <th v-text="product.adjustment_amount" class="text-end"></th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end">Total</th>
                                    <th v-text="product.total_sale" class="text-end"></th>
                                    <th></th>
                                    <th v-text="product.total_amount" class="text-end"></th>
                                </tr>
                            </tbody>
                        </table>
                    </template>
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th colspan="5" class="text-end">Grand Total</th>
                                <th class="text-end" v-text="total.grandTotal"></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card" v-if="data != null">
                <div class="text-center mt-2"><h2>Received and Under Tank Summary</h2></div>
                <div class="card-body mt-0">
                    <template v-for="product in data">
                        <table class="table table-bordered">
                            <tbody>
                                <tr class="bg-custom">
                                    <th colspan="5" class="text-center" v-text="product.product_name + ' Under Tank'"></th>
                                </tr>
                                <tr>
                                    <th>U/Tank Name</th>
                                    <th>Previous Balance</th>
                                    <th>Receive</th>
                                    <th>Total</th>
                                    <th class="text-center">{{ product.gain_loss >= 0 ? 'Gain' : 'Loss' }} Ratio</th>
                                </tr>
                            </tbody>
                            <tbody>
                            <tr>
                                <th v-text="product.product_name"></th>
                                <th class="text-end" v-text="product.end_reading"></th>
                                <th class="text-end" v-text="product.tank_refill"></th>
                                <th class="text-end" v-text="product.total_by_product"></th>
                                <th class="text-end" v-text="product.gain_loss_format"></th>
                            </tr>
                            </tbody>
                        </table>
                        <table class="table table-bordered">
                            <tbody>
                            <tr>
                                <th>U/Tank Name</th>
                                <th class="text-center">U/Tank as per DIP</th>
                                <th class="text-center">In Tank Lorry</th>
                                <th class="text-center">Closing Balance</th>
                            </tr>
                            <template v-for="(tank, index) in product.tanks">
                                <tr>
                                    <td v-text="tank.tank_name"></td>
                                    <td class="text-end" v-text="tank.end_reading_format"></td>
                                    <!-- Apply rowspan only to the first row -->
                                    <td v-if="index === 0" class="text-end" :rowspan="product.tanks.length" v-text="product.pay_order"></td>
                                    <td v-if="index === 0" class="text-end" :rowspan="product.tanks.length" v-text="product.closing_balance"></td>
                                </tr>
                            </template>
                            </tbody>
                            <tbody>
                            </tbody>
                        </table>
                    </template>
                </div>
            </div>

            <div class="card" v-if="companySales.length > 0">
                <div class="text-center mt-2"><h2>Company Sale</h2></div>
                <div class="card-body mt-0">
                    <table class="table table-bordered">
                        <tbody>
                        <tr>
                            <th>Company Name</th>
                            <th>Product Name</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Amount</th>
                        </tr>
                        </tbody>
                        <tbody>
                            <tr v-for="each in companySales">
                                <td v-text="each.name"></td>
                                <td class="" v-text="each.product_name"></td>
                                <td class="text-center" v-text="each.quantity"></td>
                                <td class="text-end" v-text="each.amount_format"></td>
                            </tr>
                        </tbody>
                        <tbody>
                            <tr>
                                <th colspan="2" class="text-end">Total:</th>
                                <th class="text-center" v-text="total.quantity"></th>
                                <th class="text-end" v-text="total.amount"></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card" v-if="companyPaid.length > 0">
                <div class="text-center mt-2"><h2>Company Paid</h2></div>
                <div class="card-body mt-0">
                    <table class="table table-bordered">
                        <tbody>
                        <tr>
                            <th>Company Name</th>
                            <th>Payment Method</th>
                            <th class="text-end">Amount</th>
                        </tr>
                        </tbody>
                        <tbody>
                        <tr v-for="each in companyPaid">
                            <td v-text="each.name"></td>
                            <td class="" v-text="each.product_name"></td>
                            <td class="text-end" v-text="each.paid_amount_format"></td>
                        </tr>
                        </tbody>
                        <tbody>
                        <tr>
                            <th colspan="2" class="text-end">Total:</th>
                            <th class="text-end" v-text="total.paid_amount"></th>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card" v-if="productSales.length > 0">
                <div class="text-center mt-2"><h2>Credit Company Product Sale</h2></div>
                <div class="card-body mt-0">
                    <table class="table table-bordered">
                        <tbody>
                        <tr>
                            <th>Product Name</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Amount</th>
                        </tr>
                        </tbody>
                        <tbody>
                        <tr v-for="each in productSales">
                            <td class="" v-text="each.product_name"></td>
                            <td class="text-center" v-text="each.quantity"></td>
                            <td class="text-end" v-text="each.amount_format"></td>
                        </tr>
                        </tbody>
                        <tbody>
                        <tr>
                            <th colspan="1" class="text-end">Total:</th>
                            <th class="text-center" v-text="total.quantity"></th>
                            <th class="text-end" v-text="total.amount"></th>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card" v-if="expenses.length > 0">
                <div class="text-center mt-2"><h2>Expense</h2></div>
                <div class="card-body mt-0">
                    <table class="table table-bordered">
                        <tbody>
                        <tr>
                            <th>Expense Category</th>
                            <th>Payment Type</th>
                            <th class="text-end">Amount</th>
                        </tr>
                        </tbody>
                        <tbody>
                        <tr v-for="each in expenses">
                            <td v-text="each.expense_type"></td>
                            <td class="" v-text="each.payment_method"></td>
                            <td class="text-end" v-text="each.amount_format"></td>
                        </tr>
                        </tbody>
                        <tbody>
                        <tr>
                            <th colspan="2" class="text-end">Total:</th>
                            <th class="text-end" v-text="total.expense"></th>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card" v-if="posSales.length > 0">
                <div class="text-center mt-2"><h2>Pos Sale</h2></div>
                <div class="card-body mt-0">
                    <table class="table table-bordered">
                        <tbody>
                        <tr>
                            <th>Name</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Amount</th>
                        </tr>
                        </tbody>
                        <tbody>
                        <tr v-for="each in posSales">
                            <td v-text="each.category_name"></td>
                            <td class="text-center" v-text="each.quantity"></td>
                            <td class="text-end" v-text="each.price"></td>
                            <td class="text-end" v-text="each.amount"></td>
                        </tr>
                        </tbody>
                        <tbody>
                        <tr>
                            <th colspan="3" class="text-end">Total:</th>
                            <th class="text-end" v-text="total.posSaleTotalAmount"></th>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card" v-if="assetTransfer.length > 0">
                <div class="text-center mt-2"><h2>Asset Transfer</h2></div>
                <div class="card-body mt-0">
                    <table class="table table-bordered">
                        <tbody>
                        <tr>
                            <th>From</th>
                            <th>To</th>
                            <th class="text-end">Amount</th>
                        </tr>
                        </tbody>
                        <tbody>
                        <tr v-for="each in assetTransfer">
                            <td v-text="each.from_category"></td>
                            <td v-text="each.to_category"></td>
                            <td class="text-end" v-text="each.amount"></td>
                        </tr>
                        </tbody>
                        <tbody>
                        <tr>
                            <th colspan="2" class="text-end">Total:</th>
                            <th class="text-end" v-text="total.totalTransferAmount"></th>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";
import moment from "moment/moment";
import Table from "../../admin/Pages/Common/Table.vue";

export default {
    components: {Table},
    data() {
        return {
            param: {
                date: moment().format('YYYY-MM-DD'),
                shift_sale_id: ''
            },
            data: null,
            companySales: [],
            companyPaid: [],
            posSales: [],
            expenses: [],
            assetTransfer: [],
            productSales: [],
            total: {},
            loadingFile: false,
            loading: false,
            shifts: []
        }
    },
    watch:{
        'param.date': function() {
            //this.fetchShift();
        }
    },
    methods: {
        fetchShift: function() {
            ApiService.POST(ApiRoutes.GetShiftByDate, {date: this.param.date}, (res) => {
                if (parseInt(res.status) === 200) {
                    this.shifts = res.data;
                }
            });
        },
        getReport: function () {
            this.loading = true
            if (this.param.date === '') {
                this.param.date = moment().format('YYYY-MM-DD')
            }
            ApiService.POST(ApiRoutes.Report + '/stockSummary', this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.data = res.data;
                    this.companySales = res.companySales;
                    this.companyPaid = res.companyPaid;
                    this.posSales = res.posSales;
                    this.expenses = res.expenses;
                    this.assetTransfer = res.assetTransfer;
                    this.productSales = res.productSales;
                    this.total = res.total;
                }
            });
        },
        downloadPdf: function () {
            this.loadingFile = true
            ApiService.DOWNLOAD(ApiRoutes.Report + '/stockSummary/export/pdf', this.param,'',(res) => {
                this.loadingFile = false
                let blob = new Blob([res], {type: 'pdf'});
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'StockSummary.pdf';
                link.click();
            });
        }
    },
    mounted() {
        setTimeout(() => {
            $('.date').flatpickr({
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d",
                defaultDate: 'today',
                mode: 'range',
                onChange: (dateStr, date) => {
                    this.param.date = date
                }
            })
        }, 1000)
        $('#dashboard_bar').text('Sale Stock & Sale Summary')
    }
}
</script>

<style lang="scss" scoped>
.bg-custom {
    background-color: #d7d2d2;
}
table{
    tbody{
        tr{
            border-color: #000000 !important;
           th, td {
               border-color: #000000 !important;
           }
        }
    }
}
</style>
