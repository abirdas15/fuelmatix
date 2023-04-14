<template>
    <div>
        <div id="preloader">
            <div class="lds-ripple">
                <div></div>
                <div></div>
            </div>
        </div>
        <div id="main-wrapper">
            <Header/>
            <SideNav/>
            <vue-page-transition name="fade">
                <router-view></router-view>
            </vue-page-transition>
            <Footer/>
        </div>
    </div>
</template>
<script>
import Header from "./components/Header";
import SideNav from "./components/SideNav";
import Footer from "./components/Footer.vue";

export default {
    components: {Footer, Header, SideNav}
}
</script>
