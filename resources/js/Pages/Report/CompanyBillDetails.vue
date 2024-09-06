<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Home</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Bill Summary</a></li>
                </ol>
            </div>
            <!-- row -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card ">
                        <div class="card-header bg-secondary">
                            <h4 class="card-title">Bill Summary</h4>
                        </div>
                        <div class="card-body">
                            <div class="col-xl-3 mb-3">
                                <button class="btn btn-primary" v-if="!loadingFile" @click="downloadPdf"><i class="fa fa-print" aria-hidden="true"></i>&nbsp;Print</button>
                                <button class="btn btn-primary" v-if="loadingFile"><i class="fa fa-print" aria-hidden="true"></i>&nbsp;Print...</button>
                            </div>
                            <div class=" mt-4" v-if="summary.length > 0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-responsive-sm">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">Car Number</th>
                                                <template v-for="each in products">
                                                    <th class="text-center" colspan="3" v-text="each.name"></th>
                                                </template>
                                            </tr>
                                            <tr>
                                                <template v-for="each in products">
                                                    <th class="text-center">Quantity</th>
                                                    <th class="text-end">Unit Price</th>
                                                    <th class="text-end">Amount</th>
                                                </template>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="each in summary">
                                            <td v-text="each.car_number"></td>
                                            <template v-for="product in products">
                                                <!-- Find the matching product for the company and display the amount -->
                                                <td class="text-center">
                                                    {{ each.products.find(p => p.name === product.name)?.quantity || '' }}
                                                </td>
                                                <td class="text-end">
                                                    {{ each.products.find(p => p.name === product.name)?.unit_price || '' }}
                                                </td>
                                                <td class="text-end">
                                                    {{ each.products.find(p => p.name === product.name)?.amount || '' }}
                                                </td>
                                            </template>
                                        </tr>
                                        </tbody>
                                        <tfoot v-if="summary.length > 0">
                                            <tr>
                                                <th>Total</th>
                                                <template v-for="amount in total">
                                                    <th colspan="3" class="text-end" v-text="amount"></th>
                                                </template>
                                            </tr>
                                            <tr>
                                                <th :colspan="products.length * 3">Grand Total</th>
                                                <th class="text-end">{{ grandTotal }}</th>
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
            total: [],
            loadingFile: false,
            grandTotal: '',
        }
    },
    methods: {
        downloadPdf: function () {
            this.loadingFile = true
            ApiService.ClearErrorHandler();
            ApiService.DOWNLOAD(ApiRoutes.Report + '/company/summary/details/export/pdf', this.param, '', (res) => {
                this.loadingFile = false
                let blob = new Blob([res], {type: 'pdf'});
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'CompanySummary.pdf';
                link.click();
            });
        },
        fetchBillSummaryDetails: function () {
            this.loading = true;
            ApiService.POST(ApiRoutes.Report + '/company/summary/details', this.param, (res) => {
                this.loading = false;
                if (parseInt(res.status) === 200) {
                    this.summary = res.data;
                    this.products = res.products;
                    this.total = res.total;
                    this.grandTotal = res.grandTotal;
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        fetchCreditCompany: function () {
            ApiService.POST(ApiRoutes.CreditCompanyList, {limit: 500, parent: 1}, (res) => {
                if (parseInt(res.status) === 200) {
                    this.companies = res.data.data;
                }
            });
        }
    },
    created() {
        this.param.start_date = this.$route.params.start_date;
        this.param.end_date = this.$route.params.end_date;
        this.param.company_id = this.$route.params.id;
        $('#dashboard_bar').text('Bill Summary')
        this.fetchBillSummaryDetails();
    }
}
</script>

<style scoped>

</style>
