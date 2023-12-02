<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'purchase'}">Purchase Bill</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Add</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add Purchase Bill</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form @submit.prevent="save">
                                <div class="row">
                                    <div class="mb-3 col-md-3 form-group">
                                        <label class="form-label">Date:</label>
                                        <input type="date" class="form-control date bg-white" name="date">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 col-md-3 form-group">
                                        <label class="form-label">Vendor</label>
                                        <select class="form-control form-select" name="vendor_id" v-model="param.vendor_id">
                                            <option v-for="v in listData" :value="v.id">{{ v.name }}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="mb-3 col-md-3 form-group">
                                        <label class="form-label">Bill ID</label>
                                        <input type="text" class="form-control" value="Dispenser Brand" name="Bill ID" v-model="param.bill_id">
                                    </div>
                                </div>
                                <div class="row" v-for="(p, index) in param.purchase_item">
                                    <div class="mb-3 col-md-3 form-group">
                                        <select class="form-control form-select" :name="'purchase_item.'+index+'.product_id'" v-model="p.product_id">
                                            <option v-for="v in ProductList" :value="v.id">{{ v.name }}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 col-md-3 form-group">
                                        <input type="number" class="form-control" placeholder="Unit Price" :name="'purchase_item.'+index+'.unit_price'" v-model="p.unit_price" @input="sumTotal(index)">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="mb-3 col-md-3 form-group">
                                        <input type="number" class="form-control" placeholder="Quantity" :name="'purchase_item.'+index+'.quantity'" v-model="p.quantity" @input="sumTotal(index)">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 col-md-2">
                                        <input type="text" class="form-control" disabled placeholder="Total" :name="'purchase_item.'+index+'.total'" v-model="p.total">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 col-md-1">
                                        <label class="form-label"> <br> </label>
                                        <button class="btn btn-primary" v-if="param.purchase_item.length-1 == index" @click="addItem" type="button">+</button>
                                        <button class="btn btn-danger" v-else @click="removeItem(index)" type="button">×</button>
                                    </div>
                                </div>
                                <div class="row" style="text-align: right;" >
                                    <div class="toolbar toolbar-bottom " style="margin-top: 5px;">
                                        <button class="btn btn-primary ms-2" v-if="!loading" type="submit">Submit</button>
                                        <button class="btn btn-primary ms-2" v-else type="button">Submitting...</button>
                                        <router-link :to="{name: 'purchase'}" class="btn btn-primary ms-2">Cancel</router-link>
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
            param: {
                date: '',
                vendor_id: '',
                bill_id: '',
                purchase_item: [],
            },
            listParam: {
                limit: 5000,
                page: 1,
            },
            loading: false,
            listData: [],
            ProductList: [],
        }
    },
    methods: {
        sumTotal: function (index) {
            let total = parseInt(this.param.purchase_item[index].unit_price) * parseInt(this.param.purchase_item[index].quantity)
            if (!isNaN(total)) {
                this.param.purchase_item[index].total = total
            }
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            if (this.param.date == '') {
                this.param.date = moment().format('YYYY-MM-DD')
            }
            ApiService.POST(ApiRoutes.PurchaseSave, this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$router.push({
                        name: 'purchase'
                    })
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        getVendor: function () {
            ApiService.POST(ApiRoutes.VendorList, this.Param,res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.listData = res.data.data;
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
        getProduct: function () {
            ApiService.POST(ApiRoutes.ProductList, this.Param,res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.ProductList = res.data.data;
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
        addItem: function () {
            this.param.purchase_item.push(
                {product_id: '', unit_price: '', quantity: '', total: ''}
            )
        },
        removeItem: function (index) {
            this.param.purchase_item.splice(index, 1)
        }
    },
    created() {
        this.param.purchase_item.push(
            {product_id: '', unit_price: '', quantity: '', total: ''}
        )
        this.getProduct()
        this.getVendor()
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
        $('#dashboard_bar').text('Purchase Bill')
    }
}
</script>

<style scoped>

</style>
