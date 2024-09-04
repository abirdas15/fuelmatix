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
                                        <label class="form-label">Shift:</label>
                                        <select class="form-control" name="shift_id" id="payment_id"  v-model="param.shift_sale_id">
                                            <option value="">Select Shift</option>
                                            <option v-for="d in shifts" :value="d.id">{{d.name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="row">
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th style="width: 20%">Expense Category</th>
                                                <th style="width: 15%">Amount</th>
                                                <th style="width: 20%">Payment Category</th>
                                                <th style="width: 15%">Paid To</th>
                                                <th style="width: 15%">Remarks</th>
                                                <th style="width: 15%">File</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(each,index) in param.expense">
                                                    <td>
                                                        <div class="form-group">
                                                            <select class="form-control" :name="'expense.' + index + '.category_id'" id="category_id"  v-model="each.category_id">
                                                                <option value="">Select Expense</option>
                                                                <option v-for="d in expenseData" :value="d.id">{{d.name}}</option>
                                                            </select>
                                                            <div class="invalid-feedback"></div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" :name="'expense.' + index + '.amount'" v-model="each.amount">
                                                            <div class="invalid-feedback"></div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-group">
                                                            <select class="form-control" :name="'expense.' + index + '.payment_id'" id="payment_id"  v-model="each.payment_id">
                                                                <option value="">Select Payment</option>
                                                                <option v-for="d in paymentData" :value="d.id">{{d.name}}</option>
                                                            </select>
                                                            <div class="invalid-feedback"></div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="paid_to" v-model="each.paid_to">
                                                            <div class="invalid-feedback"></div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="remarks" v-model="each.remarks">
                                                            <div class="invalid-feedback"></div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-group">
                                                            <input type="file" class="form-file-input"  @change="onFileChange($event, index)" name="sound_file">
                                                            <div class="invalid-feedback"></div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <button @click="addExpense" v-if="index === 0" type="button" class="btn btn-info">+</button>
                                                        <button @click="removeExpense(index)" v-if="index !== 0" type="button" class="btn btn-danger">x</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="row" style="text-align: right;">
                                    <div class="mb-3 col-md-6"></div>
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
                date: moment().format('YYYY-MM-DD'),
                shift_sale_id: '',
                expense: [
                    {
                        category_id: '',
                        amount: '',
                        payment_id: '',
                        file: '',
                        paid_to: '',
                        remarks: ''
                    }
                ]
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
        removeExpense: function(index) {
            this.param.expense.splice(index, 1);
        },
        addExpense: function() {
            this.param.expense.push({
                category_id: '',
                amount: '',
                payment_id: '',
                file: '',
                remarks: '',
                paid_to: '',
            });
        },
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
        onFileChange(e, index) {
            let files = e.target.files || e.dataTransfer.files;
            if (!files.length)
                return;
            this.param.expense[index]['file'] = files[0];
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            let formData = new FormData();

            // Append the common parameters
            formData.append('date', this.param.date);
            formData.append('shift_sale_id', this.param.shift_sale_id);

            // Loop through each expense item and append its fields to the formData
            this.param.expense.forEach((expense, index) => {
                formData.append(`expense[${index}][category_id]`, expense.category_id);
                formData.append(`expense[${index}][payment_id]`, expense.payment_id);
                formData.append(`expense[${index}][amount]`, expense.amount);
                formData.append(`expense[${index}][remarks]`, expense.remarks);
                formData.append(`expense[${index}][paid_to]`, expense.paid_to);

                // If you have a file, append it as well, ensuring the file is not empty or undefined
                if (expense.file) {
                    formData.append(`expense[${index}][file]`, expense.file);
                }
            });
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
