<template>
  <div>
      <div class="nav-header">
          <a href="javascript:void(0)" class="brand-logo">
              <img src="/images/fuelBL.jpeg" alt="">
          </a>
          <div class="nav-control">
              <div class="hamburger">
                  <span class="line"></span><span class="line"></span><span class="line"></span>
              </div>
          </div>
      </div>
      <!--**********************************
              Nav header end
          ***********************************-->

      <!--**********************************
              Header start
          ***********************************-->
      <div class="header border-bottom">
          <div class="header-content">
              <nav class="navbar navbar-expand">
                  <div class="collapse navbar-collapse justify-content-between">
                      <div class="header-left">
                          <div class="dashboard_bar" id="dashboard_bar">Dashboard</div>
                      </div>
                      <ul class="navbar-nav header-right">
                          <li class="nav-item dropdown header-profile">
                              <a class="nav-link profile-name" href="javascript:void(0);" role="button" data-bs-toggle="dropdown">
                                  <img :src="'https://via.placeholder.com/200x150?text=' + Auth.name" alt="">
                              </a>
                              <div class="dropdown-menu dropdown-menu-end">
                                  <a href="javascript:void(0)" @click="Logout" class="dropdown-item ai-icon">
                                      <svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18" height="18"
                                           viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                           stroke-linejoin="round">
                                          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                          <polyline points="16 17 21 12 16 7"></polyline>
                                          <line x1="21" y1="12" x2="9" y2="12"></line>
                                      </svg>
                                      <span class="ms-2">Logout </span>
                                  </a>
                              </div>
                          </li>
                      </ul>
                  </div>
              </nav>
          </div>
      </div>
  </div>
</template>
<script>
import ApiService from "../../../Services/ApiService";
import ApiRoutes from "../../../Services/ApiRoutes";
import store from "../../../Store/store";

export default {
    data() {
        return {
            user: null,
        };
    },
    created() {
    },
    methods: {
        Logout: function () {
           store.dispatch('Logout')
        },
    },
    mounted() {
    },
    computed: {
        Auth: function () {
            return this.$store.getters.GetAuth;
        },
        company_id: function () {
            return this.$store.getters.GetCompanyId;
        },
    },
};
</script>
<style lang="scss">
.profile-link{
    height: 3.75rem;
    width: 3.75rem;
    border-radius: 4.25rem;
}
</style>
