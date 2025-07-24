<template>
    <el-image fit="contain" :style="{ width: width }" :src="src">
        <template #placeholder>
            <el-skeleton animated>
                <template #template>
                    <el-skeleton-item
                        variant="image"
                        :style="{ width: width, height: width }"
                    />
                </template>
            </el-skeleton>
        </template>
        <template #error>
            <el-skeleton>
                <template #template>
                    <el-skeleton-item
                        variant="image"
                        :style="{ width: width, height: width }"
                    />
                </template>
            </el-skeleton>
        </template>
    </el-image>
</template>
<script setup>
const { width } = defineProps({
    width: {
        type: String,
        default: "50px",
    },
    src: {
        type: String,
        default: "/img/logo.png",
    },
});
</script>
