<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Staff Loan</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Staff Loan</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form @submit.prevent="save">
                                <div class="row">
                                    <div class="col-6 mb-3 form-group">
                                        <label class="form-label">Date:</label>
                                        <input type="text" class="form-control date bg-white" name="date" v-model="param.date">
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Bank/Cash:</label>
                                        <v-select
                                            class="form-control form-control-sm"
                                            :options="categories"
                                            name="from_category_id"
                                            placeholder="Choose Cash/Bank"
                                            label="name"
                                            v-model="param.from_category_id"
                                            :reduce="(option) => option.id"
                                            :searchable="true"
                                        ></v-select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Staff:</label>
                                        <v-select
                                            class="form-control form-control-sm"
                                            :options="staffs"
                                            placeholder="Choose Staff"
                                            name="to_category_id"
                                            label="name"
                                            v-model="param.to_category_id"
                                            :reduce="(option) => option.id"
                                            :searchable="true"
                                        ></v-select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Amount:</label>
                                        <input type="text" class="form-control" name="amount" v-model="param.amount">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Remarks:</label>
                                        <input type="text" class="form-control" name="remarks" v-model="param.remarks">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="row" style="text-align: right;">
                                    <div class="mb-3 col-md-6">

                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link :to="{name: 'user'}" type="button" class="btn btn-primary">Cancel</router-link>
                                    </div>
                                </div>
                            </form>
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
import moment from "moment/moment";
import Swal from 'sweetalert2/dist/sweetalert2.js'
export default {
    data() {
        return {
            param: {
                from_category_id: '',
                to_category_id: '',
                amount: '',
                remarks: '',
                date: moment().format('YYYY-MM-DD')
            },
            loading: false,
            categories: [],
            staffs: []
        }
    },
    methods: {
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            ApiService.POST(ApiRoutes.StaffLoan + '/save', this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$toast.success(res.message);
                    this.$router.push({
                        name: 'StaffLoanList'
                    })
                } else if (parseInt(res.status) === 300) {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: res.message
                    });
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        getParentCategory() {
            ApiService.POST(ApiRoutes.salaryGetCategory, {type: 'assets', equity: false},res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.categories = res.data
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        getStaffList() {
            ApiService.POST(ApiRoutes.LoanStaffList, {}, (res) => {
                if (parseInt(res.status) === 200) {
                    this.staffs = res.data;
                }
            });
        },
    },
    created() {
        this.getParentCategory();
        this.getStaffList();
    },
    mounted() {
        $('#dashboard_bar').text('Staff Loan');
        setTimeout(() => {
            $('.date').flatpickr({
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d",
                defaultDate: 'today',
                onChange: (date, dateStr) => {
                    this.param.date = dateStr
                }
            })
        }, 1000)
    }
}
</script>

<style scoped>

</style>
