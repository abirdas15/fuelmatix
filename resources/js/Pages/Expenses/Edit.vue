<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Expense'}">Expense</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Edit</a></li>

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
                                        <label class="form-label">Paid To:</label>
                                        <input type="text" class="form-control" name="paid_to" v-model="param.paid_to">
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
                                        <div class="mt-3" v-if="param.file_path != null"><a :href="param.file_path">{{param.file_name}}</a> </div>
                                    </div>

                                </div>
                                <div class="row" style="text-align: right;">
                                    <div class="mb-3 col-md-6">

                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link :to="{name: 'Expense'}" type="button" class="btn btn-primary">Cancel</router-link>
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
import Swal from 'sweetalert2/dist/sweetalert2.js'
export default {
    data() {
        return {
            param: {},
            loading: false,
            id: '',
            expenseData: [],
            paymentData: [],
            shifts: []
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
        getSingle: function () {
            ApiService.POST(ApiRoutes.ExpenseSingle, {id: this.id, status: this.$route.params.status},res => {
                if (parseInt(res.status) === 200) {
                    this.param = res.data
                    if (this.param.file != null) {
                        this.param.file_name = (' ' + this.param.file).slice(1);
                        this.param.file = ''
                    }
                    setTimeout(() => {
                        $('.date').flatpickr({
                            altInput: true,
                            altFormat: "d/m/Y",
                            dateFormat: "Y-m-d",
                            onChange: (date, dateStr) => {
                                this.param.date = dateStr
                            }
                        })
                    }, 1000)
                }
            });
        },
        save: function () {
            let formData = new FormData();

            // Append the common parameters
            formData.append('id', this.param.id);
            formData.append('date', this.param.date);
            formData.append('shift_sale_id', this.param.shift_sale_id);
            formData.append('category_id',  this.param.category_id);
            formData.append('payment_id',  this.param.payment_id);
            formData.append('amount',  this.param.amount);
            formData.append('remarks',  this.param.remarks);
            formData.append('paid_to',  this.param.paid_to);

            if (this.param.file) {
                formData.append('file',  this.param.file);
            }
            ApiService.ClearErrorHandler();
            this.loading = true
            this.param.status = this.$route.params.status;
            ApiService.POST(ApiRoutes.ExpenseEdit,formData,res => {
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
        this.id = this.$route.params.id
        this.getSingle()
        this.getExpenseCategory()
        this.getPaymentCategory()
    },
    mounted() {
        $('#dashboard_bar').text('Expense Edit')
    }
}
</script>

<style scoped>

</style>
