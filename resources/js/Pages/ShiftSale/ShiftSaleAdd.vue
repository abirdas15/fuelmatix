<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
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
                            <h4 class="card-title">Shift Sale Start</h4>
                        </div>
                        <form @submit.prevent="save">
                            <div class="card-body">
                                <div class="process-wrapper">
                                    <div id="progress-bar-container" v-if="listData.length > 0">
                                        <ul>
                                            <li class="step step01" :class="{'active': p.id == product_id}"
                                                v-for="p in listData" @click="product_id = p.id; getProductDispenser()">
                                                <div class="step-inner">{{ p.name }}</div>
                                            </li>
                                        </ul>

                                        <div id="line">
                                            <div id="line-progress"></div>
                                        </div>
                                    </div>
                                    <div class="text-center" v-else>No Product Found</div>

                                    <div id="progress-content-section" v-if="listDispenser">
                                        <div class="section-content discovery active">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title">
                                                        {{ listDispenser.shift_sale.product_name }}</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label">
                                                                <p>Oll Stock </p>
                                                            </label>

                                                        </div>
                                                        <div class="mb-3 col-md-3">
                                                            <input :disabled="listDispenser.shift_sale.status == 'end'"
                                                                   type="text" class="form-control"
                                                                   v-model="listDispenser.shift_sale.start_reading"
                                                                   @input="listDispenser.shift_sale.status == 'end' ? calculateAmount() : ''">
                                                        </div>
                                                        <div class="mb-3 col-md-3">
                                                            <input
                                                                :disabled="listDispenser.shift_sale.status == 'start'"
                                                                type="text" class="form-control"
                                                                v-model="listDispenser.shift_sale.end_reading"
                                                                @input="listDispenser.shift_sale.status == 'end' ? calculateAmount() : ''">
                                                        </div>

                                                        <div class="mb-3 col-md-2">
                                                            <input type="text" class="form-control"
                                                                   v-model="listDispenser.shift_sale.consumption">
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <input type="text" class="form-control" disabled
                                                                   v-model="listDispenser.shift_sale.amount">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card" v-if="listDispenser.summary.length > 0"
                                                 v-for="(d, dIndex) in listDispenser.summary">
                                                <div class="card-header">
                                                    <h5 class="card-title">{{ d.dispenser_name }}</h5>
                                                </div>
                                                <div class="card-body" v-if="d.nozzle.length > 0">
                                                    <div class="row" v-for="(n, nIndex) in d.nozzle">
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label">
                                                                <p>{{ n.name }}</p>
                                                            </label>
                                                        </div>
                                                        <div class="mb-3 col-md-3">
                                                            <input type="text" class="form-control"
                                                                   :disabled="listDispenser.shift_sale.status == 'end'"
                                                                   v-model="n.start_reading"
                                                                   @input="listDispenser.shift_sale.status == 'end' ? calculateAmountNozzle(dIndex, nIndex) : ''">
                                                        </div>
                                                        <div class="mb-3 col-md-3">
                                                            <input type="text" class="form-control"
                                                                   :disabled="listDispenser.shift_sale.status == 'start'"
                                                                   v-model="n.end_reading"
                                                                   @input="listDispenser.shift_sale.status == 'end' ? calculateAmountNozzle(dIndex, nIndex) : ''">
                                                        </div>

                                                        <div class="mb-3 col-md-2">
                                                            <input type="text" class="form-control"
                                                                   v-model="n.consumption">
                                                        </div>
                                                        <div class="mb-3 col-md-2">
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
            param: {
                dispenser_name: '',
                brand: '',
                serial: '',
                product_id: '',
            },
            loading: false,
            listData: [],
            listDispenser: null,
            product_id: '',
        }
    },
    methods: {
        calculateAmount: function () {
            this.listDispenser.shift_sale.amount = parseFloat(this.listDispenser.shift_sale.end_reading) - parseFloat(this.listDispenser.shift_sale.start_reading)
        },
        calculateAmountNozzle: function (dIndex, nIndex) {
            this.listDispenser.summary[dIndex].nozzle[nIndex].amount = parseFloat(this.listDispenser.summary[dIndex].nozzle[nIndex].end_reading) - parseFloat(this.listDispenser.summary[dIndex].nozzle[nIndex].start_reading)
        },
        getProduct: function () {
            ApiService.POST(ApiRoutes.ProductList, {limit: 5000, page: 1, order_mode: 'ASC'}, res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.listData = res.data.data;
                }
            });
        },
        getProductDispenser: function () {
            ApiService.POST(ApiRoutes.ProductDispenser, {product_id: this.product_id}, res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.listDispenser = res;
                }
            });
        },
        save: function () {
            ApiService.ClearErrorHandler;
            this.loading = true
            ApiService.POST(ApiRoutes.ShiftSaleAdd, this.listDispenser, res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$toast.success(res.message);
                    this.getProductDispenser()
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
        $('#dashboard_bar').text('Shift Sale Start')
    }
}
</script>

<style scoped>

</style>
