<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dispenser'}">Dispenser</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Add</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Dispenser</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form @submit.prevent="save">
                                <div class="row">
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Dispenser Name:</label>
                                        <input type="text" class="form-control" name="dispenser_name" v-model="param.dispenser_name">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Dispenser Brand:</label>
                                        <input type="text" class="form-control" name="brand" v-model="param.brand">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Select Product:</label>
                                        <select class="form-control" name="product_id" id="product_id"  v-model="param.product_id">
                                            <option value="">Select Product</option>
                                            <option v-for="d in listData" :value="d.id">{{d.name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Select Tank:</label>
                                        <select class="form-control" name="tank_id" id="tank_id"  v-model="param.tank_id">
                                            <option value="">Select Tank</option>
                                            <option v-for="d in listDataTank" :value="d.id">{{d.tank_name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Serial:</label>
                                        <input type="text" class="form-control" name="serial" v-model="param.serial">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Opening Stock:</label>
                                        <input type="number" class="form-control" name="opening_stock" v-model="param.opening_stock">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="row" style="text-align: right;">
                                    <div class="mb-3 col-md-6">

                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link :to="{name: 'Dispenser'}" type="button" class="btn btn-primary">Cancel</router-link>
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
            param: {
                dispenser_name: '',
                brand: '',
                serial: '',
                product_id: '',
                tank_id: '',
                opening_stock: ''
            },
            loading: false,
            listData: [],
            listDataTank: [],
        }
    },
    watch: {
        'param.product_id': function () {
            this.getTank()
        }
    },
    methods: {
        getProduct: function () {
            ApiService.POST(ApiRoutes.ProductList, {limit: 5000, page: 1},res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.listData = res.data.data;
                }
            });
        },
        getTank: function () {
            ApiService.POST(ApiRoutes.ProductTank, {product_id: this.param.product_id},res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.listDataTank = res.data;
                }
            });
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            ApiService.POST(ApiRoutes.DispenserAdd, this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$router.push({
                        name: 'Dispenser'
                    })
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
    },
    created() {
        this.getProduct()
    },
    mounted() {
        $('#dashboard_bar').text('Dispenser Add')
    }
}
</script>

<style scoped>

</style>
