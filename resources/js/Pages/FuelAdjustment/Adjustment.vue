<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Fuel Adjustment</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Fuel Adjustment</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form @submit.prevent="save">
                                <div class="row">
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label">Purpose</label>
                                        <div class="col-sm-7 form-group">
                                            <input type="text" class="form-control" name="name" v-model="param.purpose">
                                            <div class="invalid-feedback"></div>
                                        </div>

                                    </div>

                                    <div class="row mb-3">
                                        <label  class="col-sm-3 col-form-label">Product</label>
                                        <div class="col-sm-7 form-group">
                                            <select class="form-control form-select" v-model="param.product_id">
                                                <option v-for="p in products" :value="p.id">{{p.name}}</option>
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <h5 v-if="param.nozzle.length > 0">Out</h5>
                                    <div v-if="param.nozzle.length > 0" class="row mb-3" v-for="n in param.nozzle">
                                        <label  class="col-sm-3 col-form-label">{{n.name}}</label>
                                        <div class="col-sm-7 form-group">
                                            <input type="text" class="form-control" v-model="n.quantity">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <h5 v-if="param.tank.id != undefined">In</h5>
                                    <div v-if="param.tank.id != undefined" class="row mb-3">
                                        <label  class="col-sm-3 col-form-label">{{param.tank.name}}</label>
                                        <div class="col-sm-7 form-group">
                                            <input type="text" class="form-control" v-model="param.tank.quantity">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row mb-3">
                                        <label  class="col-sm-3 col-form-label">Loss</label>
                                        <div class="col-sm-7 form-group">
                                            <input type="text" class="form-control" disabled v-model="param.loss">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" style="text-align: right;">
                                    <div class="mb-3 col-md-6">
                                        <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link :to="{name: 'Bank'}" type="button" class="btn btn-primary">Cancel</router-link>
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
                purpose: '',
                product_id: '',
                loss: '',
                nozzle: [],
                tank: {}
            },
            listParam: {
                limit: 5000,
                page: 1,
            },
            loading: false,
            products: [],
        }
    },
    watch: {
        'param.product_id': function () {
            this.getNozzle()
            this.getTank()
        }
    },
    methods: {
        getName: function () {
            return 'Profit'
        },
        getProduct: function () {
            ApiService.POST(ApiRoutes.ProductList, this.listParam, res => {
                if (parseInt(res.status) === 200) {
                    this.products = res.data.data
                }
            })
        },
        getNozzle: function () {
            ApiService.POST(ApiRoutes.NozzleList, { limit: 5000, page: 1, product_id: this.param.product_id}, res => {
                if (parseInt(res.status) === 200) {
                    res.data.data.map(v => {
                        this.param.nozzle.push({id: v.id, quantity: '', name: v.name})
                    })

                }
            })
        },
        getTank: function () {
            ApiService.POST(ApiRoutes.TankList, { limit: 5000, page: 1, product_id: this.param.product_id}, res => {
                if (parseInt(res.status) === 200) {
                    this.param.tank.id = res.data.id;
                    this.param.tank.quantity = res.data.quantity;
                    this.param.tank.name = res.data.name;
                }
            })
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            ApiService.POST(ApiRoutes.BankAdd, this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {

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
        $('#dashboard_bar').text('Fuel Adjustment')
    }
}
</script>

<style scoped>

</style>
