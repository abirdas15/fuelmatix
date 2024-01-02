<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Home</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Expense Report</a></li>
                </ol>
            </div>
            <!-- row -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card ">
                        <div class="card-header bg-secondary">
                            <h4 class="card-title">Expense Report</h4>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-xl-3 mb-3">
                                    <div class="example">
                                        <p class="mb-1">Select Date</p>
                                        <input class="form-control input-daterange-datepicker date" type="text">
                                    </div>
                                </div>
                                <div class="col-xl-3 mb-3">
                                    <div class="example">
                                        <p class="mb-1">Expense Type</p>
                                        <select class="form-control" v-model="param.category_id">
                                            <option selected>Choose...</option>
                                            <option v-for="each in expenseCategories" :value="each.id"  v-text="each.name"></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-3 mb-3">
                                    <div class="example">
                                        <p class="mb-1">Requested by</p>
                                        <select v-model="param.request_by" class="form-control">
                                            <option selected>Choose...</option>
                                            <option v-for="each in users" :value="each.id" v-text="each.name"></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-3 mb-3">
                                    <div class="example">
                                        <p class="mb-1">Approved by</p>
                                        <select v-model="param.approve_by" class="form-control">
                                            <option selected>Choose...</option>
                                            <option v-for="each in users" :value="each.id" v-text="each.name"></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-3 mb-3">
                                    <div class="example">
                                        <p class="mb-1">Payment method</p>
                                        <select class="form-control" v-model="param.payment_category_id">
                                            <option selected>Choose...</option>
                                            <option v-for="each in assetCategories" :value="each.id"  v-text="each.name"></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-xl-3 mb-3">
                                    <button v-if="!loading" type="button" class="btn btn-rounded btn-white border" @click="fetchExpenseReport">
                                        <span class="btn-icon-start text-info"><i class="fa fa-filter color-white"></i></span>Filter
                                    </button>
                                    <button v-if="loading" type="button" class="btn btn-rounded btn-white border">
                                        <span class="btn-icon-start text-info"><i class="fa fa-filter color-white"></i></span>Filter...
                                    </button>
                                </div>
                            </div>

                            <div class=" mt-4">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-responsive-sm">
                                        <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Expense Type</th>
                                            <th>Amount</th>
                                            <th>Description</th>
                                            <th>Requested by</th>
                                            <th>Approved by</th>
                                            <th>Approved Date</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="each in expenses">
                                                <th v-text="each.date"></th>
                                                <td v-text="each.expense_type"></td>
                                                <td v-text="each.amount"></td>
                                                <td v-text="each.remarks"></td>
                                                <td class="color-primary" v-text="each.request_by"></td>
                                                <td v-text="each.approve_by"></td>
                                                <td v-text="each.approve_date"></td>
                                            </tr>
                                            <tr v-if="expenses.length == 0">
                                                <th class="text-center" colspan="7">No data found</th>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2" class="text-end">Total</th>
                                                <th v-text="total"></th>
                                            </tr>
                                        </tfoot>
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
            param: {
                start_date: '',
                end_date: '',
                category_id: '',
                payment_category_id: '',
                approve_by: '',
                request_by: ''
            },
            expenseCategories: [],
            users: [],
            assetCategories: [],
            loading: false,
            expenses: [],
            total: ''
        }
    },
    methods: {
        fetchExpenseReport: function() {
            this.loading = true;
            ApiService.POST(ApiRoutes.ExpenseReport, this.param, (res) => {
                this.loading = false;
                if (parseInt(res.status) === 200) {
                    this.expenses = res.data;
                    this.total = res.total;
                }
            });
        },
        fetchUser: function() {
            ApiService.POST(ApiRoutes.userList, {limit: 500}, (res) => {
                if (parseInt(res.status) === 200) {
                    this.users = res.data.data;
                }
            });
        },
        fetchParentAssetCategory: function() {
            ApiService.POST(ApiRoutes.CategoryParent, {type: 'assets'}, (res) => {
                if (parseInt(res.status) === 200) {
                    this.assetCategories = res.data;
                }
            });
        },
        fetchParentExpenseCategory: function() {
            ApiService.POST(ApiRoutes.CategoryParent, {type: 'expenses'}, (res) => {
                if (parseInt(res.status) === 200) {
                    this.expenseCategories = res.data;
                }
            });
        }
    },
    created() {
        $('#dashboard_bar').text('Expense Report')
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
        this.fetchParentExpenseCategory();
        this.fetchParentAssetCategory();
        this.fetchUser();
    }
}
</script>

<style>

</style>
