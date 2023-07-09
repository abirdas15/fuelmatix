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
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Expense:</label>
                                        <select class="form-control" name="category_id" id="category_id"  v-model="param.category_id">
                                            <option value="">Select Expense</option>
                                            <option v-for="d in expenseData" :value="d.id">{{d.category}}</option>
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
                                            <option v-for="d in paymentData" :value="d.id">{{d.category}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="mb-3 form-group col-md-6">
                                        <div class="input-group">
                                            <div class="form-file">
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
export default {
    data() {
        return {
            param: {},
            loading: false,
            id: '',
            expenseData: [],
            paymentData: [],
        }
    },
    methods: {
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
            ApiService.POST(ApiRoutes.ExpenseSingle, {id: this.id},res => {
                if (parseInt(res.status) === 200) {
                    this.param = res.data
                    if (this.param.file != null) {
                        this.param.file_name = (' ' + this.param.file).slice(1);
                        this.param.file = ''
                    }
                }
            });
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            ApiService.POST(ApiRoutes.ExpenseEdit, this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$router.push({
                        name: 'Expense'
                    })
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
