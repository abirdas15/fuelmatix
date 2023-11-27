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
                                    <div class="col-sm-6">
                                        <div class="form-group mb-3">
                                            <label class="">Purpose</label>
                                            <input type="text" class="form-control" name="purpose" v-model="param.purpose">
                                            <div class="invalid-feedback"></div>
                                        </div>

                                    </div>
                                    <div class="col-sm-6">
                                        <div class="row form-group mb-3">
                                            <label >Product</label>
                                            <select class="form-control form-select" name="product_id" v-model="param.product_id">
                                                <option v-for="p in products" :value="p.id">{{p.name}}</option>
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="row justify-content-between">
                                        <div class="col-sm-6 box-mula" style="width:49%" v-if="param.nozzles.length > 0">
                                            <div class="" >
                                                <h5 class="putkir-futa">Out</h5>
                                                <div class="row mb-3" v-for="n in param.nozzles">
                                                    <label  class="col-sm-3 col-form-label">{{n.name}}</label>
                                                    <div class="col-sm-7 form-group">
                                                        <input type="number" class="form-control" v-model="n.quantity" @input="calculateLoss()">
                                                        <div class="invalid-feedback"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 box-mula" style="width:49%" v-if="param.tank.id != ''">
                                            <div class="" >
                                                <h5 class="putkir-futa">In</h5>
                                                <div class="row mb-3">
                                                    <label  class="col-sm-3 col-form-label">{{param.tank.name}}</label>
                                                    <div class="col-sm-7 form-group">
                                                        <input type="number" class="form-control" v-model="param.tank.quantity"  @input="calculateLoss()">
                                                        <div class="invalid-feedback"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="col-md-6"></div>
                                    <div class="col-md-6">
                                        <div class="row mb-3">
                                            <label  class="col-sm-3 col-form-label text-end"><strong>Loss</strong></label>
                                            <div class="col-sm-7 form-group">
                                                <input type="text" class="form-control" name="loss_quantity" disabled v-model="param.loss_quantity">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" style="text-align: right;">
                                    <div class="mb-3 col-md-11">
                                        <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link :to="{name: 'adjustment'}" type="button" class="btn btn-primary">Cancel</router-link>
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
                loss_quantity: '',
                nozzles: [],
                tank: {
                    id: '',
                    name: '',
                    quantity: '',
                }
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
        calculateLoss: function () {
            let plus = 0
            this.param.nozzles.map(v => {
                plus+= parseInt(v.quantity)
            })
            if (!isNaN(parseInt(plus) - parseInt(this.param.tank.quantity))) {
                this.param.loss_quantity = parseInt(plus) - parseInt(this.param.tank.quantity)
            } else {
                this.param.loss_quantity = plus
            }
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
                        this.param.nozzles.push({id: v.id, quantity: 0, name: v.name})
                    })

                }
            })
        },
        getTank: function () {
            ApiService.POST(ApiRoutes.TankByProduct, {product_id: this.param.product_id}, res => {
                if (parseInt(res.status) === 200) {
                    this.param.tank.id = res.data.id;
                    this.param.tank.quantity = 0;
                    this.param.tank.name = res.data.tank_name;
                }
            })
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            ApiService.POST(ApiRoutes.FuelAdjustment, this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$router.push({
                        name: 'adjustmentView',
                        params: {id: res.adjustment_id}
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
        $('#dashboard_bar').text('Fuel Adjustment')
    }
}
</script>

<style scoped>
.box-mula{
    padding: 10px 30px;
    box-shadow: 0 0 15px 0 #CBC9C8;
    border-radius: 12px;
    margin-bottom: 30px;
    margin-top: 10px;
}
.putkir-futa{
    border-bottom: 1px solid #c1c1c1;
    margin: 10px 0px 15px 0px;
    padding-bottom: 11px;
}
</style>
