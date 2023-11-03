<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Nozzle'}">Nozzle</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Edit</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Nozzle</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form @submit.prevent="save">
                                <div class="row">
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Nozzle Name:</label>
                                        <input type="text" class="form-control" name="dispenser_name" v-model="param.name">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Dispenser:</label>
                                        <select class="form-control" name="dispenser_id" id="dispenser_id"  v-model="param.dispenser_id">
                                            <option value="">Select Dispenser</option>
                                            <option v-for="d in listData" :value="d.id">{{d.dispenser_name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
<!--                                    <div class="mb-3 form-group col-md-6">-->
<!--                                        <label class="form-label">Opening Stock:</label>-->
<!--                                        <input type="number" class="form-control" name="opening_stock" v-model="param.opening_stock">-->
<!--                                        <div class="invalid-feedback"></div>-->
<!--                                    </div>-->
                                </div>
                                <div class="row" style="text-align: right;">
                                    <div class="mb-3 col-md-6">

                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link :to="{name: 'Nozzle'}" type="button" class="btn btn-danger">Cancel</router-link>
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
            param: {},
            loading: false,
            id: '',
            listData: [],
        }
    },
    methods: {
        dispenserList: function () {
            ApiService.POST(ApiRoutes.DispenserList, this.listParam,res => {
                if (parseInt(res.status) === 200) {
                    this.listData = res.data.data;
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
        getSingle: function () {
            ApiService.POST(ApiRoutes.NozzleSingle, {id: this.id},res => {
                if (parseInt(res.status) === 200) {
                    this.param = res.data
                }
            });
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            ApiService.POST(ApiRoutes.NozzleEdit, this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$router.push({
                        name: 'Nozzle'
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
        this.dispenserList()
    },
    mounted() {
        $('#dashboard_bar').text('Nozzle Edit')
    }
}
</script>

<style scoped>

</style>
