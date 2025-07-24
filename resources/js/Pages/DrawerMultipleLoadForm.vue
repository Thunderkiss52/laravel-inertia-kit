<template>
<el-drawer style="max-width: 100vw" size="100%" :title="label" v-model="model" direction="rtl">
    <div class="flex flex-col gap-3">
        <div class="flex gap-3">
            <a v-if="templateLink" :href="templateLink">
                <el-button type="success" :icon="Icons.Download">{{$page.props.locale.download}} template</el-button>
            </a>
            <el-button :disabled="load" v-else type="success" :icon="Icons.Download" @click="getExample()">{{$page.props.locale.download}} template</el-button>
            <div>
                <el-upload ref="newFiles" :show-file-list="false" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" :limit="1" :on-change="async (file, fileList) => processFile(file, fileList)" :auto-upload="false">
                    <el-button :disabled="load" type="success" :icon="Icons.Upload">{{$page.props.locale.load}} {{$page.props.locale.data}}</el-button>
                </el-upload>
            </div>
            <div>
                <el-button :disabled="load" @click="loadData()" v-if="items.filter(i => !i.loaded).length > 0" style="float:right" type="primary" :icon="Icons.Plus">{{$page.props.locale.add}} {{items.filter(i => !i.loaded).length}} records</el-button>
            </div>
        </div>
        <el-table v-if="items.length > 0" size="small" :data="items">
            <template v-for="row in fields">
                <template v-for="field in row">
                    <el-table-column width="200" :prop="field.key" :label="field.label">
                        <template #default="scope">
                            <el-form-item :error="scope.row._state && scope.row._state.errors && scope.row._state.errors[field.key ?? field.startKey] ? scope.row._state.errors[field.key ?? field.startKey][0] : null">
                                <DateRange v-if="
                                [
                                    'timerange',
                                    'years',
                                    'datetimerange',
                                    'daterange',
                                    'monthrange'
                                ].includes(field.type)" v-model:startKey="scope.row[field.startKey]" v-model:endKey="scope.row[field.endKey]" :field="field" />
                                <Date v-else-if="
                                [
                                    'year',
                                    'month',
                                    'date',
                                    'years',
                                    'datetime',
                                    'week',
                                ].includes(field.type)" :field="field" v-model="scope.row[field.key]" />
                                <BulletList v-else-if="field.type == 'bulletlist'" :field="field" v-model="scope.row[field.key]" />
                                <SelectField v-else-if="
                                [
                                    'select',
                                    'list',
                                ].includes(field.type)" :filterOption="() => true" :field="field.checkboxes ? {...field, checkboxes:false} : field" v-model="scope.row[field.key]" />
                                <Field v-else-if="
                                [
                                    'name',
                                    'email',
                                    'address',
                                    'tel',
                                    'textarea',
                                    'text',
                                    'number',
                                    'searchselect',
                                    'time',
                                    'file',
                                    'checkbox',
                                    'button'
                                ].includes(field.type)" :field="field.type == 'searchselect' ?  {...field, small:true} : field" v-model="scope.row[field.key]" />
                                <el-input v-else type="text" clearable v-model="scope.row[field.key]" />
                            </el-form-item>
                        </template>
                    </el-table-column>
                </template>
            </template>
            <el-table-column width="130">
                <template label="Status" #default="scope">
                    <div v-if="scope.row._state" class="flex flex-col">
                        <div v-if="scope.row._state.loadProcess">
                            <el-text type="warning">Download...</el-text>
                        </div>
                        <div v-else-if="scope.row._state.error">
                            <el-text type="error">Error</el-text>
                        </div>
                        <template v-else>
                            <div v-if="scope.row._state.loaded">
                                <el-text type="success">Downloaded</el-text>
                            </div>
                            <div v-else-if="scope.row._state.errors">
                                <el-text type="error">ops, there's errors</el-text>
                            </div>
                            <div v-else-if="!scope.row._state.loaded && !scope.row._state.errors">
                                <el-text type="info">Not uploaded</el-text>
                            </div>
                        </template>
                    </div>
                </template>
            </el-table-column>
            <el-table-column width="70">
                <template #default="scope">
                    <el-button v-if="!scope.row._state || !scope.row._state.loaded" @click="items.splice(scope.$index, 1)" type="danger" :icon="Icons.Delete" />
                </template>
            </el-table-column>
            </el-table>
        <el-skeleton animated v-if="load" />
        <div v-if="items.length > 0">
            <el-button size="small" :disabled="load" type="primary" @click="items.push([])" :icon="Icons.Plus">{{$page.props.locale.addStrokeDown}}</el-button>
        </div>
        <el-empty v-if="!load && items.length == 0">
            <el-button size="small" :disabled="load" type="primary" @click="items.push([])" :icon="Icons.Plus">{{$page.props.locale.addStrokeDown}}</el-button>
        </el-empty>
    </div>
</el-drawer>
</template>

<script setup>
import axios from "axios";
import Date from "./Form/Date.vue";
import Field from "./Form/Field.vue";
import DateRange from "./Form/DateRange.vue";
import BulletList from "./Form/BulletList.vue";
import SelectField from "./Form/SelectField.vue";
import {
    read,
    readFile,
    utils,
    writeFile
} from 'xlsx';
import * as Icons from "@element-plus/icons-vue";
import {
    ref,
    computed
} from 'vue';
function loadData() {
    load.value = true;
    items.value
        .filter(item => !item._state || !item._state.loaded)
        .forEach(async (item) => {
            console.log(item);
            item._state = {
                error: false,
                loadProcess: true,
            };
            axios.post("", {
                    _method: 'POST',
                    ...item
                })
                .then((res) => {
                    item._state.loaded = true;
                    emit('success');
                })
                .catch(e => {
                    if (e.request.status == 500) {
                        item._state.error = true;
                    } else if (e.request.status == 422) {
                        item._state.errors = JSON.parse(e.request.response).errors;
                    }
                })
                .finally(() => {
                    item._state.loadProcess = false;
                })
        });
    load.value = false;
}

const load = ref(false);
const newFiles = ref(null);

// const loadTable = ref(false);
function processFile(file, fileList) {
    load.value = true;
    axios.postForm(`${link}/parse`, {
        data: file.raw
    }).then((r) => {
        // items.value = r.data; 
        items.value = [
            ...items.value,
            ...r.data
            ];
            // .map((t) => {
            //     Object.keys(t).forEach(k => {
            //         // console.log(t[k]);
            //         let d  = new Date(t[k]);
            //         if(isNaN(t[k]) &&  d != 'Invalid Date' && d.getFullYear() > 1900 && d.getFullYear() < 2050) {
            //             t[k] = d.toLocaleDateString().trim();
            //         } else {
            //             t[k] = t[k].trim();
            //         }
            //     });
            //     return t;
            // })
        newFiles.value.clearFiles();
    })
    .finally(() => {
        load.value = false;
    });

    // console.log(fileList);
    // console.log(file.raw);
    // var reader = new FileReader();
    // reader.readAsArrayBuffer(file.raw);
    // reader.onload = function (e) {
    //     let wb = read(new Uint8Array(reader.result), {
    //         type: "array",
    //         // raw: true
    //     });
    //     // console.log(wb);

    //     items.value = [
    //         ...items.value,
    //         ...utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], {
    //             raw: false
    //         })
    //         /*.map((stroke) => {
    //             let res = {};
    //             Object.keys(stroke)
    //                 .filter(colname => Object.keys(fieldKeys.value).includes(colname))
    //                 .forEach(colname => {
    //                     let keyname = fieldKeys.value[colname];

    //                     let d  = new Date(stroke[colname]);
    //                     if(isNaN(stroke[colname]) && d != 'Invalid Date' && d.getFullYear() > 1900 && d.getFullYear() < 2050) {
    //                         res[keyname] = d.toLocaleDateString();
    //                     } else {
    //                         res[keyname] = stroke[colname];
    //                     }
    //                 });
    //             return res;
    //         }) */
    //     ];
    // }
    // console.log(utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]]));
}

const {
    fields,
    link,
    method,
    label
} = defineProps({
    label: {
        type: String,
        required: true,
    },
    initdata: {
        type: Object,
        required: false,
    },
    fields: {
        type: Array,
        required: true,
    },
    templateLink: {
        type: String,
        required: false,
    },
    link: String,
    method: {
        type: String,
        default: "POST",
    },
});

const fieldKeys = computed(() => {
    const d = {};
    fields.forEach(r => {
        r.forEach(r => {
            d[r.label] = r.key;
        });
    });
    return d;
});

const data = {};
fields.forEach(r => {
    r.forEach(r => {
        data[r.label] = "";
    })
});

const emit = defineEmits(["success"]);
const model = defineModel();

const items = ref([]);

function getExample() {
    const ws = utils.json_to_sheet([data]);
    const wb = utils.book_new();
    utils.book_append_sheet(wb, ws, "Data");
    writeFile(wb, "Example.xlsx");
}
</script>
