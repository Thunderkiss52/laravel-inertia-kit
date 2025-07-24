<template>
<el-row :gutter="10">
    <el-col v-if="loadingFiles.length > 0" :span="5">
        <div class="flex flex-col gap-3">

            <!-- File Uploading Progress Form -->
            <div class="flex flex-col bg-white border border-gray-200 shadow-2xs">
                <!-- Body -->
                <div class="p-4 md:p-5 space-y-7">
                    <div v-for="file in loadingFiles">
                        <!-- Uploading File Content -->
                        <div class="mb-2 flex justify-between items-center">
                            <div class="flex items-center gap-x-3">
                                <span class="size-8 flex justify-center items-center border border-gray-200 text-gray-500 rounded-lg">
                                    <svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" x2="12" y1="3" y2="15"></line>
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{file.name}}</p>
                                    <p class="text-xs text-gray-500">{{filesize(file.size)}}</p>
                                </div>
                            </div>
                            <div class="inline-flex items-center gap-x-2">
                                <button type="button" class="relative text-gray-500 hover:text-gray-800 focus:outline-hidden focus:text-gray-800 disabled:opacity-50 disabled:pointer-events-none">
                                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                        <!-- End Uploading File Content -->

                        <!-- Progress Bar -->
                        <el-progress :percentage="file.progress" :show-text="false" :stroke-width="8" />
                        <!-- End Progress Bar -->
                    </div>
                </div>
                <!-- End Body -->

                <!-- Footer -->
                <div class="bg-gray-50 border-t border-gray-200 rounded-b-xl py-2 px-4 md:px-5">
                    <div class="flex flex-wrap justify-between items-center gap-x-3">
                        <div>
                            <span class="text-sm font-semibold text-gray-800">
                                {{loadingFiles.length}} Files
                            </span>
                        </div>
                        <!-- End Col -->

                        <div class="-me-2.5">
                            <button type="button" class="py-2 px-3 inline-flex items-center gap-x-1.5 text-sm font-medium rounded-lg border border-transparent text-gray-500 hover:bg-gray-200 hover:text-gray-800 focus:outline-hidden focus:bg-gray-200 focus:text-gray-800 disabled:opacity-50 disabled:pointer-events-none">
                                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18"></path>
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                    <line x1="10" x2="10" y1="11" y2="17"></line>
                                    <line x1="14" x2="14" y1="11" y2="17"></line>
                                </svg>
                                Delete
                            </button>
                        </div>
                        <!-- End Col -->
                    </div>
                </div>
                <!-- End Footer -->
            </div>
        </div>
    </el-col>
    <el-col :span="loadingFiles.length > 0 ? 19 : 24">
        <el-card class="mb-3" shadow="never" v-if="selectable">
            <div class="flex justify-between">
                <h5 class="text-base font-semibold text-gray-900 md:text-xl">
                    Selected: {{selectedDocs.length}}<template v-if="limit">/{{limit}}</template>
                </h5>
                <el-button-group>
                    <!-- <el-button type="success">Закрыть</el-button> -->
                    <el-button @click="selectedDocs = []" type="danger">Clear</el-button>
                </el-button-group>
            </div>
        </el-card>
        <el-card v-if="options && options.length > 1" body-style="padding: 10px; display: flex" class="mb-3">
        <el-scrollbar :always="true">
        <el-segmented
            class="folders"
            v-model="currentDir"
            :options="options"
        >
        <template #default="scope">
            <div style="width: 100px; height: 100px; padding: 0 20px;"
                :class="[
                'flex',
                'justify-center',
                'items-center',
                'flex-col',
                'horizontal',
                ]"
            >
                <el-icon size="40">
                    <component :is="Icons.Folder" />
                </el-icon>
                <div style="white-space: break-spaces">{{ scope.item.label }}</div>
                <div class="text-xs">{{filesData.filter(f => scope.item.value == '*' || f.folder == scope.item.value).length}} Files</div>
            </div>
        </template>
        
    </el-segmented>
    </el-scrollbar>
            <!-- <div style="background-color: var(--el-fill-color-light); cursor: pointer; width: 100px; height: 100px; margin: 0 20px; border-radius: calc(var(--el-border-radius-base) - 2px);"
                :class="[
                'flex',
                'justify-center',
                'items-center',
                'gap-2',
                'flex-col',
                'horizontal',
                'p-2'
                ]"
            >
                <el-icon size="40">
                    <component :is="Icons.Plus" />
                </el-icon>
                <div>Добавить</div>
            </div> -->
    </el-card>
        <!-- {{progress}} -->
        <el-card class="mb-3" shadow="never">
            <!-- <div class="flex"> -->
            <el-upload drag multiple :accept="mimeTypes" :show-file-list="false" :on-change="uploadFile" :auto-upload="false">
                <!-- <el-button size="small" type="success">Загрузить Файл</el-button> -->
                <el-icon class="el-icon--upload">
                    <Icons.UploadFilled />
                </el-icon>
                <div class="el-upload__text">
                    move or <em>select file</em>
                </div>
            </el-upload>
            <!-- </div> -->
        </el-card>

        <el-dialog v-model="showVideo" :title="showVideo ? showVideo.file_name : null" width="800">
            <video-player style="width: 100%; min-height: 500px;" v-if="showVideo && showVideo.uuid" :sources="[ {
                src: route('file.download', { uuid: showVideo.uuid }),
                type: showVideo.mime_type
            }]" :poster="route('file.preview', { uuid: showVideo.uuid })" :loop="false" controls :volume="0.6" />
        </el-dialog>

        <el-dialog class="iframe-viewer" :fullscreen="true" v-model="showLink" title="View file">
            <template #default>
                <iframe style="width: 100%; height: 100%; " :src="`https://docs.google.com/viewer?embedded=true&url=${showLink}`" />
            </template>
        </el-dialog>
        <draggable class="grid grid-cols-12 gap-3" :animation="200" @update="updateOrder" v-model="filesData" item-key="id">
            <template #item="{element: doc}">
                <el-card @contextmenu.prevent="(e) => onContextMenu(e, doc)" v-if="isAllowedMimeType(doc.mime_type) && inCurrentFolder(doc.folder)" @click="() => addDocToSelect(doc)" shadow="never" body-style="padding: 10px" class="card1 col-span-12" :class="{
                        'selected': selectable && selectedDocs.includes(doc),
                        'sm:col-span-12 md:col-span-6 lg:col-span-4 xl:col-span-3': loadingFiles.length > 0,
                        'sm:col-span-6 md:col-span-4 lg:col-span-3 xl:col-span-2': loadingFiles.length === 0,
                    }">
                    <a :href="!selectable && doc.uuid ? route('file.download', { uuid: doc.uuid }) : null">
                        <!-- <div v-if="!doc.converted" class="pb-2" >
                        <el-text>Файл обрабатывается</el-text>
                        <el-progress class="py-1" :percentage="50" :show-text="false" status="warning" :indeterminate="true" :stroke-width="8" />
                    </div> -->
                        <el-image @click="(event) => {event.preventDefault(); !selectable ? processMediaClick(doc) : null}" lazy style="width: 100%; height: 140px; border-radius: 5px; border: 1px solid #dbdbdb" :src="route('file.preview', { uuid: doc.uuid })" fit="contain">
                            <template #placeholder>
                                <el-skeleton animated>
                                    <template #template>
                                        <el-skeleton-item variant="image" style="width: 100%; height: 140px" />
                                    </template>
                                </el-skeleton>
                            </template>
                            <template #error>
                                <el-skeleton>
                                    <template #template>
                                        <el-skeleton-item variant="image" style="width: 100%; height: 140px" />
                                    </template>
                                </el-skeleton>
                            </template>
                        </el-image>

                        <el-text :line-clamp="2">{{ doc.file_name }}</el-text>
                        <div class="flex justify-between">
                            <el-text size="small">{{ new Date(doc.created_at).toLocaleString() }}</el-text>
                            <button v-if="!selectable" @click="(e) => {e.preventDefault(); router.delete(`file/${doc.uuid}`, {
                            preserveState: true,
                            preserveScroll: true,
                        })}" type="button" class=" px-2 inline-flex items-center gap-x-1.5 text-sm font-medium rounded-lg border border-transparent text-gray-500 hover:bg-gray-200 hover:text-gray-800 focus:outline-hidden focus:bg-gray-200 focus:text-gray-800 disabled:opacity-50 disabled:pointer-events-none">
                                {{filesize(doc.size)}}
                                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18"></path>
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                    <line x1="10" x2="10" y1="11" y2="17"></line>
                                    <line x1="14" x2="14" y1="11" y2="17"></line>
                                </svg>
                            </button>
                        </div>
                    </a>
                </el-card>
            </template>
        </draggable>
    </el-col>
</el-row>
<vue-easy-lightbox :visible="showImage != null" :imgs="imageLinksArray" :index="showImage" @hide="() => showImage = null" />

    <context-menu
    v-model:show="showContextMenu"
    :options="optionsComponent"
  >
    <context-menu-item label="View" @click="processMediaClick(optionsComponent.item)" />
    <context-menu-item label="Delete" @click="router.delete(`file/${optionsComponent.item.uuid}`, {
                            preserveState: true,
                            preserveScroll: true,
                        })" />
    <context-menu-group label="Move" icon="icon-reload-1">
        <context-menu-item v-for="option in options.filter(o => o.value != '*')" :label="option.label" @click="moveToCollection(optionsComponent.item.uuid, option.value)" />
    </context-menu-group>
  </context-menu>
</template>

<script setup>
import { ContextMenu, ContextMenuGroup, ContextMenuSeparator, ContextMenuItem } from '@imengyu/vue3-context-menu';
import * as Icons from '@element-plus/icons-vue';
import axios from 'axios';
import draggable from 'vuedraggable';
import TabsLayout from '@thunderkiss52/Layout/TabsLayout.vue'
import VueEasyLightbox from 'vue-easy-lightbox';
import { debounce } from 'lodash';
import { filesize } from 'filesize';
import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { VideoPlayer } from '@videojs-player/vue'
import { uploadService } from 'vuejs-chunks-upload/dist';
import 'video.js/dist/video-js.css'
import '@imengyu/vue3-context-menu/lib/vue3-context-menu.css';

let controller = new AbortController();

const {filesList, link, selectable, mimeTypes, limit, folders} = defineProps({
    filesList: Array,
    limit: {
        type: Number,
        default: null
    },
    link: {
        type: String,
        default: ''
    },
    mimeTypes: {
        default: null
    },
    selectable: {
        type: Boolean,
        default: false
    },
    folders: {
        type: Array,
        default: []
    }
});
const filesData = ref(filesList);
watch(() => filesList, () => filesData.value = filesList);
const files = ref([]);
const showImage = ref(null);
const showVideo = ref(null);
const showLink = ref(null);
const showContextMenu = ref(false);
const selectedDocs = defineModel({ type: Array, default: [] });
const loadingFiles = computed(() => files.value.filter(f => f.progress < 100));
const imageLinksArray = computed(() => Object.values(imageLinks.value));
const currentDir = ref('*'); 
const options = [
    {
        label: 'All',
        value: '*',
    },
    ...folders
];
function moveToCollection(uuid, col_name) {
    axios.patch(route('file.move', {
        uuid: uuid,
        collection: col_name
    }));
}

const optionsComponent = ref({
    item: null,
    theme: 'flat dark',
    // iconFontClass: 'iconfont',
    // customClass: "class-a",
    zIndex: 3,
    minWidth: 230,
    x: 500,
    y: 200
});


const updateOrder = debounce((event) => {
    controller.abort();
    controller = new AbortController();
    axios.patch(route('file.reorder'), {
        order: filesData.value.map((i) => i.id)
    }
    );
}, 500);

function onContextMenu(e, id) {
    // e.preventDefault();
    //Set the mouse position
    optionsComponent.value.item = id;
    optionsComponent.value.x = e.x;
    optionsComponent.value.y = e.y;
    //Show menu
    showContextMenu.value = true;
}


function inCurrentFolder(folder) {
    if(currentDir.value == '*' || folder == currentDir.value) {
        return true;
    }
}

function isAllowedMimeType(mime_type) {
    if(mimeTypes == null) {
        return true;
    }
//   return files.filter(file => {
    // Проверяем, соответствует ли MIME-тип файла одному из допустимых типов
    return mimeTypes.some(acceptType => {
      // Если acceptType заканчивается на "/*", проверяем только основную часть MIME-типа
      if (acceptType.endsWith('/*')) {
        const mainType = acceptType.split('/')[0];
        return mime_type.startsWith(mainType);
      }
      // Иначе проверяем полное совпадение MIME-типа
      return mime_type === acceptType;
    });
//   });
}

function processMediaClick(media) {
    if(media.mime_type.indexOf('image/') >= 0) {
        showFullImage(media.id)
    } else if(media.mime_type.indexOf('video/') >= 0) {
        showVideo.value = media;
    } else {
        showLink.value = route('file.download', { uuid: media.uuid }); 
    }
    
}
function addDocToSelect(item) {
    const index = selectedDocs.value.map(i => i.id).indexOf(item.id);
    if(limit == 1) {
        selectedDocs.value = [item];
    } else {
        if(index !== -1) {
            selectedDocs.value = selectedDocs.value.filter(i => i.id != item.id);
        } else if(!limit || selectedDocs.value.length < limit) {
            selectedDocs.value = [...selectedDocs.value, item]; //.push(id);
        }
    }
}

function showFullImage(id) {
    showImage.value = Object.keys(imageLinks.value).indexOf(String(id));
}
const imageLinks = computed(() => {
    let r = {};
    filesData.value ? filesData.value.filter(f => f.mime_type.indexOf("image/") >= 0).forEach(f => {
        r[f.id] = route('file.download', { uuid: f.uuid }) 
    }) : [];
    return r;
});

function uploadFile(f, fileList) {
    console.log(f.raw);
    let newFile = {
        uid: f.raw.uid,
        name: f.raw.name,
        size: f.raw.size,
        type: f.raw.type,
        progress: 0
    };
    files.value.push(newFile);
    let index = files.value.length;
    uploadService.chunk(link, f.raw,
        // Progress
        percent => {
            files.value[index - 1].progress = percent;
        },
        // Success
        res => {
            filesList.unshift(JSON.parse(res.request.response));
        },
        // Error
        err => {
            filesList.unshift(JSON.parse(err.request.response));
        }
    );

}
</script>

<style>
.iframe-viewer .el-dialog__body {
    height: 95% !important;
}

.card1 {
    border: 3px solid white;
    cursor: pointer;
}

.card1.selected {
    border: 3px solid var(--el-color-primary);
}

.folders .el-segmented__item {

}
</style>
