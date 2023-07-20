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
                            <h4 class="card-title">Shift Sale Edit</h4>
                        </div>
                        <form @submit.prevent="save">
                            <div class="card-body">
                                <div class="process-wrapper">
                                    <div id="progress-bar-container" v-if="listData.length > 0">
                                        <ul>
                                            <li class="step step01" :class="{'active': p.id == product_id}"
                                                v-for="(p, pIndex) in listData" @click="product_id = p.id; productIndex = pIndex; getProductDispenser()">
                                                <div class="step-inner">{{ p.name }}</div>
                                            </li>
                                        </ul>

                                        <div id="line">
                                            <div id="line-progress" :style="{'width': calculateLineProgress() + '%'}"></div>
                                        </div>
                                    </div>
                                    <div class="text-center" v-else>No Product Found</div>

                                    <div id="progress-content-section" v-if="listDispenser">
                                        <div class="section-content discovery active">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title">
                                                        {{ listDispenser.product_name }}</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row align-items-center text-start">
                                                        <div class="col-md-2">
                                                            <label class="form-label">
                                                                <p class="m-0">OIL Stock </p>
                                                            </label>

                                                        </div>
                                                        <div class="mb-3 col-md-3">
                                                            <label>Previous Reading </label>
                                                            <input disabled id="prReading"
                                                                   type="text" class="form-control"
                                                                   v-model="listDispenser.start_reading">
                                                        </div>
                                                        <div class="mb-3 col-md-3">
                                                            <label>Final Reading </label>
                                                            <input id="frReading" @blur="disableInput('frReading')"
                                                                type="text" class="form-control"  @click="enableInput('frReading')"
                                                                v-model="listDispenser.end_reading"
                                                                @input="calculateAmount">
                                                        </div>

                                                        <div class="mb-3 col-md-2">
                                                            <label>Consumption </label>
                                                            <input type="text" class="form-control" id="consumption" disabled
                                                                   v-model="listDispenser.consumption">
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label>Amount </label>
                                                            <input type="text" class="form-control" disabled
                                                                   v-model="listDispenser.amount">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card" v-if="listDispenser.dispensers.length > 0"
                                                 v-for="(d, dIndex) in listDispenser.dispensers">
                                                <div class="card-header">
                                                    <h5 class="card-title">{{ d.dispenser_name }}</h5>
                                                </div>
                                                <div class="card-body" v-if="d.nozzle.length > 0">
                                                    <div class="row align-items-center text-start" v-for="(n, nIndex) in d.nozzle">
                                                        <div class=" col-md-2">
                                                            <label class="form-label">
                                                                <p class="m-0">{{ n.name }}</p>
                                                            </label>
                                                        </div>
                                                        <div class="mb-3 col-md-3">
                                                            <label>Previous Reading </label>
                                                            <input type="text" class="form-control" disabled
                                                                   v-model="n.start_reading">
                                                        </div>
                                                        <div class="mb-3 col-md-3">
                                                            <label>Final Reading </label>
                                                            <input type="text" class="form-control" @blur="disableInput('frReading'+nIndex+dIndex)"
                                                                   v-model="n.end_reading" @click="enableInput('frReading'+nIndex+dIndex)"
                                                                   @input="calculateAmountNozzle(dIndex, nIndex) ">
                                                        </div>

                                                        <div class="mb-3 col-md-2">
                                                            <label>Consumption </label>
                                                            <input type="text" disabled class="form-control"
                                                                   v-model="n.consumption">
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label>Amount </label>
                                                            <input type="text" class="form-control" disabled
                                                                   v-model="n.amount">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center" v-else>Please Select any product</div>
                                </div>
                                <div class="row" style="text-align: right;" v-if="product_id">
                                    <div class="mb-3 col-md-6">

                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link :to="{name: 'ShiftSaleList'}" type="button" class="btn btn-primary">Cancel</router-link>
                                    </div>
                                </div>
                            </div>
                        </form>

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
            listData: [],
            listDispenser: null,
            product_id: '',
            productIndex: 0,
            id: '',
        }
    },
    methods: {
        disableInput: function (id) {
            $('#'+id).prop('readonly', true);
        },
        enableInput: function (id) {
            $('#'+id).prop('readonly', false);
        },
        calculateLineProgress: function () {
            let progress = 100
            let eachProgress = Math.round(progress / (this.listData?.length - 1))
            return (eachProgress * this.productIndex)
        },
        calculateAmount: function () {
            if (this.isNumeric(this.listDispenser.end_reading)) {
                this.listDispenser.consumption = parseFloat(this.listDispenser.start_reading) - parseFloat(this.listDispenser.end_reading)
                this.listDispenser.amount = parseFloat(this.listDispenser.consumption ) * parseFloat(this.listDispenser.selling_price)
            } else {
                this.listDispenser.consumption = 0
                this.listDispenser.amount = 0
            }
        },
        calculateAmountNozzle: function (dIndex, nIndex) {
            if (this.isNumeric(this.listDispenser.dispensers[dIndex].nozzle[nIndex].end_reading)) {
                this.listDispenser.dispensers[dIndex].nozzle[nIndex].consumption = parseFloat(this.listDispenser.dispensers[dIndex].nozzle[nIndex].start_reading) - parseFloat(this.listDispenser.dispensers[dIndex].nozzle[nIndex].end_reading)
                this.listDispenser.dispensers[dIndex].nozzle[nIndex].amount = parseFloat(this.listDispenser.dispensers[dIndex].nozzle[nIndex].consumption) * parseFloat(this.listDispenser.selling_price)
            } else {
                this.listDispenser.dispensers[dIndex].nozzle[nIndex].consumption = 0
                this.listDispenser.dispensers[dIndex].nozzle[nIndex].amount = 0
            }
        },
        getProduct: function () {
            ApiService.POST(ApiRoutes.ProductList, {limit: 5000, page: 1, order_mode: 'ASC'}, res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.listData = res.data.data;
                    this.listData.map((v, i) => {
                        if (this.product_id == v.id) {
                            this.productIndex = i
                        }
                    })
                    this.calculateLineProgress()
                }
            });
        },
        getSingle: function() {
            ApiService.POST(ApiRoutes.ShiftSaleSingle, {id: this.id}, res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.listDispenser = res.data;
                    this.product_id = this.listDispenser.product_id
                    this.getProduct()
                }
            });
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            ApiService.POST(ApiRoutes.ShiftSaleAdd, this.listDispenser, res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$toast.success(res.message);
                    this.$router.push({
                        name: 'ShiftSaleList'
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
    },
    mounted() {
        $('#dashboard_bar').text('Shift Sale Start')
    }
}
</script>

<style scoped>

</style>
