<template>
    <main class="page-content">
        <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-2 row-cols-xxl-4">
            <div class="col">
                <div class="card overflow-hidden radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-stretch justify-content-between overflow-hidden">
                            <div class="w-50">
                                <p>Total Company</p>
                                <h4 class="">{{ summary?.total_company }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card overflow-hidden radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-stretch justify-content-between overflow-hidden">
                            <div class="w-50">
                                <p>Total User</p>
                                <h4 class="">{{ summary?.total_user }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>
<script>
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";

export default {
    data() {
        return {
            summary: {}
        }
    },
    created() {
        this.fetchDashboardSummary();
    },
    methods: {
        fetchDashboardSummary: function() {
            ApiService.POST(ApiRoutes.Dashboard + '/summary', {}, (res) => {
                if (parseInt(res.status) === 200) {
                    this.summary = res.data;
                }
            });
        }
    }
}
</script>

<style scoped>

</style>
