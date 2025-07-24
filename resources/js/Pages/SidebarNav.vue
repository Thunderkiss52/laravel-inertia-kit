<template>
    <el-menu class="sidebar-nav" :default-active="active">
        <el-menu-item v-for="tab in tabs" :index="tab.url">
            <Link style="width: 100%" :href="tab.url">
            <el-icon v-if="tab.attributes && tab.attributes.icon">
                <component :is="Icons[tab.attributes.icon]"></component>
            </el-icon>
            <span>{{ tab.title }}</span>
            </Link>
        </el-menu-item>
    </el-menu>
</template>

<script setup>
import * as Icons from '@element-plus/icons-vue';
import { Head, Link, router } from "@inertiajs/vue3";
const { tabs, active } = defineProps({ tabs: Array, active: String });
</script>

<style>

.sidebar-nav .el-sub-menu__title span, .sidebar-nav .el-menu-item a {
    text-wrap: auto;
    line-height: normal;
}

.sidebar-nav .el-menu-item a {
    padding: 20px 5px !important;
    padding-left: 15px !important;
}

.sidebar-nav .el-menu-item {
    padding: 0px !important;
    height: fit-content !important;
}

.sidebar-nav .el-sub-menu__title {
    height: fit-content !important;
    padding: 20px !important;
}
</style>