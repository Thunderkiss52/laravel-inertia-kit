<template>
    <div class="flex flex-col" style="width: 100%">
        <el-button v-if="props.edit" @click="showModal = !showModal">{{props.limit > 1 ? 'Choose files' : 'Choose file'}}</el-button>
        <div class="flex flex-col gap-2 py-2">
            <template v-for="file in selectedFiles">
                <el-card body-style="padding: 10px" shadow="never" v-if="file && file.uuid">
                <!-- Uploading File Content -->
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-x-3">
                        <!-- <span class="size-8 flex justify-center items-center border border-gray-200 text-gray-500 rounded-lg">
                            <svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </span> -->
                        <el-image lazy style="height: 35px; border-radius: 5px; border: 1px solid #dbdbdb" :src="route('file.preview', { uuid: file.uuid })" fit="contain">
                            <template #placeholder>
                                <el-skeleton animated>
                                    <template #template>
                                        <el-skeleton-item variant="image" style="height: 35px" />
                                    </template>
                                </el-skeleton>
                            </template>
                            <template #error>
                                <el-skeleton>
                                    <template #template>
                                        <el-skeleton-item variant="image" style="height: 35px" />
                                    </template>
                                </el-skeleton>
                            </template>
                        </el-image>
    
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{file.file_name}}</p>
                            <p class="text-xs text-gray-500">{{filesize(file.size)}}</p>
                        </div>
                    </div>
                    <div class="inline-flex items-center gap-x-2">
                        <a :href="route('file.download', file.uuid)" type="button" class="relative text-gray-500 hover:text-gray-800 focus:outline-hidden focus:text-gray-800 disabled:opacity-50 disabled:pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>

                            <span class="sr-only">Download</span>
                        </a>
                        <button v-if="props.edit" @click="dropFile(file.id)" type="button" class="relative text-gray-500 hover:text-gray-800 focus:outline-hidden focus:text-gray-800 disabled:opacity-50 disabled:pointer-events-none">
                            <svg class="shrink-0 size-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"></path>
                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                <line x1="10" x2="10" y1="11" y2="17"></line>
                                <line x1="14" x2="14" y1="11" y2="17"></line>
                            </svg>
                            <span class="sr-only">Delete</span>
                        </button>
                    </div>
                </div>
                </el-card>
            </template>
        </div>
    </div>
    <el-dialog v-if="props.edit" v-model="showModal" :title="props.limit > 1 ? 'Choose files' : 'Choose file'" width="90%">
        <el-skeleton v-if="isLoading" animated />
        <FilesList v-model="selectedFiles" v-if="!isLoading" :filesList="filesList" :limit="props.limit" :link="link" :mimeTypes="props.mimeTypes" :selectable="true" />
    </el-dialog>
</template>

<script setup>
import { filesize } from 'filesize';
import {ref, watch, onMounted} from 'vue';
import axios from 'axios';
import FilesList from '@thunderkiss52/FilesList.vue';
const filesList = ref([]);
const showModal = ref(false);
const isLoading = ref(false);
const model = defineModel({ default: [] });
const selectedFiles = ref([]);//defineModel({ type: Array, default: [] });
const props = defineProps({
    edit: {
        type: Boolean,
        default: true
    },
    limit: {
        type: Number,
        default: null
    },
    mimeTypes: {
        // type: String,
        default: null
    },
    link: {
        type: String,
        default: ''
    }
});

watch(selectedFiles, (newval) => {
    if(props.limit == 1) {
        model.value = newval.length > 0 ? newval[0].uuid : null;
    } else {
        model.value = newval.map(v => v.uuid);
    }
});

if(!Array.isArray(selectedFiles.value)) { 
    selectedFiles.value = [];
}
function dropFile(id) {
    selectedFiles.value = selectedFiles.value.filter(i => i.id != id);
}
onMounted(() => {
    isLoading.value = true;
    axios.get(`${props.link}?files`)
    .then((res) => {
        filesList.value = res.data;
        if(Array.isArray(model.value) && model.value.length > 0) {
            selectedFiles.value = filesList.value.filter(f => model.value.includes(f.uuid));
        } else if(model.value != null) {
            selectedFiles.value = [filesList.value.filter(f => model.value == f.uuid)[0]];
        }
    })
    .catch((ex) => ElMessage.error("Failed to load the file list."))
    .finally(() => isLoading.value = false)
})
</script>