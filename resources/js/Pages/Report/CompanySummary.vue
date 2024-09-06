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
                            <div class="row align-items-end">
                                <div class="col-xl-3 mb-3 form-group">
                                    <div class="example">
                                        <p class="mb-1">Select Date</p>
                                        <input class="form-control input-daterange-datepicker date" name="start_date" type="text">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-xl-3 mb-3 form-group">
                                    <div class="example">
                                        <p class="mb-1">Company</p>
                                        <select class="form-control" v-model="param.company_id" name="company_id">
                                            <option value="">Choose...</option>
                                            <option v-for="each in companies" :value="each.id"  v-text="each.name"></option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-xl-3 mb-3">
                                    <button v-if="!loading" type="button" class="btn btn-rounded btn-white border" @click="fetchCompanySummary">
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
                                            <th>Company Name</th>
                                            <template v-for="each in products">
                                                <th class="text-end" v-text="each.name"></th>
                                            </template>
                                            <th class="text-center">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="each in summary">
                                                <td v-text="each.company_name"></td>
                                                <template v-for="product in products">
                                                    <!-- Find the matching product for the company and display the amount -->
                                                    <td class="text-end">
                                                        {{ each.products.find(p => p.name === product.name)?.amount || '' }}
                                                    </td>
                                                </template>
                                                <td class="text-center">
                                                    <router-link :to="{name: 'CompanyBillDetails', params: { id: each.account_id, start_date: param.start_date.trim(), end_date: param.end_date.trim() }}" class=" btn btn-primary shadow btn-xs sharp me-1">
                                                        <i class="fas fa-eye"></i>
                                                    </router-link>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot v-if="summary.length > 0">
                                            <tr>
                                                <th>Total</th>
                                                <template v-for="amount in total">
                                                    <th class="text-end" v-text="amount"></th>
                                                </template>
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
        }
    },
    methods: {
        downloadPdf: function () {
            this.loadingFile = true
            ApiService.ClearErrorHandler();
            ApiService.DOWNLOAD(ApiRoutes.Report + '/company/summary/export/pdf', this.param, '', (res) => {
                this.loadingFile = false
                let blob = new Blob([res], {type: 'pdf'});
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'CompanySummary.pdf';
                link.click();
            });
        },
        fetchCompanySummary: function () {
            this.loading = true;
            ApiService.POST(ApiRoutes.Report + '/company/summary', this.param, (res) => {
                this.loading = false;
                if (parseInt(res.status) === 200) {
                    this.summary = res.data;
                    this.products = res.products;
                    this.total = res.total;
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
        $('#dashboard_bar').text('Company Bill')
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
                    }
                }
            })
        }, 1000);
        this.fetchCreditCompany();
    }
}
</script>

<style scoped>

</style>
