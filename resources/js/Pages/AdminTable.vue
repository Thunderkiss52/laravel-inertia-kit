<script setup>
import * as Icons from '@element-plus/icons-vue'
import { Link, Head, router, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
const { item, link, fields, breadcrumbs, label, actions } = defineProps({ item: Object, link: String, fields: Array, breadcrumbs: Object, label: String, actions: Array });
import AdminForm from './AdminForm.vue';

async function showDialog(row = null, column = null, event = null) {
    router.get(row.link);
}

const changeFilters = (newFilters) => {
    changePage(1, query.value);
}

const modal = ref(false);
const items = ref([]);
const loadItems = ref(false);
const query = ref('');

onMounted(() => {
    changePage(1);
});

function changePage(page, q = '') {
    loadItems.value = false;
    axios.get('' + `?search=${q}`, {
        headers: {
            'x-inertia': true,
            'x-inertia-partial-component': 'Template/Table',
            'x-inertia-partial-data': 'items',
            'x-inertia-version': usePage().version
        }
    })
        .then((res) => {
            items.value = res.data.props.items;
            loadItems.value = true;
        });
}


</script>
<template>
    <el-row>

        <el-col :span="8">

            <el-breadcrumb v-if="breadcrumbs" :separator-icon="Icons.ArrowRight">
                <el-breadcrumb-item v-for="(blink, bname) in breadcrumbs">
                    <Link :href="blink">{{ bname }}</Link>
                </el-breadcrumb-item>
                <el-breadcrumb-item>{{ item.name }}</el-breadcrumb-item>
            </el-breadcrumb>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mt-3">{{ label }}</h2>
        </el-col>
        <el-col :span="16">
            <div style="float: right;">
                <a v-for="act in actions" :href="act.url">
                    <el-button :disabled="!loadItems || (items && items.length == 0)" target="_blank" class="mr-2"
                        :icon="Icons[act.attributes.icon]" :type="act.attributes.type">{{ act.title }}</el-button>
                </a>
                <a :href="`?search=${query}&export`">
                    <el-button :disabled="!loadItems || (items && items.length == 0)" target="_blank" class="mr-2"
                        :icon="Icons.Download" type="success">Download</el-button>
                </a>
                <el-button @click="modal = true" :icon="Icons.Plus" type="primary">Add</el-button>
            </div>
        </el-col>
    </el-row>
        <div class="mt-3">
            <div class="overflow-hidden">
                <el-input clearable :prefix-icon="Icons.Search" size="large" @input="changeFilters()" v-model="query"
                    placeholder="Search..." class="mb-3" />
                <el-card shadow="never" v-if="!loadItems">
                    <el-skeleton animated>

                    </el-skeleton>
                </el-card>
                <el-table @row-click="showDialog" v-else-if="items && items.length > 0" :data="items">
                    <el-table-column :width="95" v-if="Object.keys(items[0]).includes('avatar')">
                        <template #default="{ row }">
                            <el-avatar :src="row.avatar" :size="70"></el-avatar>
                        </template>
                    </el-table-column>
                    <template v-for="ikey in Object.keys(items[0])">

                        <el-table-column v-if="ikey != 'link' && ikey != 'avatar' && ikey != 'id'" :prop="ikey"
                            :label="ikey">
                            <template #default="{ row }">
                                <Link v-if="row[ikey] && Object.keys(row[ikey]).includes('link')"
                                    :href="row[ikey].link">
                                <el-link :underline="false" type="primary">
                                    {{ row[ikey].name }}
                                </el-link>
                                </Link>
                                <template v-else>
                                    {{ row[ikey] }}
                                </template>
                            </template>
                        </el-table-column>
                    </template>
                </el-table>
                <el-card shadow="never" v-else>
                    <el-empty />
                </el-card>
            </div>
        </div>
    <el-drawer v-model="modal" size="540px">
        <template #header>
            <h1 class="el-drawer__title">Add new</h1>
        </template>
        <AdminForm @success="() => { modal = false; changeFilters() }" v-if="fields" :link="link" :fields="fields"
            submitLabel="Add" />
    </el-drawer>
</template>