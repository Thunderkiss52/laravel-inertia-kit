<template>
<el-card v-if="files">

<template #header>
    <div class="flex flex-row justify-between">
        <div>Files</div>
        <el-upload v-if="link" :with-credentials="true" :auto-upload="false" :on-change="async (file, fileList) => processFile(file)">
            <el-button type="primary" :icon="Icons.Upload">{{$page.props.locale.load}}</el-button>
        </el-upload>
    </div>
</template>

<el-row :gutter="10" v-if="files && files.length > 0">
    <el-col class="mb-3" v-for="doc in files" :span="8">
        <el-card shadow="never" body-style="padding: 10px">
            <a target="_blank" :href="route('file.download', { uuid: doc.uuid })">
                <el-image style="width: 100%; height: 160px; border-radius: 5px" :src="route('file.download', { uuid: doc.uuid })" fit="contain">
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
            </a>
            <div>
                <el-text>{{ doc.file_name }}</el-text>
                <el-text style="float: right;">{{ doc.size }} b.</el-text>
            </div>
            <div>
                <el-text>{{ new Date(doc.created_at).toLocaleString() }}</el-text>
                <Link style="float: right;" :href="route('file.destroy', { uuid: doc.uuid })" method="DELETE">
                <el-button size="small" type="danger" :icon="Icons.Delete">{{$page.props.locale.delete}}</el-button>
                </Link>
            </div>
        </el-card>
    </el-col>
</el-row>
<el-empty description="No files" v-else />
</el-card>
</template>
<script setup>
import * as Icons from "@element-plus/icons-vue";

import {
    Link, router
} from "@inertiajs/vue3";
const {files, link} = defineProps({
    files: Array,
    link: String
});
function processFile(file) {
    router.post(link, {
        file: file.raw
    })
}
</script>