<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'salary'}">Salary</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Add</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="row">
                <div class="col-sm-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Search Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-3" :class="{'block' : employee.length > 0}">
                                <div class="col-12 form-group position-relative">
                                    <label for="year" class="form-label">Select Year<span class="text-danger">*</span></label>
                                    <select v-model="searchParam.year" name="year" class="form-control form-select" id="year">
                                        <option v-for="year in years(new Date().getFullYear()-1)" :value="year.id">{{ year.name}}</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-12 form-group">
                                    <label for="month" class="form-label">Select Month<span class="text-danger">*</span></label>
                                    <select v-model="searchParam.month" name="month" class="form-control form-select" id="month">
                                        <option v-for="month in months()" :value="month.id">{{ month.name}}</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12 form-group text-end">
                                    <button type="button" v-if="employee.length > 0" class="btn btn-secondary me-3" @click="resetForm()">
                                        Reset
                                    </button>
                                    <button type="button" @click="getEmployee" class="btn btn-primary btn-width" v-if="!searchLoading" :disabled="employee.length > 0">
                                        Search
                                    </button>
                                    <button type="button" class="btn btn-primary btn-width" v-if="searchLoading">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-sm-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Salary Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="basic-form">
                                <form @submit.prevent="save">
                                    <div class="row">
                                       <table class="table table-bordered">
                                           <thead>
                                           <tr>
                                               <th>RFID</th>
                                               <th>Name</th>
                                               <th>Position</th>
                                               <th>Salary</th>
                                               <th>Payment Method</th>
                                           </tr>
                                           </thead>
                                           <tbody v-if="employee.length > 0">
                                           <tr v-for="(e, index) in employee">
                                               <td>{{e.rfid}}</td>
                                               <td>{{e.name}}</td>
                                               <td>{{e.position}}</td>
                                               <td>
                                                   <div class="form-group">
                                                       <input class="form-control" v-model="e.salary" type="text" :name="'employees.'+index+'.salary'">
                                                       <div class="invalid-feedback"></div>
                                                   </div>
                                               </td>
                                               <td>
                                                   <div class="form-group">
                                                       <select class="form-control" v-model="e.category_id" :name="'employees.'+index+'.category_id'">
                                                           <option value="">Select Category</option>
                                                           <option v-for="c in category" :value="c.id">{{c.name}}</option>
                                                       </select>
                                                       <div class="invalid-feedback"></div>
                                                   </div>
                                               </td>
                                           </tr>
                                           </tbody>
                                           <tbody v-if="employee.length == 0">
                                           <tr>
                                               <td class="text-center" colspan="20">No Data Found</td>
                                           </tr>
                                           </tbody>
                                       </table>
                                    </div>
                                    <div class="row" style="text-align: right;">
                                        <div class="mb-3 col-md-6">

                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                            <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                            <router-link :to="{name: 'salary'}" type="button" class="btn btn-danger">Cancel</router-link>
                                        </div>
                                    </div>
                                </form>
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
            searchParam: {
                month: '',
                year: '',
            },
            employee: [],
            loading: false,
            searchLoading: false,
            loadMoreLoading: false,
            lastPage: 0,
            category: []
        }
    },
    methods: {
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            ApiService.POST(ApiRoutes.salaryAdd, {month: this.searchParam.month, year: this.searchParam.year, employees: this.employee},res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$router.push({
                        name: 'salary'
                    })
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        resetForm: function () {
            this.message = ''
            this.employee = [];
        },
        getCategory: function () {
            ApiService.POST(ApiRoutes.salaryGetCategory, {},res => {
                if (parseInt(res.status) === 200) {
                    this.category = res.data
                }
            });
        },
        getEmployee: function () {
            this.searchLoading = true
            this.employee = []
            this.searchParam.page = 1
            this.searchParam.limit = 20
            ApiService.POST(ApiRoutes.salarySearchEmployee, this.searchParam,res => {
                this.searchLoading = false
                if (parseInt(res.status) === 200) {
                    this.employee = res.data.data
                    this.lastPage = res.data.last_page
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        getMore: function () {
            this.loadMoreLoading = true
            this.searchParam.page++
            ApiService.POST(ApiRoutes.salarySearchEmployee, this.searchParam, (res) => {
                this.loadMoreLoading = false
                if (parseInt(res.status) === 200) {
                    res.data.data.map(v => {
                        this.employee.push(v)
                    })
                }
            });
        },
    },
    created() {
        this.getCategory()
    },
    mounted() {
        $('#dashboard_bar').text('Salary Add')
    }
}
</script>

<style scoped>

</style>
