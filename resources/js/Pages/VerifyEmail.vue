<template>
    <div>
        <el-alert show-icon style="width: 100%;" v-if="!loading.hidden" :closable="false" type="warning" effect="dark">
            <template #title>
                <el-row :gutter="10">
                    <el-col :span="20">To receive notifications, please confirm your email. {{ page.props.auth.user.email
                        }}</el-col>
                    <el-col :span="4">
                        <el-button size="small" :loading="loading.state" @click="sendRequest()"
                            style="float: right;">Confirm</el-button>
                    </el-col>
                </el-row>

            </template>
        </el-alert>
    </div>
</template>
<script setup lang="ts">
import axios from "axios";
import { reactive } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { ElMessage } from "element-plus";
let loading = reactive({
    state: false,
    hidden: false
});
const page = usePage();
function sendRequest() {
    loading.state = true;
    axios
        .get(route('verification.send'))
        .then((response) => {
            ElMessage.success(`The message has been sent to the email ${page.props.auth.user.email}`);
            loading.hidden = true;
        })
        .catch((error) => {
            //console.log(error);
            ElMessage.error(error.response.data.message);
        })
        .finally(() => {
            loading.state = false;
        })
}
</script>

<style>
.el-alert__content {
    width: 100%;
}
</style>