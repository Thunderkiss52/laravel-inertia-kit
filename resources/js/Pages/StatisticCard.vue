<template>
    <el-card shadow="never" class="mb-3 shadow-sm sm:rounded-lg">
        <template #header>
            {{ state.label }}
        </template>
        <div style="display: flex; gap: 40px">
            <div>
                <el-progress type="circle" :percentage="state.progress" status="success" />
            </div>
            <div style="width: 100%;">
                <el-descriptions style="width: 100%;" size="small" :column="1" border>
                    <el-descriptions-item :label="d" v-for="(g, d) in state.cells" :key="d">
                        {{ g }}
                    </el-descriptions-item>
                </el-descriptions>
            </div>
        </div>
    </el-card>
</template>

<script setup>
    const props = defineProps({
        state: {
            type: Object,
            required: true
        }
    });
</script>
