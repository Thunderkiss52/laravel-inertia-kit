<template>
    <TabsLayout>
        <div class="flex gap-4 flex-col">
            <el-card>
                <template #header>
                    <div class="flex justify-between">
                        <h2>{{data.label}}</h2>
                        <el-button v-if="item.fields"
                            :icon="Icons.Plus"
                            @click="modal = true"
                            type="primary"
                            >Add</el-button
                        >
                    </div>
                </template>
                <SearchTable ref="table" :lazy="false" :search="false" :columnWidth="data.columnWidth"  v-model:items="data.items" :filters="data.filters" />
            </el-card>
        </div>
    </TabsLayout>
    <DrawerForm
        v-model="modal"
        :link="data.link"
        :fields="data.fields"
        label="Add"
        submitLabel="Add"
    />
</template>
<script setup>
import * as Icons from "@element-plus/icons-vue";
import TabsLayout from "../Layout/TabsLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import SearchTable from '@thunderkiss52/SearchTable.vue';
import DrawerForm from "@thunderkiss52/DrawerForm.vue";
import { ref } from "vue";
const { item, items } = defineProps({
    item: Object,
    data: Array,
});

const modal = ref(false);
</script>
