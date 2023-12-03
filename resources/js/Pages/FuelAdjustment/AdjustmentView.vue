<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Fuel Adjustment View</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Fuel Adjustment View</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label class="fw-bold">Purpose</label>
                                        <div>{{param.purpose}}</div>
                                    </div>

                                </div>
                                <div class="col-sm-6">
                                    <div class="row form-group mb-3">
                                        <label class="fw-bold">Product</label>
                                        <div>{{param.product_name}}</div>
                                    </div>
                                </div>
                                <div class="row justify-content-between">
                                    <div class="col-sm-6 box-mula" style="width:49%" v-if="param.nozzles != undefined && param.nozzles.length > 0">
                                        <div class="" >
                                            <h5 class="putkir-futa">Out</h5>
                                            <div class="row mb-3 align-items-center" v-for="n in param.nozzles">
                                                <label  class="col-sm-3 col-form-label fw-bold">{{n.name}}</label>
                                                <div class="col-sm-3">{{n.quantity}}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 box-mula" style="width:49%" v-if="param.tank != undefined && param.tank.id != ''">
                                        <div class="" >
                                            <h5 class="putkir-futa">In</h5>
                                            <div class="row mb-3 align-items-center">
                                                <label  class="col-sm-3 col-form-label fw-bold">{{param.tank.name}}</label>
                                                <div class="col-sm-7 form-group">
                                                    <div>{{param.tank.quantity}}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="col-md-6"></div>
                                <div class="col-md-6">
                                    <div class="row mb-3 align-items-center">
                                        <label  class="col-sm-3 col-form-label text-end"><strong>Loss</strong></label>
                                        <div class="col-sm-7 form-group">
                                            <div>{{param.loss_quantity}}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="text-align: right;">
                                <div class="mb-3 col-md-11">
                                    <router-link :to="{name: 'adjustment'}" type="button" class="btn btn-primary">Cancel</router-link>
                                </div>
                            </div>
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
            loading: false,
            id: null,
            param: {},
        }
    },
    watch: {

    },
    methods: {
        getFuelAdjustment: function () {
            ApiService.POST(ApiRoutes.FuelAdjustmentSingle, {id: this.id}, res => {
                if (parseInt(res.status) === 200) {
                    this.param = res.data
                }
            })
        },
    },
    created() {

    },
    mounted() {
        this.id = this.$route.params.id
        this.getFuelAdjustment()
        $('#dashboard_bar').text('Fuel Adjustment View')
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
