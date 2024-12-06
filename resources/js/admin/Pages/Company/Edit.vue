<template>
    <main class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Home</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page"><router-link :to="{name: 'Company'}">Company</router-link></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
        <form @submit.prevent="updateCompany">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row mb-3">
                                <label class="col-md-4">Name</label>
                                <div class="col-md-8">
                                    <input type="text" v-model="params.name" name="name" class="form-control">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-md-4">Email</label>
                                <div class="col-md-8">
                                    <input type="text" v-model="params.email" name="email" class="form-control">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-md-4">Phone Number</label>
                                <div class="col-md-8">
                                    <input type="text" v-model="params.phone_number" name="phone_number" class="form-control">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-md-4">Address</label>
                                <div class="col-md-8">
                                    <textarea class="form-control" v-model="params.address" name="address" rows="5"></textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-md-4">Sales Mismatch Allow</label>
                                <div class="col-md-8">
                                    <input type="text" v-model="params.sale_mismatch_allow" name="sale_mismatch_allow" class="form-control">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-md-4">Expense Approve</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" v-model="params.expense_approve" name="expense_approve">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-md-4">Currency Precision</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" v-model="params.currency_precision" name="currency_precision">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-md-4" for="headerText">Quantity Precision</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" v-model="params.quantity_precision" name="quantity_precision">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-md-4" for="headerText">Header Text</label>
                                <div class="col-md-8">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="headerText" v-model="params.header_text">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-md-4" for="footerText">Footer Text</label>
                                <div class="col-md-8">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="footerText" v-model="params.footer_text">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-md-4" for="voucherCheck">Voucher Check</label>
                                <div class="col-md-8">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="voucherCheck" v-model="params.voucher_check">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-md-4" for="voucherCheck">Invoice QR Code</label>
                                <div class="col-md-8">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="voucherCheck" v-model="params.invoice_qr_code">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-2">
                    <button v-if="!loading" type="submit" class="btn btn-primary w-100">Save</button>
                    <Button v-if="loading" class="w-100 btn btn-primary" type="button">
                        <div class="spinner-border spinner-border-sm"></div>
                    </Button>
                </div>
            </div>
        </form>
    </main>
</template>

<script>
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";

export default {
    data() {
        return {
            params: {

            },
            loading: false,
        }
    },
    created() {
        this.fetchSingleCompany()
    },
    methods: {
        fetchSingleCompany: function() {
            ApiService.POST(ApiRoutes.Company + '/single', {id: this.$route.params.id}, (res) => {
                if (parseInt(res.status) === 200) {
                    this.params = res.company;
                }
            });
        },

        updateCompany: function() {
            this.loading = true;
            ApiService.POST(ApiRoutes.Company + '/update', this.params, (res) => {
                this.loading = false;
                if (parseInt(res.status) === 500) {
                    ApiService.ErrorHandler(res.errors);
                } else if (parseInt(res.status) === 300) {
                    this.$toast.warning(res.message);
                } else {
                    this.$toast.success(res.message);
                }
            });
        }
    }
}

</script>

<style scoped>

</style>
