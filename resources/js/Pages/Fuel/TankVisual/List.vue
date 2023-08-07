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
                            <div class="mt-3 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="tt text-height">Height</div>
                                    <div class="line height"></div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="tt text-capacity">Capacity</div>
                                    <div class="line capacity"></div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="tt text-fuel">Fuel</div>
                                    <div class="line fuel"></div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="tt text-water">Water</div>
                                    <div class="line water"></div>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-sm-4 mb-5" v-for="f in listData">
                                    <div class="taank">
<!--                                        <div class="tank-height">
                                            <div class="height">{{ f.height != null ? f.height : 'N/A' }}</div>
                                            <div class="height-line"></div>
                                        </div>-->
                                        <div id="waterLevelDiags" class="water-tank">
<!--                                            <div class="tank-capacity">
                                                <div class="capacity">{{f.capacity != null ? f.capacity : 'N/A'}}</div>
                                                <div class="capacity-line"></div>
                                            </div>-->
                                            <div class="fuel-height">
                                                <div class="fuel-capacity" :style="{bottom: f.water_percent+'%', height: f.fuel_percent+'%'}">
                                                    <div class="fuel-attr" v-if="f.fuel_percent > 0">{{f.fuel_percent}}%</div>
                                                    <div class="fuel-line" v-if="f.fuel_percent > 0"></div>
                                                </div>
                                                <div class="water-capacity" :style="{bottom: 0, height: f.water_percent+'%'}">
                                                    <div class="water-attr" v-if="f.water_percent > 0">{{f.water_percent}}%</div>
                                                    <div class="water-line" v-if="f.water_percent > 0"></div>
                                                </div>
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
        right: 40%;
        text-align: center;
        top: -24px;
        .height-line{
            height: 3px;
            width: 94px;
            background-image: linear-gradient(90deg, transparent, transparent 50%, #fff 50%, #fff 100%), linear-gradient(90deg, #369D6F, #369D6F, #369D6F, #369D6F, #369D6F);
            background-size: 12px 3px, 100% 3px;
            border: none;
        }
        .height{
            color: #369D6F;
        }
        .tank-attr{
            color: #369D6F;
            font-weight: bold;
            position: absolute;
            left: -6rem;
            top: 0.8rem;
        }
    }
    .water-tank{
        margin: auto;
        height: 250px;
        width: 300px;
        border-radius : 190px 190px 133px 147px;
        border-width: 3px;
        border-color: #a6a6a6;
        border-style: solid;
        position: relative;
        overflow: hidden;
        .tank-capacity{
            position: absolute;
            left: 41px;
            text-align: center;
            top: 1rem;
            .capacity-line{
                height: 3px;
                width: 209px;
                background-image: linear-gradient(90deg, transparent, transparent 50%, #fff 50%, #fff 100%), linear-gradient(90deg, red, red, red, red, red);
                background-size: 12px 3px, 100% 3px;
                border: none;
            }
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
        .fuel-height{
            height: 200px;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            .fuel-capacity{
                width: 100%;
                position: absolute;
                left: 0;
                right: 0;
                text-align: center;
                &.petrol{
                    background-color: #D5D783;
                }
                &.octane{
                    background-color: #e76362;
                }
                &.octane{
                    background-color: #e76362;
                }
                //animation: wave 5s infinite;
                transition: 800ms;
                .fuel-line{
                    height: 2px;
                    width: 300px;
                    background-size: 14px 3px, 100% 3px;
                    border: none;
                    position: absolute;
                    top: -2px;
                }
                .fuel-attr{
                    color: #FFC301;
                    font-weight: bold;
                    position: absolute;
                    top: -19px;
                    right: 2.5rem;
                }
            }
            .water-capacity{
                width: 100%;
                background-color: #1fafed8c;
                position: absolute;
                left: 0;
                right: 0;
                border-bottom-left-radius: 8px;
                border-bottom-right-radius: 8px;
                //animation: wave 5s infinite;
                transition: 800ms;
                .water-line{
                    height: 2px;
                    width: 300px;
                    background-size: 14px 3px, 100% 3px;
                    border: none;
                    position: absolute;
                    top: -2px;
                }
                .water-attr{
                    color: #00B3FF;
                    font-weight: bold;
                    position: absolute;
                    top: -20px;
                    left: 3.5rem;
                }
            }
        }
    }
}
@keyframes wave {
    0% {
        transform: rotateZ(0deg);
        transition: 800ms
    }
    25% {
        transform: rotateZ(3deg);
        transition: 800ms
    }
    75% {
        transform: rotateZ(0deg);
        transition: 800ms
    }
    100% {
        transform: rotateZ(-3deg);
        transition: 800ms
    }
}
.tt{
    width: 70px;
    &.text-height{
        color: #369D6F;
    }
    &.text-capacity{
        color: red;
    }
    &.text-fuel{
        color: #bf9201;
    }
    &.text-water{
        color: #00B3FF;
    }
}

.line{
    height: 2px;
    width: 80px;
    background-size: 14px 3px, 100% 3px;
    border: none;
    margin-left: 20px;
    &.height{
        background-image: linear-gradient(90deg, transparent, transparent 50%, #fff 50%, #fff 100%), linear-gradient(90deg, #369D6F, #369D6F, #369D6F, #369D6F, #369D6F);
    }
    &.capacity{
        background-image: linear-gradient(90deg, transparent, transparent 50%, #fff 50%, #fff 100%), linear-gradient(90deg, red, red, red, red, red);
    }
    &.fuel{
        background-image: linear-gradient(90deg, transparent, transparent 50%, #fff 50%, #fff 100%), linear-gradient(90deg, #bf9201, #bf9201, #bf9201, #bf9201, #bf9201);
    }
    &.water{
        background-image: linear-gradient(90deg, transparent, transparent 50%, #fff 50%, #fff 100%), linear-gradient(90deg, #00B3FF, #00B3FF, #00B3FF, #00B3FF, #00B3FF);
    }
}
</style>
