<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Product'}">Product</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Edit</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Product</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form @submit.prevent="save">
                                <div class="row">
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Product:</label>
                                        <input type="text" class="form-control" name="name" v-model="param.name">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Product Type:</label>
                                        <select class="form-control wide product-type" name="type_id" v-model="param.type_id">
                                            <option value="">Select Type</option>
                                            <option v-for="t of productType" :value="t.id" :data-id="t.vendor">{{t.name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Buying Price: </label>
                                        <input type="number" class="form-control" name="buying_price" v-model="param.buying_price">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Selling Price: </label>
                                        <input type="number" class="form-control" name="selling_price" v-model="param.selling_price">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Driver Selling Price: </label>
                                        <input type="number" class="form-control" name="selling_price" v-model="param.driver_selling_price">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Opening Stock:</label>
                                        <input type="number" class="form-control" name="opening_stock" v-model="param.opening_stock">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6" v-if="is_vendor">
                                        <label class="form-label">Vendor:</label>
                                        <select class="form-control wide" name="vendor_id" v-model="param.vendor_id">
                                            <option value="">Select Type</option>
                                            <option v-for="t of vendors" :value="t.id">{{t.name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="row" style="text-align: right;">
                                    <div class="mb-3 col-md-6">

                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link :to="{name: 'Product'}" type="button" class="btn btn-danger">Cancel</router-link>
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
import ApiService from "../../../Services/ApiService";
import ApiRoutes from "../../../Services/ApiRoutes";
export default {
    data() {
        return {
            param: {},
            loading: false,
            productType: [],
            id: '',
            is_vendor: false,
            vendors: []
        }
    },
    methods: {
        getAllVendor: function() {
            ApiService.POST(ApiRoutes.VendorList, {limit: 500},res => {
                if (parseInt(res.status) === 200) {
                    this.vendors = res.data.data
                }
            });
        },
        getSingle: function () {
            ApiService.POST(ApiRoutes.ProductSingle, {id: this.id},res => {
                if (parseInt(res.status) === 200) {
                    this.param = res.data
                    setTimeout(() => {
                        let _this = this;
                        let vendor = parseInt($('.product-type').find(':selected').attr('data-id'));
                        if (vendor === 1) {
                            _this.is_vendor = true;
                        } else {
                            _this.is_vendor = false;
                        }
                        $('.product-type').change(function() {
                            let vendor = parseInt($(this).find(':selected').attr('data-id'));
                            if (vendor === 1) {
                                _this.is_vendor = true;
                            } else {
                                _this.is_vendor = false;
                            }
                        });
                    }, 500)
                }
            });
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            ApiService.POST(ApiRoutes.ProductEdit, this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$router.push({
                        name: 'Product'
                    })
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        getProductType: function () {
            ApiService.POST(ApiRoutes.ProductType, {},res => {
                if (parseInt(res.status) === 200) {
                    this.productType = res.data
                }
            });
        },
    },
    created() {
        this.id = this.$route.params.id
        this.getProductType()
        this.getSingle()
        this.getAllVendor();
    },
    mounted() {
        $('#dashboard_bar').text('Product Edit')
    }
}
</script>

<style scoped>

</style>
