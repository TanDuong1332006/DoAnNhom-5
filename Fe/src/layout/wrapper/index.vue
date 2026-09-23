<template>
    <div class="wrapper">
        <div class="header-wrapper">

            <header>
                <div class="topbar d-flex align-items-center">
                    <HeaderRocker></HeaderRocker>
                </div>
            </header>
            <MenuRocker></MenuRocker>
        </div>
        <div class="page-wrapper">
            <div class="page-content">
                <router-view></router-view>

            </div>
        </div>
        <div class="overlay toggle-icon"></div>
        <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
        <FooterRocker></FooterRocker>
    </div>
</template>
<script>
import HeaderRocker from "../../layout/components/HeaderRocker.vue";
import MenuRocker from "../../layout/components/MenuRocker.vue";
import FooterRocker from "../../layout/components/FooterRocker.vue";
import "../../assets/js/pace.min.js";
import "../../assets/js/bootstrap.bundle.min.js";
import "../../assets/js/jquery.min.js";
import "../../assets/plugins/simplebar/js/simplebar.min.js";
import "../../assets/plugins/metismenu/js/metisMenu.min.js";

import "../../assets/js/app.js";
export default {
    components: {
        HeaderRocker,
        MenuRocker,
        FooterRocker
    }
}
</script>
<style>
@import "../../assets/plugins/vectormap/jquery-jvectormap-2.0.2.css";
@import "../../assets/plugins/simplebar/css/simplebar.css";
@import "../../assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css";
@import "../../assets/plugins/metismenu/css/metisMenu.min.css";
@import "../../assets/css/pace.min.css";
@import "../../assets/css/bootstrap.min.css";
@import "../../assets/css/bootstrap-extended.css";
@import "../../assets/css/app.css";
@import "../../assets/css/icons.css";
@import "../../assets/css/dark-theme.css";
@import "../../assets/css/semi-dark.css";
@import "../../assets/css/header-colors.css";
@import url("https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap");
@import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css");
</style>