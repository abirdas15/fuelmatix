<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Nozzle'}">Nozzle</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Add</a></li>

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
                                    <div class="mb-3 form-group col-md-4">
                                        <label class="form-label">Nozzle Name:</label>
                                        <input type="text" class="form-control" name="name" v-model="param.name">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-4">
                                        <label class="form-label">Nozzle ID:</label>
                                        <input type="text" class="form-control" name="nozzle_id" v-model="param.nozzle_id">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-4">
                                        <label class="form-label">Dispenser:</label>
                                        <select class="form-control" name="dispenser_id" id="dispenser_id"  v-model="param.dispenser_id">
                                            <option value="">Select Dispenser</option>
                                            <option v-for="d in listData" :value="d.id">{{d.dispenser_name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-4">
                                        <label class="form-label">Opening Stock:</label>
                                        <input type="text" class="form-control" name="opening_stock" v-model="param.opening_stock">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-4">
                                        <label class="form-label">PF:</label>
                                        <input type="text" class="form-control" name="pf" v-model="param.pf">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-4">
                                        <label class="form-label">Max Value:</label>
                                        <input type="text" class="form-control" name="max_value" v-model="param.max_value">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-4">
                                        <label class="form-label">Mac:</label>
                                        <input type="text" class="form-control" name="mac" v-model="param.mac">
                                        <div class="invalid-feedback"></div>
                                    </div>
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
            param: {
                dispenser_id: '',
                name: '',
                opening_stock: '',
                pf: '',
                max_value: '',
                nozzle_id: '',
                mac: ''
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
        dispenserList: function () {
            ApiService.POST(ApiRoutes.DispenserList, this.listParam,res => {
                if (parseInt(res.status) === 200) {
                    this.listData = res.data.data;
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            ApiService.POST(ApiRoutes.NozzleAdd, this.param,res => {
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
        this.dispenserList()
    },
    mounted() {
        $('#dashboard_bar').text('Nozzle Add')
    }
}
</script>

<style scoped>

</style>
