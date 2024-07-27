<template>
    <aside class="sidebar-wrapper" data-simplebar="true">
        <div class="sidebar-header">
            <div>
                <h4 class="logo-text">Fuel Matix</h4>
            </div>
            <div class="toggle-icon ms-auto"><i class="bi bi-list"></i>
            </div>
        </div>
        <!--navigation-->
        <ul class="metismenu" id="menu">
            <li>
                <router-link :to="{name: 'Dashboard'}">
                    <div class="menu-title"><i class="bi bi-house-fill"></i>&nbsp;Dashboard</div>
                </router-link>
            </li>
            <li>
                <router-link :to="{name: 'Company'}">
                    <div class="menu-title"><i class="bi bi-buildings"></i>&nbsp;Company</div>
                </router-link>
            </li>
            <li>
                <router-link :to="{name: 'User'}">
                    <div class="menu-title"><i class="bi bi-people"></i>&nbsp;User</div>
                </router-link>
            </li>
        </ul>
        <!--end navigation-->
    </aside>
</template>
<script>


</script>
<style scoped>

</style>
