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
                            <div class="">
                                <div id="progress-bar-container">
                                    <div class="fs-18">Start Date: {{ shiftSale.start_date_format }}</div>
                                    <div class="fs-18">End Date: {{ shiftSale.end_date_format }}</div>
                                    <div class="fs-18">Product: {{ shiftSale.product_name }}</div>
                                </div>

                                <div id="progress-content-section">

                                    <div class="card" v-for="(tank,tankIndex) in shiftSale.tanks">
                                        <div class="card-header">
                                            <h5 class="card-title w-100">
                                                <div class="row">
                                                    <div class="col-md-6"> Tank: {{ tank.tank_name }}</div>
                                                </div>
                                            </h5>
                                        </div>
                                        <template v-if="parseInt(shiftSale.tank) === 1">
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
                                                            <div v-if="tank.start_reading">{{tank.start_reading}} {{ shiftSale.unit }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <div v-if="tank.tank_refill">{{tank.tank_refill}} {{ shiftSale.unit }}</div>
                                                    </div>
                                                    <div class="mb-3 col-md-2" >
                                                        <div v-if="tank.end_reading">{{tank.end_reading}} {{ shiftSale.unit }}</div>
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <div v-if="tank.adjustment">{{tank.adjustment}} {{ shiftSale.unit }}</div>
                                                    </div>

                                                    <div class="mb-3 col-md-2">
                                                        <div v-if="tank.consumption">{{tank.consumption}} {{ shiftSale.unit }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        <div  v-for="(d, dIndex) in tank.dispensers">
                                            <div class="custom-bg">
                                                <h5 class="card-title">Dispenser: {{ d.dispenser_name }}</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row align-items-center text-start" v-for="(n, nIndex) in d.nozzles">
                                                    <div class=" col-md-4">
                                                        <label class="form-label">
                                                            <p class="m-0">{{ n.nozzle_name }}</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <label class="fw-bold">Start Reading </label>
                                                        <div v-if="n.start_reading">{{n.start_reading}} {{ shiftSale.unit }}</div>
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <label class="fw-bold">End Reading </label>
                                                        <div v-if="n.end_reading">{{n.end_reading}} {{ shiftSale.unit }}</div>
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <label class="fw-bold">Adjustment </label>
                                                        <div v-if="n.adjustment">{{n.adjustment}} {{ shiftSale.unit }}</div>
                                                    </div>

                                                    <div class="mb-3 col-md-2" >
                                                        <label class="fw-bold">Consumption </label>
                                                        <div v-if="n.consumption">{{n.consumption}} {{ shiftSale.unit }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body text-end" v-if="tank.net_profit > 0">
                                            <strong>Net Profit: {{ tank.net_profit }} {{ shiftSale.unit }}</strong>
                                        </div>
                                        <div class="card-body text-end" v-if="tank.net_profit < 0">
                                            <strong>Net Loss: {{ Math.abs(tank.net_profit) }} {{ shiftSale.unit }}</strong>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-7"></div>
                                        <div class="col-sm-5 text-end mb-2">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <td style="font-size: 18px;padding: 0;" class="">Total sale:</td>
                                                    <td style="font-size: 18px;padding: 0;" class="text-end ">{{shiftSale.consumption}} {{ shiftSale.unit }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-size: 18px;padding: 0;" class="">Total amount:</td>
                                                    <td style="font-size: 18px;padding: 0;" class="text-end ">{{shiftSale.amount}} Tk</td>
                                                </tr>
                                                <tr v-for="(category,index) in shiftSale.categories">
                                                    <td style="font-size: 18px;padding: 0;" class="">{{category.name}}:</td>
                                                    <td style="font-size: 18px;padding: 0;" class="text-end ">{{category.amount}} Tk</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="text-align: right;" v-if="id">
                                <div class="mb-3 col-md-6">

                                </div>
                                <div class="mb-3 col-md-6">
                                    <router-link :to="{name: 'ShiftSaleListStart'}" type="button" class="btn btn-danger">Cancel</router-link>
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
