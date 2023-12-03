<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Tank'}">Tank</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Edit</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Tank</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form @submit.prevent="save">
                                <div class="row">
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Tank Name:</label>
                                        <input type="text" class="form-control" name="tank_name" v-model="param.tank_name">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Tank Product:</label>
                                        <select class="form-control" name="product_id" id="product_id"  v-model="param.product_id">
                                            <option value="">Select Product</option>
                                            <option v-for="d in listData" :value="d.id">{{d.name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Opening Stock:</label>
                                        <input type="number" class="form-control" name="height" v-model="param.opening_stock">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">BSTI Chart:</label>
                                        <input id="fileInput" type="file" class="form-file-input form-control"  @change="onFileChange" name="mediaFile">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                </div>
                                <div class="row" style="text-align: right;">
                                    <div class="mb-3 col-md-6">

                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link :to="{name: 'Tank'}" type="button" class="btn btn-danger">Cancel</router-link>
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
            listData: []
        }
    },
    methods: {
        getSingle: function () {
            ApiService.POST(ApiRoutes.TankSingle, {id: this.id},res => {
                if (parseInt(res.status) === 200) {
                    this.param = res.data
                }
            });
        },
        getProduct: function () {
            ApiService.POST(ApiRoutes.ProductList, {limit: 5000, page: 1},res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.listData = res.data.data;
                }
            });
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            let formData = new FormData()
            formData.append('id', this.param.id)
            formData.append('tank_name', this.param.tank_name)
            formData.append('capacity', this.param.capacity)
            formData.append('height', this.param.height)
            formData.append('product_id', this.param.product_id)
            formData.append('opening_stock', this.param.opening_stock)
            formData.append('file', this.param.file)
            ApiService.POST(ApiRoutes.TankEdit, formData,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$router.push({
                        name: 'Tank'
                    })
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        onFileChange(e) {
            let files = e.target.files || e.dataTransfer.files;
            if (!files.length)
                return;
            this.param.file = files[0];
        },
    },
    created() {
        this.id = this.$route.params.id
        this.getSingle()
        this.getProduct()
    },
    mounted() {
        $('#dashboard_bar').text('Tank Edit')
    }
}
</script>

<style scoped>

</style>
