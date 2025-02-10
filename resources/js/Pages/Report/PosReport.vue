<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Home</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Pos Report</a></li>
                </ol>
            </div>
            <!-- row -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card ">
                        <div class="card-header bg-secondary">
                            <h4 class="card-title">Pos Report</h4>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-xl-3 mb-3 form-group">
                                    <div class="example">
                                        <p class="mb-1">Select Date</p>
                                        <input class="form-control input-daterange-datepicker date" name="start_date" type="text">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-xl-3 mb-3">
                                    <button v-if="!loading" type="button" class="btn btn-rounded btn-white border" @click="fetchPosReport">
                                        <span class="btn-icon-start text-info"><i class="fa fa-filter color-white"></i></span>Filter
                                    </button>
                                    <button v-if="loading" type="button" class="btn btn-rounded btn-white border">
                                        <span class="btn-icon-start text-info"><i class="fa fa-filter color-white"></i></span>Filter...
                                    </button>
                                </div>
                                <div class="col-xl-3 mb-3">
                                    <button class="btn btn-primary" v-if="!loadingFile" @click="downloadPdf"><i class="fa fa-print" aria-hidden="true"></i>&nbsp;Print</button>
                                    <button class="btn btn-primary" v-if="loadingFile"><i class="fa fa-print" aria-hidden="true"></i>&nbsp;Print...</button>
                                </div>
                            </div>

                            <div class=" mt-4" v-if="summary.length > 0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-responsive-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Card Name</th>
                                                <th>Voucher Number</th>
                                                <th>Card Number/Transaction ID</th>
                                                <th>Invoice Number</th>
                                                <th>Product Name</th>
                                                <th>Quantity</th>
                                                <th>Price</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="each in summary">
                                                <td v-text="each.date"></td>
                                                <td v-text="each.category_name"></td>
                                                <td v-text="each.voucher_number"></td>
                                                <td v-text="each.card_number"></td>
                                                <td v-text="each.invoice_number"></td>
                                                <td v-text="each.product_name"></td>
                                                <td v-text="each.quantity"></td>
                                                <td v-text="each.price"></td>
                                                <td v-text="each.subtotal"></td>
                                            </tr>
                                        </tbody>
                                        <tfoot v-if="summary.length > 0">
                                            <tr>
                                                <th colspan="5">Total</th>
                                                <th>{{ total.quantity }}</th>
                                                <th></th>
                                                <th>{{ total.amount }}</th>
                                            </tr>
                                        </tfoot>
                                        <tbody v-if="summary.length === 0">
                                            <tr>
                                                <td colspan="6" class="text-center">No data found.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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
            companies: [],
            param: {
                start_date: '',
                end_date: '',
                company_id: ''
            },
            loading: false,
            summary: [],
            products: [],
            total: {

            },
            loadingFile: false,
        }
    },
    methods: {

        downloadPdf: function () {
            this.loadingFile = true
            ApiService.ClearErrorHandler();
            ApiService.DOWNLOAD(ApiRoutes.Report + '/pos-machine/export/pdf', this.param, '', (res) => {
                this.loadingFile = false
                let blob = new Blob([res], {type: 'pdf'});
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'Pos Report.pdf';
                link.click();
            });
        },
        fetchPosReport: function () {
            this.loading = true;
            ApiService.POST(ApiRoutes.Report + '/pos-machine', this.param, (res) => {
                this.loading = false;
                if (parseInt(res.status) === 200) {
                    this.summary = res.data;
                    this.total = res.total;
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
    },
    created() {
        $('#dashboard_bar').text('Pos Report')
        setTimeout(() => {
            $('.date').flatpickr({
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d",
                mode: 'range',
                onChange: (date, dateStr) => {
                    let dateArr = dateStr.split('to')
                    if (dateArr.length === 2) {
                        this.param.start_date = dateArr[0]
                        this.param.end_date = dateArr[1]
                    } else {
                        this.param.start_date = dateStr;
                        this.param.end_date = dateStr;
                    }
                }
            })
        }, 1000);
    }
}
</script>

<style scoped>

</style>
