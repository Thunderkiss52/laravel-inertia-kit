<template>
<el-row :gutter="10">
    <el-col v-if="filters && showFilters" :span="5">
        <AdminFilter @filtered="filterPage" :filters="filters" />
    </el-col>
    <el-col :span="filters && showFilters ? 19 : 24">
    <div class="flex gap-2 mb-3">
        <el-button size="large" v-if="filters" @click="showFilters = !showFilters" :icon="Icons.Filter">{{$page.props.locale.filters}}</el-button>
        <el-input
            v-if="search"
            clearable
            :prefix-icon="Icons.Search"
            size="large"
            @input="() => changePage(items.current_page, query)"
            v-model="query"
            :placeholder="$page.props.locale.search+'...'"
            
        />
        <!-- <a :href="`${searchLink}&export`"> -->
        <!-- <el-button size="large" type="success" :icon="Icons.Download">{{$page.props.locale.download}}</el-button> -->
        
         <el-dropdown :disabled="!loadItems" :hide-on-click="false" style="display: contents" size="large" type="success" split-button @click="exportData()">
      {{$page.props.locale.download}}
      <template #dropdown>
        <el-dropdown-menu>
          <el-dropdown-item v-for="(ilabel, ikey) in (headers ?? headersData)">
           <el-checkbox v-model="exportHeaders[ikey]">
            <template v-if="ilabel == 'link'">Link</template>
            <template v-else-if="ilabel == 'avatar'">Image</template>
            <template v-else>{{ ilabel }}</template>
            </el-checkbox>
          </el-dropdown-item>
        </el-dropdown-menu>
      </template>
    </el-dropdown>
        <!-- </a> -->
        </div>
        <el-card shadow="never" v-if="!loadItems">
            <el-skeleton animated> </el-skeleton>
        </el-card>
        <el-table
            @sort-change="changeSort"
            :default-sort="sortKey && sortKey.length > 0 ? { prop: getHeaderNum(sortKey.replace('-', '')), order:  sortKey.charAt(0) == '-' ? 'descending' : 'ascending' } : {}"
            @row-click="showDialog"
            v-else-if="items && (items.data ? items.data.length > 0 : items.length > 0)"
            :data="items.data ?? items"
        >
            <el-table-column
                prop="avatar"
                :width="95"
                v-if="(headers ?? headersData).includes('avatar')"
            >
                <template #default="{ row }">
                    <el-avatar
                        v-if="row[(headers ?? headersData).findIndex(p => p == 'avatar')] != null"
                        style="cursor: zoom-in"
                        :src="row[(headers ?? headersData).findIndex(p => p == 'avatar')]"
                        :size="70"
                    ></el-avatar>
                </template>
            </el-table-column>
            <template
                v-for="(ilabel, ikey) in (headers ?? headersData)"
            >
                <el-table-column v-if="!['link', 'avatar', 'id'].includes(ilabel)"
                    sortable="custom"
                    :label="ilabel"
                    :width="Object.keys(columnWidth).includes(ilabel) ? columnWidth[ilabel] + 20 : null"
                    :prop="String(ikey)"
                >
                    <template #default="{ row }">
                        <!-- <Link
                            v-if="
                                row[ikey] &&
                                Object.keys(row[ikey]).includes(
                                    'url'
                                )
                            "
                            :href="row[ikey].url"
                        > -->
                            <el-link v-if="
                                row[ikey] &&
                                Object.keys(row[ikey]).includes(
                                    'url'
                                )
                            " @click="getData(row[ikey].url)"
                                :underline="false"
                                type="primary"
                            >
                                {{ row[ikey].title }}
                            </el-link>
                        <!-- </Link> -->
                        <template
                            v-else-if="Array.isArray(row[ikey])"
                        >
                            <div class="flex flex-col">
                            <!-- <Link
                                
                                :href="e.value"
                            > -->
                                <el-link style="width: 100%" v-for="e in row[ikey]" @click="getData(row[ikey].url)"
                                    :underline="false"
                                    type="primary"
                                >
                                    {{ e.label }}
                                </el-link>
                                </div>
                            <!-- </Link> -->
                        </template>
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
    </el-col>
</el-row>

        <el-drawer style="max-width: 100vw" @closed="closedDialog" :size="secondModal.open ?  '1080px' :  modal.open && ['edit', 'create'].includes(modal.type)  ? (modalwidth)+'px' : `540px`" v-model="modal.open"
            direction="rtl">
            <template #header>
                <el-row :gutter="20">
                    <el-col :span="secondModal.open ? 12 : 24">
                        <!-- <el-row> -->
                            <!-- <el-col :span="24"> -->

                                <h1 class="el-drawer__title">{{ modal.type == null ? $page.props.locale.loading : modal.type == 'edit' ?
                            $page.props.locale.edit : modal.type == 'create' ? $page.props.locale.create :
                                `${modal.payload.item.name ?? modal.payload.item.id}` }}</h1>
                            <!-- </el-col> -->
                            <!-- <el-col :span="12">
                                <a class="mr-3 float-right" target="_blank" :href="`${link}/${modal.payload.id}`">
                                <el-button v-if="modal.type == 'info'" type="link" size="small">В новой вкладке</el-button>
                            </a>
                            </el-col> -->
                        <!-- </el-row> -->
                    </el-col>
                    <el-col v-if="secondModal.open" :span="12">
                        <h1 class="el-drawer__title">{{ secondModal.label }}</h1>
                        
                    </el-col>
                </el-row>
            </template>
            <template #footer>
                <el-row :gutter="20">
                    <el-col :span="secondModal.open ? 12 : 24">
                        <el-button v-if="modal.type == 'edit'" style="width: 100%;" @click="showDialog()"
                            type="danger">{{$page.props.locale.close}}</el-button>
                    </el-col>
                    <el-col :span="12" v-if="secondModal.open">
                        <el-button style="width: 100%;" @click=closeSecondary() type="danger">{{$page.props.locale.close}}</el-button>
                    </el-col>
                </el-row>
            </template>
            <el-row :gutter="30">
                <el-col :span="secondModal.open ? 12 : 24">
                    <el-result v-if="modal.error" icon="error" :title="$page.props.locale.error">
                        <template #extra>
                            <el-button @click="showDialog(modal.payload)" type="primary">{{$page.props.locale.retry}}</el-button>
                        </template>
                    </el-result>
                    <el-skeleton v-else-if="modal.type == null" :rows="7" animated />
                    <template v-if="modal.type == 'edit'">

                        <AdminForm @success="() => { getData(modal.payload.link); changePage(items.current_page, query)}" method="PATCH"
                            :initdata="modal.payload.item" :submitLabel="$page.props.locale.update"
                            :link="modal.payload.link" :fields="modal.payload.fields" />
                    </template>
                    <!-- <template v-if="modal.type == 'create'">
                        <AdminForm @success="() => { getData(modal.payload.link); changePage(items.currentPage, query) }" method="POST" submitLabel="Создать"
                            :link="link" :fields="fields" />
                    </template> -->
                    <template v-if="modal.type == 'info'">


                        <template v-if="modal.tabsList.length > 0">
                            <el-segmented style="width: 100%; margin-bottom: 20px" v-model="activeTab" :options="[
                                ...[{
                                    label: $page.props.locale.data,
                                    value: 'info',
                                    icon: 'Tickets'
                                }],
                                ...modal.tabsList
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
                            <template v-for="(cvalue, clabel ) in modal.payload.info.item">
                                <div class="more-info-item">
                                    <p> <el-text class="mx-1" type="info">{{ clabel }}</el-text></p>
                                    <!-- <template v-if="props.belongs && props.belongs[column]">
                                        <el-link :underline="false" type="primary">
                                            <Link :href="`${props.belongs[column]}/${modal.payload[column].id}`">
                                            {{ modal.payload[column].name }}
                                            </Link>
                                        </el-link>
                                    </template> -->
                                    <p align="right">
                                    
                                    <!-- <Link
                                        :href="cvalue.url"
                                    > -->
                                        <el-link
                                        @click="getData(cvalue.url)"
                                        v-if="
                                            cvalue &&
                                            Object.keys(cvalue).includes(
                                                'url'
                                            )
                                        "
                                            :underline="false"
                                            type="primary"
                                        >
                                            {{ cvalue.title }}
                                        </el-link>
                                    <!-- </Link> -->
                                    <template
                                        v-else-if="Array.isArray(cvalue)"
                                    >
                            <div class="flex flex-col">
                                        <!-- <Link
                                            v-for="e in cvalue"
                                            :href="e.value"
                                        > -->
                                            <el-link style="width: 100%"
                                            v-for="e in cvalue"
                                            
                                            @click="getData(e.value)"
                                                :underline="false"
                                                type="primary"
                                            >
                                                {{ e.label }}
                                            </el-link>
                                            </div>
                                        <!-- </Link> -->
                                    </template>
                                    <template v-else>
                                        {{ cvalue }}
                                    </template>
                                    </p>
                                </div>
                                <!-- <template v-if="props.many && props.many[column]">
                                    <h2 style="padding-bottom: 15px;">{{ props.columns[column] }}</h2>
                                    <template v-for="item in modal.payload[column]">
                                        <div class="more-info-item">
                                            <p> <el-text class="mx-1" type="info">{{ item.name }}</el-text></p>
                                            <p>{{ item.id }}</p>
                                        </div>
                                    </template>
                                </template>
                                <template v-else>
                                    <div class="more-info-item">
                                        <p> <el-text class="mx-1" type="info">{{ props.columns[column] }}</el-text></p>
                                        <template v-if="props.belongs && props.belongs[column]">
                                            <el-link :underline="false" type="primary">
                                                <Link :href="`${props.belongs[column]}/${modal.payload[column].id}`">
                                                {{ modal.payload[column].name }}
                                                </Link>
                                            </el-link>
                                        </template>
                                        <p v-else>{{ modal.payload[column] }}</p>
                                    </div>
                                </template> -->
                            </template>
                            <div style="display: flex;">
                                <el-button v-if="modal.payload.info.can_delete" @click="deleteRecord(modal.payload.item.id)"
                                    style="width: 100%;" type="danger">{{$page.props.locale.delete}}</el-button>
                                <el-button v-if="modal.payload.fields && modal.payload.link" @click="editRecord(modal.payload.item)"
                                    style="width: 100%;" type="primary">{{$page.props.locale.edit}}</el-button>
                            </div>
                        </template>

                        <template v-else-if="activeTab == 'logs'">
                            <div style="min-height: 200px; max-height: 500px;" v-loading="loading.logs">
                                <template v-if="!loading.logs">
                                    <el-empty v-if="logs.length == 0" :description="$page.props.locale.emptyLogs" />
                                    <el-card v-else v-for="log in logs" @click="openSecondary(log.event, log)"
                                        :class="{ 'card1': true, 'card_active': secondModal.payload && secondModal.payload.card && secondModal.payload.card.id == log.id }"
                                        style="background-color: var(--el-color-secondary); cursor: pointer; margin-bottom: 15px;"
                                        shadow="hover">
                                        <div class="more-info-item">
                                            <p> <el-text class="mx-1">{{$page.props.locale.action}}</el-text></p>
                                            <p>{{ log.event }}</p>
                                        </div>
                                        <div class="more-info-item">
                                            <p> <el-text class="mx-1">Changed records</el-text></p>
                                            <p>{{ log.changes.length }}</p>
                                        </div>
                                        <div class="more-info-item">
                                            <p> <el-text class="mx-1">{{$page.props.locale.user}}</el-text></p>
                                            <p>{{ log.user.name }}</p>
                                        </div>
                                        <div class="more-info-item">
                                            <p> <el-text class="mx-1">Datetime</el-text></p>
                                            <p>{{ new Date(log.updated_at).toLocaleString() }}</p>
                                        </div>
                                    </el-card>
                                </template>
                            </div>
                        </template>

                        <template v-else>
                            <template v-if="tabInfo.type == 'cards'">
                                <el-skeleton v-if="otherModalTabLoading" animated />
                                <template v-else>
                                <el-button @click="showAddCardForm(tabInfo)" :icon="Icons.Plus"
                                    v-if="tabInfo.fields_create" style="width: 100%; margin-bottom: 10px;"
                                    type="primary">{{$page.props.locale.add}}</el-button>
                                <div style="max-height: 500px;" v-if="tabInfo.items.length > 0">
                                    <el-card v-for="item1 in tabInfo.items"
                                        @click="openSecondary(item1.label, item1)"
                                        :class="{ 'card1': true, 'card_active': secondModal.payload && secondModal.payload.card && secondModal.payload.card.id == item1.card.id }"
                                        style="background-color: var(--el-color-secondary); cursor: pointer; margin-bottom: 15px;"
                                        shadow="hover">
                                        <div v-for="(g, k) in Object.keys(item1.card)" class="more-info-item">
                                            <p> <el-text class="mx-1">{{ g }}</el-text></p>
                                            <p>{{ item1.card[g] }}</p>
                                        </div>
                                    </el-card>
                                </div>
                                <el-empty v-else />
                                </template>
                            </template>
                            <el-row v-else-if="tabInfo.type == 'docs'">
                                <el-col v-for="doc in props.docs" :span="24">
                                    <!-- <DocumentButton style="margin-bottom: 10px;"
                                        :link="route(`document.${doc.link}`, {
                                            id: modal.payload.id
                                        })"
                                        :label="doc.label" /> -->
                                </el-col>
                            </el-row>
                            <el-row v-else-if="tabInfo.type == 'actions'">
                                <el-col v-for="doc in props.actions" :span="24">
                                    <!-- <ActionButton style="margin-bottom: 10px;"
                                        :link="`${props.link}/${modal.payload.id}/action/${doc.link}`"
                                        :label="doc.label" /> -->
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
                            <AdminForm method="POST" :link="secondModal.payload.link"
                                @success="() => refetch()" v-if="tabInfo.fields_create" :submitLabel="$page.props.locale.add"
                                :fields="tabInfo.fields_create" />
                        </template>
                        <template v-else>
                            <template v-if="secondModal.payload && secondModal.payload.data">
                            <div v-for="item2 in Object.keys(secondModal.payload.data)" class="more-info-item">
                                <p> <el-text class="mx-1" type="info">{{ item2 }}</el-text></p>
                                <p>{{ secondModal.payload.data[item2] }}</p>
                            </div>
                                <template v-if="secondModal.payload.documents" shadow="never" class="p-0">
                                    <div class="flex flex-col gap-2">
                                        <a v-for="d in secondModal.payload.documents" :href="d.link">
                                            <el-button style="width: 100%; margin: 0px" :icon="Icons.Document" size="small">{{d.name}}</el-button>
                                        </a>
                                    </div>
                                </template>
                            </template>
                            <!-- <div v-for="item2 in Object.keys(tabInfo.info)" class="more-info-item">
                                <p> <el-text class="mx-1" type="info">{{ tabInfo.info[item2] }}</el-text></p>
                                <p>{{ secondModal.payload[item2] }}</p>
                            </div> -->
                            <template v-if="tabInfo.fields">
                                <el-skeleton v-if="secondModal.loading" :rows="7" animated />
                                <template v-else>
                                    <AdminForm method="PATCH" @success="() => { refetch(); changePage(items.current_page, query) }"
                                        :link="secondModal.payload.link"
                                        :initdata="secondModal.payload.item" :fields="tabInfo.fields" />

                                </template>
                            </template>
                        </template>
                    </template>
                </el-col>
            </el-row>
        </el-drawer>
<vue-easy-lightbox :visible="showImage != null" :imgs="imageLinksArray" :index="showImage" @hide="() => showImage = null" />
</template>
<style>
.el-dropdown .el-button-group {
    display: flex
}


.el-dropdown-menu.el-dropdown-menu--large .el-checkbox {
    height: fit-content;
    padding: 7px 20px;
}

.el-dropdown-menu__item {
    padding: 0px !important;
}

.el-dropdown-menu__item .el-checkbox, .el-dropdown-menu__item .el-checkbox .el-checkbox__label {
    width: 100% !important;
}

</style>
<script setup>
import AdminForm from "@thunderkiss52/AdminForm.vue";
import AdminFilter from "@thunderkiss52/AdminFilter.vue";
import * as Icons from "@element-plus/icons-vue";
import { Link, Head, router, usePage } from "@inertiajs/vue3";
import VueEasyLightbox from 'vue-easy-lightbox';
import {onMounted, ref, reactive, computed, watch} from 'vue';

const query = ref("");
const sortKey = ref(null);
const activeTab = ref('info');
const items = defineModel('items')
const loadItems = defineModel('load')
const headersData = ref([]);
const page = usePage();
const showFilters = ref(false);
const layout = ref(null);
const table = ref(null);
const selected_rows = ref([]);
const exportHeaders = ref({});
const showImage = ref(null);
const imageLinksArray = computed(() => {
    if(!items.value) {
        return [];
    }
    let its = items.value.data ?? items.value;
    if(Array.isArray(its)) {
        return its.map(r => r[(headers ?? headersData.value).findIndex(p => p == 'avatar')]);
    } else {
        return [];
    }
});


function showFullImage(id) {
    showImage.value = imageLinksArray.value.findIndex(p => p == id);
}


if(headers) {
for (let index = 0; index < headers.length; index++) {
    exportHeaders.value[index] = true;
}
}


const secondModal = reactive({
    label: null,
    loading: false,
    isCreate: false,
    open: false,
    type: null,//string//"info" | "create"
    payload: { id: null }
});

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

const otherModalTabLoading = ref(false);

const loading = reactive({
    logs: false,
});

async function showDialog(row = null, column = null, event = null) {
    // console.log(column);
    // console.log();
    if(column.property == 'avatar') {
        var index = (headers ?? headersData.value).findIndex(p => p == column.property);
        showFullImage(row[index]);
    } else if(event.srcElement.className != 'el-link__inner') {//if(!row[column.property] || typeof row[column.property] != 'object') {
        if(tabs) {
            if (!column || (column.property != 'documents' && column.property != 'actions')) {

                var index = (headers ?? headersData.value).findIndex(p => p == "link");
                modal.error = false;
                modal.tabsList = [];
                modal.type = null;
                modal.open = true;
                if (!row && backup_data) {
                    modal.payload = backup_data;
                    modal.type = 'info';
                } else {
                    getData(row[index]);
                }
            }
        } else {
            var index = (headers ?? headersData.value).findIndex(p => p == "link");
            router.get(row[index]);
        }
    }
}

function getData(id) {
    modal.error = false;
    modal.open = true;
    modal.loading = true;
    axios.get(`${id}?format=json`)
    .then((response) => {
        modal.payload = response.data;
        let r = [];
        Object.keys(response.data.tabs ?? []).forEach(element => {
            r.push({
                label: response.data.tabs[element].label,
                value: element,
                icon: response.data.tabs[element].icon
            });
        });
        modal.loading = false;
        modal.tabsList = r;
        modal.type = 'info';
    })
    .catch((e) => {
        modal.error = true;
    });

}

async function editRecord(payload) {
    backup_data = JSON.parse(JSON.stringify(payload));
    modal.type = null;
    modal.type = 'edit';
    modal.open = true;
}

function deleteRecord(id) {
    ElMessageBox.confirm(
        `${page.props.locale.areYouWant} ${page.props.locale.delete} ${label} №${id}?`,
        page.props.locale.attention,
        {
            type: 'warning',
        }
    )
        .then(() => {
            axios.delete(modal.payload.link)
                .then(() => {

                    //${props.label}
                    ElMessage.success(page.props.locale.deleted);
                        modal.open = false;
                        changePage(items.current_page, query.value);
                })
                .catch(() => {
                    ElMessage.error(page.props.locale.error)
                })

        })
}

function processEditData(payload) {
    //return payload;
    let pay = {};
    if (!payload) {
        return {};
    }
    //console.log(Object.keys(payload));
    Object.keys(payload).forEach(key => {
        if (Array.isArray(payload[key])) {
            console.log(key);
            console.log(payload[key]);
            pay[key] = payload[key].map((option) => option.id ?? option);
        } else {
            pay[key] = payload[key];
        }
    });
    return pay;
}


function changeSort({ column, prop, order }) {
    // console.log();
    let k = column.label;
    sortKey.value = order == 'descending' ? `-${k}` : k;
    changePage(1, query.value);
}
watch(activeTab, async (newValue, oldValue) => {
    closeSecondary();
    if (newValue == 'logs') {
        //console.log('test111');
        loading.logs = true;
        axios.get(`${link}/${modal.payload.id}/logs`, {
                headers: {
                    'Content-Type': "application/json"
                }
            })
            .then((response) => {
                logs.value = response.data;
            })
            .catch((e) => {
                ElMessage.error($page.props.locale.error);
            })
            .finally(() => {
                loading.logs = false;
            });
    } else {
        loading.logs = true;
        logs.value = [];

    }
});

function getHeaderNum(l) {
    return (headers ?? headersData.value).findIndex(p => p == l);
}

async function refetch() {
    //modal.loading = false;
    closeSecondary();
    otherModalTabLoading.value = true;
    axios.get(modal.payload.link, {
                headers: {
                    'Content-Type': "application/json"
                }
            })
        .then((response) => {
            modal.payload = response.data;
            otherModalTabLoading.value = false;
            //console.log(modal.payload);
            modal.type = 'info';
        })
        .catch((e) => {
            modal.error = true;
        })
        .finally(() => {
            otherModalTabLoading.value = false;
        });
}


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
    openSecondary(`${page.props.locale.add} New ${tab.label}`, tab, true);
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



const tabInfo = computed(() => {
    if (activeTab.value == 'docs') {
        return {
            label: $page.props.locale.docs,
            type: 'docs',
            value: 'docs',
            icon: 'Document'
        }
    } else if (activeTab.value == 'actions') {
        return {
            label: $page.props.locale.actions,
            type: 'actions',
            value: 'actions',
            icon: 'Memo'
        }
    } else if (activeTab.value == 'logs') {
        return {
            label: $page.props.locale.logs,
            type: 'logs',
            value: 'logs',
            icon: 'Memo'
        }
    } else {
        return modal.payload.tabs[activeTab.value];
    }
});


function handleSelectionChange(val) {
    //console.log(val);
    selected_rows.value = val;
}


const {comp, filters, search, lazy, columnWidth, headers, link, docs, actions, tabs, label, modalwidth} = defineProps({
    comp: {
        type: String,
        default: "@thunderkiss52/UI/TableData"
    },
    filters: Array,
    link: String,
    docs: Array,
    actions: Array,
    tabs: {
        default: false
    },
    modalwidth: {
        type: Number,
        default: 540
    },
    label: String,
    search:  {
        type: Boolean,
        default: false
    },
    lazy:  {
        type: Boolean,
        default: true
    },
    headers: {
        type: Array,
        default: null
    },
    columnWidth: {
        type: Object,
        default: []
    }
});

function filterPage(filterData) {
    filters.value = filterData;
    // console.log(filterData);
    changePage(1);
}

onMounted(() => {
    if(lazy) {
        changePage(1);
    } else {
        loadItems.value = true;
    }
});

defineExpose({
    refetch: (p = 1) => changePage(p),
    getData: (l) => getData(l)
});
let controller = new AbortController();

const searchLink = computed(() => {
    let filtersData = {};
    if (filters && Array.isArray(filters.value)) {
        Object.keys(filters.value).forEach((f) => {
            filtersData[`filter[${f}]`] = filters.value[f];
        });
    }
    return "?" + new URLSearchParams({
        page: items.value && items.value.current_page ? items.value.current_page : 1,
        ...sortKey.value ? {sort: sortKey.value} : {},
        ...query.value ? {search: query.value} : {},
        ...filtersData,
    })
    .toString();
});

function changePage(page) {
    console.log(`changePage ${page}`);
    if(items.value && items.value.current_page) {
            items.value.current_page = page;
    }
    controller.abort();
    controller = new AbortController();
    loadItems.value = false;
    axios.get(searchLink.value, {
            headers: {
                "x-inertia": true,
                "x-inertia-partial-component": comp,
                "x-inertia-partial-data": "items,headers",
                "x-inertia-version": usePage().version,
            },
        signal: controller.signal,
        })
        .then((res) => {
            items.value = res.data.props.items;
            headersData.value = res.data.props.headers;
            for (let index = 0; index < headersData.value.length; index++) {
                exportHeaders.value[index] = true;
            }
            loadItems.value = true;
        })
        .catch((ex) => {
            console.log(ex);
            if(ex.response) {
                ElMessage.error(ex.response.data && ex.response.data.message ? ex.response.data.message : null);
                loadItems.value = true;
                items.value = [];
            }
        })
}

function exportData() {
    window.open(`${searchLink.value}&`+new URLSearchParams({
        export: true,
        headers: Object.keys(exportHeaders.value).filter(key => exportHeaders.value[key] === true).map((key) => (headers ?? headersData.value)[key])
        }), '_self');
}

</script>