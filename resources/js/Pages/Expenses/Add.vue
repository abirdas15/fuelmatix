<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Expense'}">Expense</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Add</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Expense</h4>
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
                                        <label class="form-label">Expense:</label>
                                        <select class="form-control" name="category_id" id="category_id"  v-model="param.category_id">
                                            <option value="">Select Expense</option>
                                            <option v-for="d in expenseData" :value="d.id">{{d.name}}</option>
                                        </select>
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
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Payment:</label>
                                        <select class="form-control" name="payment_id" id="payment_id"  v-model="param.payment_id">
                                            <option value="">Select Payment</option>
                                            <option v-for="d in paymentData" :value="d.id">{{d.name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Shift:</label>
                                        <select class="form-control" name="shift_id" id="payment_id"  v-model="param.shift_sale_id">
                                            <option value="">Select Shift</option>
                                            <option v-for="d in shifts" :value="d.id">{{d.name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="mb-3 form-group col-md-6">
                                        <div class="input-group">
                                            <div class="form-file mt-5">
                                                <input type="file" class="form-file-input form-control"  @change="onFileChange" name="sound_file">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="row" style="text-align: right;">
                                    <div class="mb-3 col-md-6">

                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link :to="{name: 'Expense'}" type="button" class="btn btn-danger">Cancel</router-link>
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
import moment from "moment";
import Swal from 'sweetalert2/dist/sweetalert2.js'
export default {
    data() {
        return {
            param: {
                category_id: '',
                amount: '',
                remarks: '',
                payment_id: '',
                file: '',
                date: moment().format('YYYY-MM-DD'),
                shift_sale_id: ''
            },
            loading: false,
            expenseData: [],
            paymentData: [],
            shifts: [],
        }
    },
    watch:{
        'param.date': function() {
            this.fetchShift();
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
        getExpenseCategory: function () {
            ApiService.POST(ApiRoutes.CategoryParent, {type: 'expenses'},res => {
                if (parseInt(res.status) === 200) {
                    this.expenseData = res.data;
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
        getPaymentCategory: function () {
            ApiService.POST(ApiRoutes.CategoryParent, {type: 'assets'},res => {
                if (parseInt(res.status) === 200) {
                    this.paymentData = res.data;
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
        onFileChange(e) {
            let files = e.target.files || e.dataTransfer.files;
            if (!files.length)
                return;
            this.param.file = files[0];
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            let formData = new FormData()
            formData.append('category_id', this.param.category_id)
            formData.append('payment_id', this.param.payment_id)
            formData.append('amount', this.param.amount)
            formData.append('remarks', this.param.remarks)
            formData.append('file', this.param.file)
            formData.append('date', this.param.date)
            formData.append('shift_sale_id', this.param.shift_sale_id)
            ApiService.POST(ApiRoutes.ExpenseAdd, formData, res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$router.push({
                        name: 'Expense'
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
    },
    created() {
        this.getPaymentCategory()
        this.getExpenseCategory()
    },
    mounted() {
        $('#dashboard_bar').text('Expense Add')
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
        }, 1000);
        this.fetchShift();
    }
}
</script>

<style scoped>

</style>
