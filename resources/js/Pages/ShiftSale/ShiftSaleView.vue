<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active">
                        <router-link :to="{name: 'Dashboard'}">Home</router-link>
                    </li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Shift Sale</a></li>
                </ol>
            </div>
            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Shift Sale View</h4>
                        </div>
                        <div class="card-body">
                            <div class="process-wrapper">
                                <div id="progress-bar-container">
                                    <div class="fs-18 fw-bold">Date: {{ shiftSale.date_format }}</div>
                                    <div class="fs-18 fw-bold">Product: {{ shiftSale.product_name }}</div>
                                </div>

                                <div id="progress-content-section">
                                    <div class="section-content discovery active">
                                        <template>
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title">
                                                        {{ shiftSale.product_name }}</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row align-items-center text-start">
                                                        <div class="col-md-2">
                                                            <label class="form-label">
                                                                <p class="m-0">OIL Stock </p>
                                                            </label>

                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="fw-bold">Start Reading </label>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="fw-bold">Tank Refill </label>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="fw-bold">End Reading </label>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="fw-bold">Adjustment </label>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="fw-bold">Consumption </label>
                                                        </div>
                                                        <div class="mb-3 col-md-2 offset-2">
                                                            <div class="input-group">
                                                                <div>{{shiftSale.start_reading}} Liter</div>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <div>{{shiftSale.tank_refill}} Liter</div>
                                                        </div>
                                                        <div class="mb-3 col-md-2" >
                                                            <div>{{shiftSale.end_reading}} Liter</div>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <div>{{shiftSale.adjustment}} Liter</div>
                                                        </div>

                                                        <div class="mb-3 col-md-2">
                                                            <div>{{shiftSale.consumption}} Liter</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        <div class="card" v-if="shiftSale.dispensers.length > 0"
                                             v-for="(d, dIndex) in shiftSale.dispensers">
                                            <div class="card-header">
                                                <h5 class="card-title">{{ d.dispenser_name }}</h5>
                                            </div>
                                            <div class="card-body" v-if="d.nozzle.length > 0">
                                                <div class="row align-items-center text-start" v-for="(n, nIndex) in d.nozzle">
                                                    <div class=" col-md-4">
                                                        <label class="form-label">
                                                            <p class="m-0">{{ n.name }}</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <label class="fw-bold">Start Reading </label>
                                                        <div>{{n.start_reading}} Liter</div>
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <label class="fw-bold">End Reading </label>
                                                        <div>{{n.end_reading}} Liter</div>
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <label class="fw-bold">Adjustment </label>
                                                        <div>{{n.adjustment}} Liter</div>
                                                    </div>

                                                    <div class="mb-3 col-md-2" >
                                                        <label class="fw-bold">Consumption </label>
                                                        <div>{{n.consumption}} Liter</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <template>
                                            <div class="row">
                                                <div class="col-sm-7"></div>
                                                <div class="col-sm-5 text-end mb-2">
                                                    <table class="table">
                                                        <tr>
                                                            <td style="font-size: 18px;padding: 0px;" class="">Total sale:</td>
                                                            <td style="font-size: 18px;padding: 0px;" class="text-end ">{{shiftSale.consumption}} Liter</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-size: 18px;padding: 0px;" class="">Total amount:</td>
                                                            <td style="font-size: 18px;padding: 0px;" class="text-end ">{{shiftSale.amount}} Tk</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
<!--                                            <div class="row" v-if="listDispenser.pos_sale.length > 0">
                                                <div class="col-sm-6">
                                                </div>
                                                <div class="col-sm-6 text-end">
                                                    <h4 style="text-align: left;margin-left: 5rem;">POS Sale</h4>
                                                    <div class="d-flex mb-3 justify-content-end"  v-for="pos in listDispenser.pos_sale">
                                                        <select class="form-control me-3" style="max-width: 210px" v-model="pos.category_id" disabled>
                                                            <option v-for="c in allAmountCategory" :value="c.id">{{c.name}}</option>
                                                        </select>
                                                        <div class="form-group">
                                                            <input class="form-control me-3 text-end"  style="max-width: 210px" type="number" v-model="pos.amount"
                                                                   disabled>
                                                            <div class="invalid-feedback"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-8"></div>
                                                <div class="col-sm-4">
                                                    <table class="table">
                                                        <tr>
                                                            <td style="font-size: 18px;padding: 0px;" class="">Total POS sale:</td>
                                                            <td style="font-size: 18px;padding: 0px;" class="text-end ">{{totalPosSale()}} Tk</td>
                                                        </tr>
                                                        <tr v-if="totalAmount > 0">
                                                            <td style="font-size: 18px;padding: 0px;" class="">Remaining Balance: </td>
                                                            <td style="font-size: 18px;padding: 0px;" class="text-end ">{{totalAmount - totalPosSale()}} Tk</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>-->

                                            <div class="row">
                                                <div class="col-sm-6"></div>
                                                <div class="col-sm-6 text-end">
                                                    <table class="table">
                                                        <tr v-for="(category,index) in shiftSale.categories">
                                                            <td style="font-size: 18px;padding: 0px;" class="text-end">{{category.name}}</td>
                                                            <td style="font-size: 18px;padding: 0px;" class="text-end "> {{category.amount}} Tk</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="col-sm-8"></div>
                                                <div class="col-sm-4">
                                                    <table class="table">
                                                        <tr>
                                                            <td style="font-size: 18px;padding: 0px;" class="">Amount:</td>
                                                            <td style="font-size: 18px;padding: 0px;" class="text-end ">{{shiftSale.amount}} Tk</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </template>

                                    </div>
                                </div>
                            </div>
                            <div class="row" style="text-align: right;" v-if="id">
                                <div class="mb-3 col-md-6">

                                </div>
                                <div class="mb-3 col-md-6">
                                    <router-link :to="{name: 'ShiftSaleList'}" type="button" class="btn btn-danger">Cancel</router-link>
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
            shiftSale: {},
            id: '',
        }
    },
    watch: {

    },
    methods: {
        getShiftSale() {
            ApiService.POST(ApiRoutes.ShiftSaleSingle, {id: this.id}, res => {
                if (parseInt(res.status) === 200) {
                    this.shiftSale =  res.data;
                }
            });
        },
    },
    created() {
    },
    mounted() {
        this.id = this.$route.params.id
        this.getShiftSale()
        $('#dashboard_bar').text('Shift Sale View')
    }
}
</script>

<style scoped>
.input-group-text{
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border: 1px solid #c3bfbf;
    padding: 16.5px 15px;
}
.input-group-append {
    width: 25%;
}
@media only screen and (max-width: 1366px) {
    .input-group-text{
        padding: 10.5px 15px;
    }
}
.input-group-append {
    width: 25%;
}
</style>
