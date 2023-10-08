<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Tank List</a></li>
                    <li style="margin-left: auto;"><router-link :to="{name: 'TankAdd'}"><i class="fa-solid fa-plus"></i> Add New Tank</router-link></li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h4 class="card-title">Tank List</h4>
                        </div>
                        <div class="card-body">
                            <div class="row mt-4">
                                <div class="col-sm-4 mb-5" v-for="f in listData">
                                    <div class="taank">
                                        <div class="tank-height">
                                            <div class="height">{{ f.height != null ? f.height : 'N/A' }} (Tank Height)</div>
                                        </div>
                                        <div class="water-tank">
                                            <div class="tank-capacity">
                                                <div class="capacity">{{f.capacity != null ? f.capacity : 'N/A'}} (Fuel Capacity)</div>
                                            </div>
                                            <div class="fuel-height">
                                                <svg width="100%" height="100%" version="1.1" xmlns="http://www.w3.org/2000/svg" class="wave"><defs></defs><path :id="'fuel'+i" d=""/></svg>
                                                <svg style="position: absolute; left: 0" width="100%" height="100%" version="1.1" xmlns="http://www.w3.org/2000/svg" class="wave"><defs></defs><path :id="'water'+i"d=""/></svg>
                                            </div>
                                            <div class="fuel-vol" :style="{top: calculateTop(f)}">
                                                <div class="vol">{{f.last_reading.volume != null ? f.last_reading.volume : 'N/A'}} mm</div>
                                            </div>
                                        </div>

                                        <div class="text-center mt-1 fw-bold">
                                            {{f.tank_name}}
                                        </div>
                                        <div class="text-center">
                                            ({{f.product_name}})
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Swal from 'sweetalert2/dist/sweetalert2.js'
import ApiService from "../../../Services/ApiService";
import ApiRoutes from "../../../Services/ApiRoutes";
import Pagination from "../../../Helpers/Pagination";
export default {
    components: {
        Pagination,
    },
    data() {
        return {
            paginateData: {},
            Param: {
                keyword: '',
                limit: 10,
                order_by: 'id',
                order_mode: 'DESC',
                page: 1,
            },
            Loading: false,
            TableLoading: false,
            listData: [],
        };
    },
    watch: {
        'Param.keyword': function () {
            this.list()
        },
    },
    created() {
        this.list();
    },
    computed: {
        Auth: function () {
            return this.$store.getters.GetAuth;
        },
    },
    methods: {
        calculateTop: function (tank) {
            return 200 - (parseInt(tank.fuel_percent) * 2) +27 +'px'
        },
        openModalDelete(data) {
            Swal.fire({
                title: 'Are you sure you want to delete?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.Delete(data)
                }
            })
        },
        list: function (page) {
            if (page == undefined) {
                page = {
                    page: 1
                };
            }
            this.Param.page = page.page;
            this.TableLoading = true
            ApiService.POST(ApiRoutes.TankList, this.Param,res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.paginateData = res.data;
                    this.listData = res.data.data;
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
        Delete: function (data) {
            ApiService.POST(ApiRoutes.TankDelete, {id: data.id },res => {
                if (parseInt(res.status) === 200) {
                    this.$toast.success(res.message);
                    this.list()
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },

        sortClass: function (order_by) {
            let cls;
            if (this.Param.order_by == order_by && this.Param.order_mode == 'DESC') {
                cls = 'sorting_desc'
            } else if (this.Param.order_by == order_by && this.Param.order_mode == 'ASC') {
                cls = 'sorting_asc'
            } else {
                cls = 'sorting'
            }
            return cls;
        },
        sortData: function (sort_name) {
            this.Param.order_by = sort_name;
            this.Param.order_mode = this.Param.order_mode == 'DESC' ? 'ASC' : 'DESC'
            this.list();
        },
    },
    mounted() {
        $('#dashboard_bar').text('Tank List')
    }
}
</script>

<style lang="scss" scoped>
.taank{
    position: relative;
    .tank-height{
        position: absolute;
        left: -2rem;
        text-align: right;
        top: 0;
        width: 180px;
        .height{
            color: #369D6F;
        }
    }

    .water-tank{
        margin: auto;
        height: 250px;
        width: 200px;
        border-radius : 0;
        border-width: 3px;
        border-top: 0;
        border-color: #a6a6a6;
        border-style: solid;
        position: relative;
        overflow: visible;
        .tank-capacity{
            position: absolute;
            left: -11.5rem;
            text-align: right;
            top: 1.7rem;
            width: 180px;
            .capacity{
                color: red;
            }
            .tank-attr{
                color: red;
                font-weight: bold;
                position: absolute;
                right: -7rem;
                top: 0.8rem;
            }
        }
        .fuel-vol{
            position: absolute;
            right: -9rem;
            text-align: left;
            width: 137px;
            .vol{
                color: #424242;
            }
        }
        .fuel-height{
            height: 200px;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
        }
    }
    .tank-bar{
        height: 250px;
        width: 2px;
        background-color: #a6a6a6;
    }
}
</style>
