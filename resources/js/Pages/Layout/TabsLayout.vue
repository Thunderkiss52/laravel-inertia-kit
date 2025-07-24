<template>
<el-config-provider :locale="ruRu">

    <Head v-if="$page.props.layout && $page.props.layout.label" :title="$page.props.layout.label" />
    <AuthenticatedLayout>
        <div class="mx-auto" :class="{'max-w-7xl': !$page.props.layout || !$page.props.layout.large}">
            <el-row :gutter="10">
                <el-col :span="24 - extraSize">
                    <Card class="mb-3" v-if="$page.props.layout && $page.props.layout.item" :item="$page.props.layout.item" />
                </el-col>
                <el-col v-if="extraSize > 0" :span="extraSize">
                    <el-card shadow="never">
                        <slot name="extra" />
                    </el-card>
                </el-col>
                <el-col :span="
            $page.props.layout && $page.props.layout.tabs && $page.props.layout.tabs.length > 0
                ? ($page.props.layout && $page.props.layout.large ? 21 : 20)
                : 24
            ">
                    <div v-if="$page.props.layout.alerts && $page.props.layout.alerts.length > 0" class="flex gap-2 flex-col pb-3">
                        <template v-for="al in $page.props.layout.alerts">
                            <Alert v-if="al.type != null && al.show" :item="al" />
                        </template>
                    </div>
                    <Files class="mb-3" v-if="$page.props.files" :files="$page.props.files" :link="$page.props.filesLink" />
                    <slot />
                </el-col>
                <el-col v-if="$page.props.layout && $page.props.layout.tabs" :span="$page.props.layout && $page.props.layout.large ? 3 : 4">
                    <el-affix  position="top" :offset="10">
                        <SidebarNav :tabs="$page.props.layout.tabs" active="" />
                    </el-affix>
                </el-col>
            </el-row>
        </div>
    </AuthenticatedLayout>
</el-config-provider>
</template>

<script setup>
import * as Icons from "@element-plus/icons-vue";
import {
    Head
} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SidebarNav from "../SidebarNav.vue";
import Card from "../Card.vue";
import Files from "../Files.vue";
import Alert from "../Alert.vue";

import "element-plus/theme-chalk/index.css";
import ruRu from "element-plus/es/locale/lang/ru";
import {
    ElConfigProvider
} from "element-plus";
import "dayjs/locale/ru";
import dayjs from "dayjs";
dayjs.locale("ru", {
    weekStart: 1, // Понедельник — первый день недели
});
const {
    extraSize
} = defineProps({
    extraSize: {
        type: Number,
        default: 0
    }
})
</script>
