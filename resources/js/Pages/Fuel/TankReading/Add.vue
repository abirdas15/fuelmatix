<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'TankReading'}">Tank</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Reading</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Tank Reading</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form @submit.prevent="save">
                                <div class="row">
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Tank ID:</label>
                                        <select class="form-control" name="tank_id" id="tank_id"  v-model="param.tank_id">
                                            <option value="">Select Tank</option>
                                            <option v-for="d in listData" :value="d.id">{{d.tank_name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Type:</label>
                                        <select class="form-control" name="type" id="type"  v-model="param.type">
                                            <option value="">Select Type</option>
                                            <option value="shift sell">Shift sell</option>
                                            <option value="tank refill">Tank refill</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Date:</label>
                                        <input type="text" class="form-control date bg-white" name="date" v-model="param.date">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Height:</label>
                                        <input type="text" class="form-control" name="height" v-model="param.height">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Water Height:</label>
                                        <input type="text" class="form-control" name="water_height" v-model="param.water_height">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="row" style="text-align: right;">
                                    <div class="mb-3 col-md-6">

                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link :to="{name: 'TankReading'}" type="button" class="btn btn-primary">Cancel</router-link>
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
            param: {
                tank_id: '',
                date: '',
                height: '',
                type: '',
                water_height: '',
            },
            loading: false,
            listData: [],
        }
    },
    methods: {
        getTank: function () {
            ApiService.POST(ApiRoutes.TankList, {limit: 5000, page: 1},res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.listData = res.data.data;
                }
            });
        },
        save: function () {
            ApiService.ClearErrorHandler();
            if (this.param.date == '') {
                this.param.date = moment().format('YYYY-MM-DD')
            }
            this.loading = true
            ApiService.POST(ApiRoutes.TankReadingAdd, this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$router.push({
                        name: 'TankReading'
                    })
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
    },
    created() {
        this.getTank()
    },
    mounted() {
        setTimeout(() => {
            $('.date').flatpickr({
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d",
                defaultDate: 'today',
                onChange: (dateStr, date) => {
                    this.param.date = date
                }
            })
        }, 1000)
        $('#dashboard_bar').text('Tank Reading')
    }
}
</script>

<style scoped>

</style>
