<template>
        <el-row style="width: 100%" v-if="Array.isArray(model)" :gutter="10">
            <el-col :span="12">
                <!-- <el-card> -->
                    <!-- <template #header>
                        Добавить товар
                    </template> -->
                    <div class="flex flex-col gap-3">
                        <CustomSelect :canSearch="props.canSearch" :fields="props.fields" :addLink="props.addLink" :emptyFetch="props.emptyFetch" :exclude="model.map(m => m.id)" drag="form-bulletlist" :label="props.label" :link="props.link" />
                    </div>
                <!-- </el-card> -->
            </el-col>
            <el-col :span="12">
                <div class="pb-3 flex justify-between">
                    <h1>Content</h1>
                    <el-tag effect="dark"><h1>{{model.length}}</h1></el-tag>
                </div>
                <!-- <el-card> -->
                    <!-- <template #header>
                        Содержимое
                    </template> -->
                    <!-- <div class="flex flex-col gap-3"> -->
                    <draggable style="min-height: 300px" :group="{ name: 'form-bulletlist', pull: true, put: true }" class="flex flex-col gap-3" :animation="200" v-model="model" item-key="id">
                        <template #item="{element: item}">
                            <Card style="cursor: move; user-select: none" small :item="item" />
                        </template>
                    </draggable>
                    <!-- </div> -->
                <!-- </el-card> -->
            </el-col>
        </el-row>
</template>

<script setup>
import draggable from 'vuedraggable';
import Card from '@thunderkiss52/Card.vue';
import CustomSelect from "@thunderkiss52/CustomSelect.vue";
const model = defineModel();
const props = defineProps({
    link: String,
    canSearch: Boolean,
    fields: Array,
    addLink: String,
    label: String,
    emptyFetch: Boolean
});
if(!Array.isArray(model.value)) {
    model.value = [];
}
</script>