<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Asset Transfer</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Transfer Edit</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form @submit.prevent="save">
                                <div class="row">
                                    <div class="col-6 mb-3 form-group">
                                        <label class="form-label">Date:</label>
                                        <input type="text" class="form-control date bg-white" name="date" v-model="param.date">
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">From:</label>
                                        <select name="from_category_id" class="form-control form-select" v-model="param.from_category_id">
                                            <template v-for="c in categories">
                                                <option v-if="c.id != param.to_category_id" :value="c.id">{{c.name}}</option>
                                            </template>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">To:</label>
                                        <select name="from_category_id" class="form-control form-select" v-model="param.to_category_id">
                                            <template v-for="c in categories">
                                                <option v-if="c.id != param.from_category_id" :value="c.id">{{c.name}}</option>
                                            </template>
                                        </select>

                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Amount:</label>
                                        <input type="text" class="form-control" name="amount" v-model="param.amount">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Remarks:</label>
                                        <input type="text" class="form-control" name="remarks" v-model="param.remarks">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="row" style="text-align: right;">
                                    <div class="mb-3 col-md-6">

                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link :to="{name: 'user'}" type="button" class="btn btn-primary">Cancel</router-link>
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
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";
export default {
    data() {
        return {
            param: {},
            loading: false,
            id: '',
            categories: []
        }
    },
    methods: {
        getSingle: function () {
            ApiService.POST(ApiRoutes.balanceTransferSingle, {id: this.id},res => {
                if (parseInt(res.status) === 200) {
                    this.param = res.data;
                    setTimeout(() => {
                        $('.date').flatpickr({
                            altInput: true,
                            altFormat: "d/m/Y",
                            dateFormat: "Y-m-d",
                            defaultDate: 'today',
                            onChange: (date, dateStr) => {
                                this.param.date = dateStr
                            }
                        })
                    }, 1000)
                }
            });
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            ApiService.POST(ApiRoutes.balanceTransferEdit, this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$router.push({
                        name: 'balanceTransfer'
                    })
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        getParentCategory() {
            ApiService.POST(ApiRoutes.CategoryParent, {type: 'assets'},res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.categories = res.data
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        }
    },
    created() {
        this.id = this.$route.params.id
        this.getSingle()
        this.getParentCategory()
    },
    mounted() {
        $('#dashboard_bar').text('Transfer Edit')
    }
}
</script>

<style scoped>

</style>
