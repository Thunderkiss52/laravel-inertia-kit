<template>
<el-select clearable v-if="small" v-model="model" :multiple="multiple" filterable remote reserve-keyword :remote-method="remoteMethod" :loading="isSearching" style="width: 240px">
    <el-option v-for="item in items" :key="item.id" :label="item.label" :value="item.id" />
</el-select>
<div v-else class="flex gap-2 flex-col" style="width: 100%">
    <div class="flex justify-end">
        <el-button-group>
            <el-button v-if="fields" @click="addDialog = true" :icon="Icons.Plus" class="float-right" size="small" type="primary">Add</el-button>
            <el-button :disabled="model == null" @click="model = null" :icon="Icons.Close" class="float-right" size="small" type="danger">Clear</el-button>
        </el-button-group>
    </div>
    <el-input v-if="canSearch" v-model="searchText" placeholder="Search" />
    <el-card v-if="isSearching" shadow="never">
        <el-skeleton animated />
    </el-card>
    <template v-else>
        <el-card v-if="!drag && items.length == 0" shadow="never">
            <el-empty>
                <el-button v-if="addLink && fields" @click="addDialog = true" type="primary" :icon="Icons.Plus">Add</el-button>
            </el-empty>
        </el-card>
        <draggable style="min-height: 300px" :disabled="!drag" :group="{ name: drag, pull: true, put: true }" v-else class="flex flex-col gap-3" :animation="200" v-model="items" item-key="id">
            <template #item="{element: item}">
                <Card @click="model = item.id" :class="{
                  'select-active': !drag && (model == item.id)
                }" :style="{cursor: drag ? 'move' : 'default' }" style="user-select: none; cursor: pointer" small :item="item" />
            </template>
        </draggable>
    </template>
</div>
<DrawerForm v-if="addLink && fields" :size="'540px'" :label="`Add ${label.toLowerCase()}`" v-model="addDialog" @success="() => refetch()" :fields="fields" :link="addLink" submitLabel="Add" method="POST" />
</template>

<style>
div.select-active {
    border: 1px solid var(--el-color-primary) !important;
}
</style>

<script setup>
import {
    debounce
} from 'lodash';
import draggable from 'vuedraggable';
import * as Icons from "@element-plus/icons-vue";
import DrawerForm from "./DrawerForm.vue";
import Card from '@thunderkiss52/Card.vue';
import {
    ref,
    watch,
    onMounted
} from "vue";
import axios from "axios";
const searchText = ref("");
const items = ref([]);
const {
    emptyFetch,
    exclude,
    canSearch,
    label,
    drag,
    link,
    multiple,
    fields,
    addLink,
    linkIncludes
} = defineProps({
    emptyFetch: Boolean,
    canSearch: Boolean,
    label: String,
    drag: String,
    link: String,
    exclude: {
        type: Array,
        default: []
    },
    multiple: {
        type: Boolean,
        default: false
    },
    small: {
        type: Boolean,
        default: false
    },
    fields: Array,
    linkIncludes: Object,
    addLink: String,
});
let controller = new AbortController();
const isSearching = ref(false);
const model = defineModel();
const emit = defineEmits(['input']);
onMounted(() => {
    if (emptyFetch) {
        refetch();
    }
});

const remoteMethod = (query) => {
    if (query) {
        refetch(query);
    } else {
        items.value = []
    }
}

watch(() => model.value, (v) => emit('input'));

watch(() => linkIncludes, (oldData, newData) => {
    if (linkIncludes != null) {
        let needUpdate = false;

        Object.keys(newData).forEach(k => {
            if (newData[k] != oldData[k]) {
                needUpdate = true;
            }
        });
        if (needUpdate) {
            model.value = null;
            refetch();
        }
    }
});

const addDialog = ref(false);

const refetch = function (search = null) {
    controller.abort();
    isSearching.value = false;
    controller = new AbortController();
    isSearching.value = true;
    axios
        .post(
            link, {
                ...linkIncludes,
                ...(search ?
                    {
                        search: search,
                    } :
                    {}),
            }, {
                signal: controller.signal,
            }
        )
        .then((response) => {
            items.value = response.data.filter(item => !exclude.includes(item.id));
            isSearching.value = false;
        });
}

defineExpose({
    refetch
})

watch(searchText, debounce((val) => {
    if (val.length > 0) {
        refetch(val);
    } else {
        items.value = [];
    }
}, 500));
</script>
