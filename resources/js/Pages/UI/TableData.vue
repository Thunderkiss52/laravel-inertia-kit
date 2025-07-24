<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import * as Icons from "@element-plus/icons-vue";
import { Link, Head, router, usePage } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";
import { ElMessage } from 'element-plus';
import AdminForm from "@thunderkiss52/AdminForm.vue";
import DrawerForm from "@thunderkiss52/DrawerForm.vue";
import DrawerMultipleLoadForm from "@thunderkiss52/DrawerMultipleLoadForm.vue";
import SearchTable from '@thunderkiss52/SearchTable.vue';

const { locale, link, fields, breadcrumbs, label, actions, filters, items, short, modalwidth } =
defineProps({
    locale: Object,
    headers: Array,
    link: String,
    draftsLink: {
        type: String,
        default: null
    },
    exportTemplateLink: {
        type: String,
        default: null
    },
    short: {
        type: Boolean,
        default: false,
    },
    initdata: {
        type: Object,
        default: {},
    },
    fields: Array,
    breadcrumbs: Object,
    label: String,
    modalwidth: {
        type: Number,
        default: 540
    },
    actions: Array,
    filters: Array,
    lazy: Boolean,
    items: Array,
    docs: Array,
    actions: Array,
    tabs: {
        default: false
    },
    columnWidth: Object
});
const page = ref(1);
const modal = ref(false);
const loadItems = ref(true);
const multipleModal = ref(false);

const changeFilters = (newFilters) => {
    table.value.refetch(1);
};

const itemsData = ref(items ?? null);
const table = ref(null);

</script>
<template>
    <AuthenticatedLayout>
        <Head :title="label" />
        <template #header>
            <el-breadcrumb
                v-if="breadcrumbs"
                :separator-icon="Icons.ArrowRight"
            >
                <el-breadcrumb-item v-for="b in breadcrumbs">
                    <Link :href="b.url">{{ b.title }}</Link>
                </el-breadcrumb-item>
                <el-breadcrumb-item>{{ label }}</el-breadcrumb-item>
            </el-breadcrumb>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mt-3">
                {{ label }}
            </h2>
            <el-pagination v-if="itemsData && itemsData.current_page" v-model:current-page="itemsData.current_page" @change="(p) => table.refetch(p)"
                :page-count="itemsData.last_page" background layout="prev, pager, next" />
        </template>
        <template #actions>
            <a v-for="act in actions" :href="act.url">
                <el-button
                    :disabled="!loadItems || (itemsData && (itemsData.data ? itemsData.data.length == 0 : itemsData.length == 0))"
                    target="_blank"
                    class="mr-2"
                    :icon="Icons[act.attributes.icon]"
                    :type="act.attributes.type"
                    >{{ act.title }}</el-button
                >
            </a>
            <el-button v-if="fields" @click="multipleModal = true" :icon="Icons.Upload"
                >{{$page.props.locale.load}}</el-button
            >
            <el-button v-if="fields" @click="modal = true" :icon="Icons.Plus" type="primary"
                >{{locale.add}}</el-button
            >
        </template>

        <div :class="{'max-w-7xl mx-auto' : !filters && short }">
            <SearchTable ref="table"
            :link="link"
            :modalwidth="modalwidth"
            :docs="docs"
            :actions="actions"
            :tabs="tabs"
            :label="label"            
            :lazy="lazy" 
            :headers="headers" 
            :search="true" 
            :columnWidth="columnWidth" v-model:load="loadItems" v-model:items="itemsData" :filters="filters" />
        </div>

        <DrawerMultipleLoadForm
            v-if="fields"
            :label="$page.props.locale.multipleUpload"
            v-model="multipleModal"
            :fields="fields"
            :link="link"
            :templateLink="exportTemplateLink"
            @success="() => changeFilters()"
            :submitLabel="locale.add"
            method="POST"
        />

        <DrawerForm
            v-if="fields"
            :size="`${modalwidth}px`"
            :label="locale.add"
            :draftsLink="draftsLink"
            v-model="modal"
            :initdata="initdata"
            :fields="fields"
            :link="link"
            :successclear="true"
            @success="() => changeFilters()"
            :submitLabel="locale.add"
            method="POST"
        />
    </AuthenticatedLayout>
</template>
