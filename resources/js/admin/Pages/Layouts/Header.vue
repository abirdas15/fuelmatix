<template>
    <header class="top-header">
        <nav class="navbar navbar-expand gap-3">
            <div class="top-navbar-right ms-auto">
                <ul class="navbar-nav align-items-center gap-1">

                </ul>
            </div>
            <div class="dropdown dropdown-user-setting">
                <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown">
                    <div class="user-setting d-flex align-items-center gap-3">
                        <img :src="auth.image_path" class="user-img" alt="">
                        <div class="d-none d-sm-block">
                            <p class="user-name mb-0">{{ auth.name }}</p>
                        </div>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <div class=""><i class="bi bi-person-fill"></i></div>
                                <div class="ms-3"><span>Profile</span></div>
                            </div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" @click="logout">
                            <div class="d-flex align-items-center">
                                <div class=""><i class="bi bi-lock-fill"></i></div>
                                <div class="ms-3"><span>Logout</span></div>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
</template>
<script>
import ApiRoutes from "../../Services/ApiRoutes";
import ApiService from "../../Services/ApiService";

export default {
    data() {
        return {
            auth: {}
        }
    },
    created() {
        this.fetchProfile();
    },
    methods: {
        logout: function() {
            ApiService.POST(ApiRoutes.Logout, {}, (res) => {
                if (parseInt(res.status) === 200) {
                    window.location.reload();
                }
            });
        },
        fetchProfile: function() {
            ApiService.POST(ApiRoutes.Profile + '/me', {}, (res) => {
                if (parseInt(res.status) === 200) {
                    this.auth = res.data;
                }
            });
        }
    }
}
</script>
<style scoped>

</style>
