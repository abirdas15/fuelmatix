<template>
    <div class="wrapper">
        <Header></Header>
        <SideNav></SideNav>
        <router-view></router-view>
    </div>
</template>
<script>

import Header from "../Layouts/Header.vue";
import SideNav from "../Layouts/Sidenav.vue";

export default {
    components: {
        SideNav,
        Header
    }
}
</script>

<style scoped>

</style>
