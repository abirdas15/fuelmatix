<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'NozzleReading'}">Nozzle Reading</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Add</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Nozzle Reading</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form @submit.prevent="save">
                                <div class="row">
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Nozzle:</label>
                                        <select class="form-control" name="nozzle_id" id="nozzle_id"  v-model="param.nozzle_id">
                                            <option value="">Select Nozzle</option>
                                            <option v-for="d in listData" :value="d.id">{{d.name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Date:</label>
                                        <input type="text" class="form-control date bg-white" name="date">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Reading:</label>
                                        <input type="text" class="form-control" name="reading" v-model="param.reading">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="row" style="text-align: right;">
                                    <div class="mb-3 col-md-6">

                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link :to="{name: 'NozzleReading'}" type="button" class="btn btn-primary">Cancel</router-link>
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
                nozzle_id: '',
                reading: '',
                date: '',
            },
            listParam: {
                limit: 5000,
                page: 1,
            },
            loading: false,
            listData: [],
        }
    },
    methods: {
        nozzleList: function () {
            ApiService.POST(ApiRoutes.NozzleList, this.listParam,res => {
                if (parseInt(res.status) === 200) {
                    this.listData = res.data.data;
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
        save: function () {
            ApiService.ClearErrorHandler();
            if (this.param.date == '') {
                this.param.date = moment().format('YYYY-MM-DD')
            }
            this.loading = true
            ApiService.POST(ApiRoutes.NozzleReadingAdd, this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$router.push({
                        name: 'NozzleReading'
                    })
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
    },
    created() {
        this.nozzleList()
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
        $('#dashboard_bar').text('Nozzle Reading Add')
    }
}
</script>

<style scoped>

</style>
