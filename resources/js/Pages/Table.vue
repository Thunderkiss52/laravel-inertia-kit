<template>

    <Head>
        <title>{{ props.label }}</title>
    </Head>
    <AuthenticatedLayout ref="layout">
        <template #header>
            <el-row style="width: 100%;" v-if="selected_rows.length > 0" :gutter="10">

                <el-col :span="6">
                    <h1><el-button @click="table.clearSelection();" :icon="Icons.Close" size="small" circle
                            style="width: 30px;" type="danger" /> Selected {{ selected_rows.length }}</h1>

                </el-col>
                <el-col :span="18">
                    <div class="select_btns">
                        <el-dropdown v-if="props.docs" @command="proceedDocs">
                            <el-button :icon="Icons.Document" size="small" type="primary">Documents<el-icon
                                    class="el-icon--right">
                                    <Icons.ArrowDown />
                                </el-icon></el-button>
                            <template #dropdown>
                                <el-dropdown-menu>
                                    <el-dropdown-item :command="doc" v-for="doc in props.docs">{{ doc.label
                                        }}</el-dropdown-item>
                                </el-dropdown-menu>
                            </template>
                        </el-dropdown>
                        <el-dropdown v-if="props.actions" @command="proceedActions">
                            <el-button :icon="Icons.Memo" size="small" type="primary">Actions<el-icon
                                    class="el-icon--right">
                                    <Icons.ArrowDown />
                                </el-icon></el-button>
                            <template #dropdown>
                                <el-dropdown-menu>
                                    <el-dropdown-item :command="action" v-for="action in props.actions">{{
                                        action.label }}</el-dropdown-item>
                                </el-dropdown-menu>
                            </template>
                        </el-dropdown>
                        <el-button :icon="Icons.Delete" size="small" type="danger">Delete</el-button>
                    </div>
                </el-col>
            </el-row>

            <el-row v-else :gutter="10" style="width: 100%;">

                <el-col :span="6">
                    <h1><el-tag disable-transitions effect="dark">{{ props.paginate.count }}
                            records</el-tag>
                        {{ props.label }}</h1>
                </el-col>
                <el-col :span="8">
                    <el-pagination v-model:current-page="props.paginate.current" @change="changePage"
                        :page-count="props.paginate.pages" background layout="prev, pager, next" />
                </el-col>
                <el-col :span="10">
                    <div style="float: right; display: flex; gap: 10px;">
                        <!--<el-select v-model="value" placeholder="Отображать по" style="width: 200px">
                    <el-option v-for="item in options" :key="item.value" :label="item.label"
                        :value="item.value" />
                </el-select> -->
                        <el-button @click="download()" :icon="Icons.Download" type="success">Download</el-button>
                        <el-button v-if="props.perms.create" @click="() => { modal.type = 'create'; modal.open = true }"
                            :icon="Icons.Plus" type="primary">Add
                            {{ props.label }}</el-button>

                    </div>
                </el-col>
            </el-row>
        </template>
        <div class="py-4">
            <div class="mx-auto sm:px-6 lg:px-8">
                <section>

                    <el-row :gutter="10">
                        <el-col :span="1" v-if="!setting.filter">
                            <el-button class="filter" @click="setting.filter = true" size="small">Filters</el-button>
                        </el-col>
                        <el-col :span="4" v-if="setting.filter">
                            <el-card shadow="never" style="margin-bottom: 10px;">
                                <el-row :gutter="10">
                                    <el-col :span="20">
                                        <el-input v-model="inputext.test" :prefix-icon="Search"
                                            :placeholder="`Search ${props.label}`" />
                                    </el-col>
                                    <el-col :span="4">
                                        <el-button style="width: 100%;" :icon="Icons.Search" type="primary" />
                                    </el-col>
                                </el-row>

                            </el-card>
                            <el-card shadow="never">
                                <template #header>
                                    <el-row>
                                        <el-col :span="12">
                                            <el-button style="font-size: 15px;" @click="setting.filter = false"
                                                size="small" :icon="Icons.Fold">Filters</el-button>

                                        </el-col>
                                        <el-col :span="12">
                                            <el-button @click="changeFilters()" link
                                                style="float: right;">Apply</el-button>

                                        </el-col>
                                    </el-row>
                                </template>
                                <AdminForm :vertical="true" ref="form" :hideSubmit="true" :enableSaveStatus="false"
                                    @success="() => { modal.open = false }" method="POST" submitLabel="Apply"
                                    :link="props.link" :fields="filterFields()" />
                            </el-card>
                        </el-col>
                        <el-col :span="setting.filter ? 20 : 23">
                            <el-table ref="table" @selection-change="handleSelectionChange"
                                v-if="props.data && props.data.length > 0" @row-click="showDialog"
                                @sort-change="changeSort" :default-sort="{ prop: 'date', order: 'descending' }"
                                :data="props.data" style="width: 100%">
                                <el-table-column type="selection" width="30" />
                                <el-table-column sortable="custom" prop="id" width="65" label="№">
                                </el-table-column>
                                <template v-for="column in Object.keys(props.columns)">
                                    <el-table-column
                                        :sortable="props.sorting && props.sorting.includes(column) ? 'custom' : null"
                                        :prop="props.many && props.many[column] ? `${column}_count` : column"
                                        :label="props.columns[column]">
                                        <template #default="scope" v-if="props.belongs && props.belongs[column]">
                                            <el-link :underline="false" type="primary">
                                                <Link :href="`${props.belongs[column]}/${scope.row[column].id}`">
                                                {{ scope.row[column].name }}
                                                </Link>
                                            </el-link>
                                        </template>
                                    </el-table-column>
                                </template>
                                <el-table-column prop="actions" v-if="props.actions" fixed="right" label="Actions"
                                    min-width="120">
                                    <template #default="scope">
                                        <el-button @click="proceedAction(action, scope.row, 'action')"
                                            v-for="action in props.actions" link type="primary" size="small">{{
                                                action.label
                                            }}</el-button>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="documents" v-if="props.docs" fixed="right" label="Documents"
                                    min-width="120">
                                    <template #default="scope">
                                        <el-link v-for="doc in props.docs"
                                            :href="`${props.link}/${scope.row.id}/doc/${doc.link}`">{{ doc.label
                                            }}</el-link>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <el-card shadow="never" v-else
                                style="width: 400px; margin: auto; margin-top: 20px; padding: 0px;">
                                <el-empty />
                            </el-card>
                        </el-col>
                    </el-row>

                </section>
            </div>
        </div>
        <el-drawer @closed="closedDialog" :size="secondModal.open ? '1080px' : '540px'" v-model="modal.open"
            direction="rtl">
            <template #header>
                <el-row :gutter="20">
                    <el-col :span="secondModal.open ? 12 : 24">
                        <h1 class="el-drawer__title">{{ modal.type == null ? 'Download' : modal.type == 'edit' ?
                            `Edit ${props.label}` : modal.type == 'create' ? `Create ${props.label}` :
                                `${props.label} ${modal.payload.name ?? modal.payload.id}` }}</h1>
                    </el-col>
                    <el-col v-if="secondModal.open" :span="12">
                        <h1 class="el-drawer__title">{{ secondModal.label }}</h1>
                    </el-col>
                </el-row>
            </template>
            <template #footer>
                <el-row :gutter="20">
                    <el-col :span="secondModal.open ? 12 : 24">
                        <el-button v-if="modal.type == 'edit'" style="width: 100%;" @click=showDialog()
                            type="danger">Close</el-button>
                    </el-col>
                    <el-col :span="12" v-if="secondModal.open">
                        <el-button style="width: 100%;" @click=closeSecondary() type="danger">Close</el-button>
                    </el-col>
                </el-row>
            </template>
            <el-row :gutter="30">
                <el-col :span="secondModal.open ? 12 : 24">
                    <el-result v-if="modal.error" icon="error" title="Error" sub-title="Failed to load data">
                        <template #extra>
                            <el-button @click="showDialog(modal.payload)" type="primary">Repeat</el-button>
                        </template>
                    </el-result>
                    <el-skeleton v-else-if="modal.type == null" :rows="7" animated />
                    <template v-if="modal.type == 'edit'">

                        <AdminForm @success="() => { modal.open = false; showDialog(modal.payload) }" method="PATCH"
                            :initdata="processEditData(modal.payload)" submitLabel="Update"
                            :link="`${props.link}/${modal.payload.id}`" :fields="props.fields.edit" />
                    </template>
                    <template v-if="modal.type == 'create'">
                        <AdminForm @success="() => { modal.open = false }" method="POST" submitLabel="Create"
                            :link="props.link" :fields="props.fields.create" />
                    </template>
                    <template v-if="modal.type == 'info'">


                        <template v-if="props.tabs || props.docs || props.actions || true">
                            <el-segmented style="width: 100%; margin-bottom: 20px" v-model="activeTab" :options="[
                                ...[{
                                    label: 'Data',
                                    value: 'info',
                                    icon: 'Tickets'
                                }],
                                ...props.tabs ?? [],

                                ...((props.docs && props.docs.length > 0) ? [{
                                    label: 'Documents',
                                    value: 'docs',
                                    icon: 'Document'
                                }] : []),
                                ...((props.actions && props.actions.length > 0) ? [{
                                    label: 'Actions',
                                    value: 'actions',
                                    icon: 'Memo'
                                }] : []),
                                ...(true ? [{
                                    label: 'Logs',
                                    value: 'logs',
                                    icon: 'View'
                                }] : [])
                            ]">
                                <template #default="{ item }">
                                    <div class="flex flex-col items-center gap-2 p-2">
                                        <el-icon size="20">
                                            <component :is="Icons[item.icon]" />
                                        </el-icon>
                                        <div>{{ item.label }}</div>
                                    </div>
                                </template>
                            </el-segmented>
                        </template>

                        <template v-if="activeTab == 'info'">

                            <el-descriptions :column="1">
                                <template v-for="column in Object.keys(props.columns)">
                                    <template v-if="props.many && props.many[column]">
                                        <h2 style="padding-bottom: 15px;">{{ props.columns[column] }}</h2>
                                        <template v-for="item in modal.payload[column]">
                                            <el-descriptions-item :label="item.name">{{ item.id
                                                }}</el-descriptions-item>
                                            <!-- <div class="more-info-item">
                                                <p> <el-text class="mx-1" type="info">{{ item.name }}</el-text></p>
                                                <p>{{ item.id }}</p>
                                            </div> -->
                                        </template>
                                    </template>
                                    <template v-else>
                                        <el-descriptions-item :label="props.columns[column]">

                                            <template v-if="props.belongs && props.belongs[column]">
                                                <el-link :underline="false" type="primary">
                                                    <Link
                                                        :href="`${props.belongs[column]}/${modal.payload[column].id}`">
                                                    {{ modal.payload[column].name }}
                                                    </Link>
                                                </el-link>
                                            </template>
                                            <template v-else>{{ modal.payload[column] }}</template>

                                        </el-descriptions-item>

                                        <!--<div class="more-info-item">
                                            <p> <el-text class="mx-1" type="info">{{ props.columns[column] }}</el-text>
                                            </p>
                                            </div> -->
                                    </template>
                                </template>

                            </el-descriptions>
                            <div style="display: flex;">
                                <el-button v-if="props.perms.delete" @click="deleteRecord(modal.payload.id)"
                                    style="width: 100%;" type="danger">Delete</el-button>
                                <el-button v-if="props.perms.edit" @click="editRecord(modal.payload)"
                                    style="width: 100%;" type="primary">Edit</el-button>
                            </div>
                        </template>

                        <template v-else-if="activeTab == 'logs'">
                            <div style="min-height: 200px; max-height: 500px;" v-loading="loading.logs">
                                <template v-if="!loading.logs">
                                    <el-empty v-if="logs.length == 0" description="No events" />
                                    <el-card v-else v-for="log in logs" @click="openSecondary(log.event, log)"
                                        :class="{ 'card1': true, 'card_active': secondModal.payload && secondModal.payload.id == log.id }"
                                        style="background-color: var(--el-color-secondary); cursor: pointer; margin-bottom: 15px;"
                                        shadow="hover">
                                        <div class="more-info-item">
                                            <p> <el-text class="mx-1">Action</el-text></p>
                                            <p>{{ log.event }}</p>
                                        </div>
                                        <div class="more-info-item">
                                            <p> <el-text class="mx-1">Fields changed</el-text></p>
                                            <p>{{ log.changes.length }}</p>
                                        </div>
                                        <div class="more-info-item">
                                            <p> <el-text class="mx-1">User</el-text></p>
                                            <p>{{ log.user.name }}</p>
                                        </div>
                                        <div class="more-info-item">
                                            <p> <el-text class="mx-1">Datatime</el-text></p>
                                            <p>{{ new Date(log.updated_at).toLocaleString() }}</p>
                                        </div>
                                    </el-card>
                                </template>
                            </div>
                        </template>

                        <template v-else>
                            <template v-if="tabInfo.type == 'cards'">
                                <el-button @click="showAddCardForm(tabInfo)" :icon="Icons.Plus"
                                    v-if="tabInfo.fields_create" style="width: 100%; margin-bottom: 10px;"
                                    type="primary">Add</el-button>
                                <div style="max-height: 500px;" v-if="modal.payload[tabInfo.data].length > 0">
                                    <el-card v-for="item1 in modal.payload[tabInfo.data]"
                                        @click="openSecondary(item1.label, item1)"
                                        :class="{ 'card1': true, 'card_active': secondModal.payload && secondModal.payload.id == item1.id }"
                                        style="background-color: var(--el-color-secondary); cursor: pointer; margin-bottom: 15px;"
                                        shadow="hover">
                                        <div v-for="k in Object.keys(tabInfo.card)" class="more-info-item">
                                            <p> <el-text class="mx-1">{{ tabInfo.card[k] }}</el-text></p>
                                            <p>{{ item1[k] }}</p>
                                        </div>
                                    </el-card>
                                </div>
                                <el-empty v-else />
                            </template>
                            <el-row v-else-if="tabInfo.type == 'docs'">
                                <el-col v-for="doc in props.docs" :span="24">
                                    <DocumentButton style="margin-bottom: 10px;"
                                        :link="`${props.link}/${modal.payload.id}/doc/${doc.link}`"
                                        :label="doc.label" />
                                </el-col>
                            </el-row>
                            <el-row v-else-if="tabInfo.type == 'actions'">
                                <el-col v-for="doc in props.actions" :span="24">
                                    <ActionButton style="margin-bottom: 10px;"
                                        :link="`${props.link}/${modal.payload.id}/action/${doc.link}`"
                                        :label="doc.label" />
                                </el-col>
                            </el-row>
                        </template>
                    </template>
                </el-col>

                <el-col v-if="secondModal.open" :span="12">
                    <template v-if="tabInfo.type == 'logs'">
                        <div v-for="item2 in secondModal.payload.changes" class="more-info-item">
                            <p> <el-text class="mx-1" type="info">{{ item2.key }}</el-text></p>
                            <p>{{ item2.old }}</p>
                            <p>{{ item2.new }}</p>
                        </div>
                    </template>
                    <template v-else>
                        <template v-if="secondModal.isCreate">
                            <AdminForm method="POST" :link="`${props.link}/${modal.payload.id}/${tabInfo.link}`"
                                @success="refetch()" v-if="tabInfo.fields_create" submitLabel="Add"
                                :fields="tabInfo.fields_create" />
                        </template>
                        <template v-else>
                            <div v-for="item2 in Object.keys(tabInfo.card)" class="more-info-item">
                                <p> <el-text class="mx-1" type="info">{{ tabInfo.card[item2] }}</el-text></p>
                                <p>{{ secondModal.payload[item2] }}</p>
                            </div>
                            <div v-for="item2 in Object.keys(tabInfo.info)" class="more-info-item">
                                <p> <el-text class="mx-1" type="info">{{ tabInfo.info[item2] }}</el-text></p>
                                <p>{{ secondModal.payload[item2] }}</p>
                            </div>
                            <template v-if="tabInfo.fields">
                                <el-skeleton v-if="secondModal.loading" :rows="7" animated />
                                <template v-else>
                                    <AdminForm method="PATCH" @success="refetch()"
                                        :link="`${props.link}/${modal.payload.id}/${tabInfo.link}/${secondModal.payload.id}`"
                                        :initdata="secondModal.payload" :fields="tabInfo.fields" />

                                </template>
                            </template>
                        </template>
                    </template>
                </el-col>
            </el-row>
        </el-drawer>
    </AuthenticatedLayout>
</template>
<script setup>
import AdminForm from '@/Components/AdminForm.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import * as Icons from '@element-plus/icons-vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';
import { computed, reactive, ref, watch } from 'vue';
import DocumentButton from '@/Components/DocumentButton.vue';
import ActionButton from '@/Components/ActionButton.vue';
import fileDownload from 'js-file-download';
const modal = reactive({
    error: false,
    open: false,
    //loading: false,
    type: null,//string//"info" | "create"
    payload: { id: null }
});

const setting = reactive({
    filter: true
});

const loading = reactive({
    logs: false,
});

const layout = ref(null);
const table = ref(null);

const selected_rows = ref([]);


const tabInfo = computed(() => {
    if (activeTab.value == 'docs') {
        return {
            label: 'Documents',
            type: 'docs',
            value: 'docs',
            icon: 'Document'
        }
    } else if (activeTab.value == 'actions') {
        return {
            label: 'Actions',
            type: 'actions',
            value: 'actions',
            icon: 'Memo'
        }
    } else if (activeTab.value == 'logs') {
        return {
            label: 'Logs',
            type: 'logs',
            value: 'logs',
            icon: 'Memo'
        }
    } else {
        let res = props.tabs.filter((tab) => tab.value == activeTab.value);
        console.log(props.tabs);
        console.log(activeTab.value);
        return res.length == 1 ? res[0] : null;
    }
});

function download() {
    axios.get(`${props.link}/data/export`, {
        responseType: 'blob'
    }).then((response) => {
        fileDownload(response.data, `${props.label}.xlsx`);
    })
        .catch((error) => ElMessage.error("Failed to unload data"));
}

function handleSelectionChange(val) {
    //console.log(val);
    selected_rows.value = val;
}

function proceedAction(action, row, type) {
    layout.value.addAction({
        label: action.label,
        link: `${props.link}/${row.id}/${type}/${action.link}`,
        type: type
    });
}

function proceedDocs(doc) {
    selected_rows.value.forEach(row => {
        proceedAction(doc, row, 'doc');
    });
}

function proceedActions(action) {
    selected_rows.value.forEach(row => {
        proceedAction(action, row, 'action');
    });
}

function processEditData(payload) {
    //return payload;
    if (!payload) {
        return {};
    }
    Object.keys(payload).forEach(key => {
        if (Array.isArray(payload[key])) {
            console.log(key);
            console.log(payload[key]);
            payload[key] = payload[key].map((option) => option.id);
        }
    });
    return payload;
}

let activeTab = ref('info');

watch(activeTab, async (newValue, oldValue) => {
    closeSecondary();
    if (newValue == 'logs') {
        //console.log('test111');
        loading.logs = true;
        axios.get(`${props.link}/${modal.payload.id}/logs`)
            .then((response) => {
                logs.value = response.data;
            })
            .catch((e) => {
                ElMessage.error("Failed to load data");
            })
            .finally(() => {
                loading.logs = false;
            });
    } else {
        loading.logs = true;
        logs.value = [];

    }
});

const secondModal = reactive({
    label: null,
    loading: false,
    isCreate: false,
    open: false,
    type: null,//string//"info" | "create"
    payload: { id: null }
});

const inputext = useForm({
    test: ''
});

let backup_data = null;

async function closedDialog() {
    modal.type = null;
    backup_data = null;
    secondModal.open = false;
    secondModal.payload = null;
    activeTab.value = 'info';
}

async function showAddCardForm(tab) {
    closeSecondary();
    openSecondary(`Add new ${tab.label}`, null, true);
}

async function openSecondary(label, payload = null, isCreate = false) {
    //closeSecondary();
    if (secondModal.open) {
        secondModal.loading = true;
    }
    secondModal.isCreate = isCreate;
    secondModal.payload = payload;
    secondModal.label = label;
    secondModal.open = true;
    setTimeout(() => {

        secondModal.loading = false;
    }, 1);
}

function closeSecondary() {
    secondModal.open = false;
    secondModal.isCreate = false;
    secondModal.payload = null;
    secondModal.label = null;
}

function filterFields() {
    let result = [];
    props.fields.filter.forEach(function (row) {
        //console.log(props.filtering);
        result.push(row.filter((field) => props.filtering.includes(field.key)))
    });
    return result;
}

async function showDialog(row = null, column = null, event = null) {
    if (!column || (column.property != 'documents' && column.property != 'actions')) {
        modal.error = false;
        //modal.payload = row;
        modal.type = null;
        modal.open = true;
        if (!row && backup_data) {
            modal.payload = backup_data;
            modal.type = 'info';
        } else {

            axios.get(`${props.link}/${row.id}`)
                .then((response) => {

                    modal.payload = response.data;
                    //console.log(modal.payload);
                    modal.type = 'info';

                })
                .catch((e) => {
                    modal.error = true;
                });
        }
    }

}

async function refetch() {
    //modal.loading = false;
    closeSecondary();
    axios.get(`${props.link}/${modal.payload.id}`)
        .then((response) => {
            modal.payload = response.data;
            //console.log(modal.payload);
            modal.type = 'info';
        })
        .catch((e) => {
            modal.error = true;
        })
        .finally(() => {


            //modal.loading = true;
        });
}

async function editRecord(payload) {
    backup_data = JSON.parse(JSON.stringify(payload));
    modal.type = null;
    if (props.many) {
        Object.keys(props.many).forEach(key => {
            payload[key] = payload[key].map(option => option.id);
        });
    }
    modal.type = 'edit';
    modal.open = true;
}

//let isLoad = true;
function changeSort({ column, prop, order }) {
    prop = order == 'descending' ? `-${prop}` : prop;
    router.get(`?sort=${prop}`, {}, {
        preserveScroll: true,
        preserveState: true,
        onBefore: (function () {
            //isLoad = false;
        }),
    })
}

const form = ref(null);

function changeFilters() {
    let formdata = form.value.getValues().data();
    formdata._method = null;
    formdata = Object.fromEntries(Object.entries(formdata).filter(([_, v]) => v != null));
    let dat = {};

    Object.keys(formdata).forEach((key) => {
        dat[`filter[${key}]`] = formdata[key];
    })

    let data = new URLSearchParams(dat).toString();
    console.log(data);
    router.get(`?${data}`, {}, {
        preserveScroll: true,
        preserveState: true,
        onBefore: (function () {
            //isLoad = false;
        }),
    })
}

function changePage(currentPage, pageSize) {
    router.get(`?page=${currentPage}`, {}, {
        preserveScroll: true,
        preserveState: true,
        onBefore: (function () {
            //isLoad = false;
        }),
    })
}

function deleteRecord(id) {
    ElMessageBox.confirm(
        `Do you want to delete ${props.label} №${id}?`,
        'Attention',
        {
            type: 'warning',
        }
    )
        .then(() => {
            router
                .delete(`${props.link}/${id}`, {
                    onSuccess: (() => {
                        ElMessage.success(`${props.label} deleted`);
                        modal.open = false;
                    }),
                    onError: (() => ElMessage.error(`Failed to delete ${props.label}`))
                })

        })
}

function generateLink(link, row) {
    let parameters = {};
    Object.keys(link.params).forEach(key => {
        parameters[key] = row[link.params[key]];
    });
    return route(link.path, parameters);
}

const logs = ref([]);

const props = defineProps({
    filtering: { required: true, type: Array },
    label: { required: true, type: String },
    columns: { required: true, type: Object },
    sorting: { required: true, type: Array },
    belongs: { required: false, type: Object },
    many: { required: false, type: Object },
    data: { required: true, type: Array },
    docs: { required: true, type: Array },
    breadcrumbs: { required: true, type: Array },
    includes: Array,
    paginate: Array,
    perms: { required: true, type: Object },
    link: { required: true, type: String },
    actions: { required: false, type: Array },
    buttons: { required: false, type: Array },
    tabs: { required: false, type: Array },
    fields: { type: Object }
    // items: { required: true, type: Array<cardInterface> }
});
</script>

<style>
.select_btns {
    display: flex;
    gap: 10px;
    float: right;
}

.select_btns button {
    padding: 1.8px 10px;
}
</style>