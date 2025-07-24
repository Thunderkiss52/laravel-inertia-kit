<script setup>
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout.vue";
import {
    Head,
    Link,
    router
} from "@inertiajs/vue3";
import * as Icons from "@element-plus/icons-vue";
import AdminForm from "@thunderkiss/AdminForm.vue";
import {
    ElMessageBox
} from "element-plus";
import {
    ref
} from 'vue';
import draggable from 'vuedraggable';
const {
    orderable,
    title,
    items,
    cols,
    addLink,
    deleteLink,
    fields,
    actions
} =
defineProps({
    orderable: {
        type: Boolean,
        default: false
    },
    title: String,
    items: Array,
    cols: Number,
    addLink: String,
    deleteLink: String,
    fields: Array,
    actions: Array,
});

function showDeletePrompt() {
    ElMessageBox.confirm("Do you want to delete?").then(() =>
        router.delete(deleteLink)
    );
}


// Обработчик завершения перетаскивания
const onDragEnd = async () => {
    const newOrder = templatesData.value.map((tpl) => tpl.id);

    try {
        await axios.put(props.draftsLink, {
            order: newOrder
        });
    } catch (error) {
        console.error('Error saving order:', error);
    }
};

const listItems = ref(items);
</script>

<template>
<Head :title="title" />

<AuthenticatedLayout>
    <template #header>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ title }}
        </h2>
    </template>
    <template #actions>
        <template v-if="actions">
            <Link :href="action.link" v-for="action in actions">
            <el-button class="mr-2" :type="action.type" :icon="Icons[action.icon]">{{ action.label }}</el-button>
            </Link>
        </template>
        <el-button v-if="deleteLink" @click="showDeletePrompt()" type="danger" :icon="Icons.Delete">Delete</el-button>
    </template>

    <div class="m-3">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <el-row :gutter="15">
                <el-col :sm="fields ? 16 : 24" :md="fields ? 18 : 24">
                    <!-- <template> -->
                        <draggable v-if="listItems.length > 0" @end="onDragEnd" class="flex gap-2 flex-row" style="width: 100%" handle=".handle" :animation="200" v-model="listItems" item-key="id">
                            <template #item="{ element: i }">
                            <section class="w-1/3 handle">
                                    <Link :href="i.route">
                                    <el-card shadow="hover" body-style="padding: 10px 20px">
                                        <template #header>
                                            {{ i.label }}
                                            <template v-if="i.tags">
                                                <el-tag v-for="tag in i.tags" :type="tag.type" style="float: right" effect="dark">{{ tag.label }}</el-tag>
                                            </template>
                                        </template>

                                        <el-descriptions :column="2">
                                            <template v-if="i.state">
                                                <el-descriptions-item v-for="stateKey in Object.keys(
                                                        i.state
                                                    )" :label="stateKey">
                                                    {{ i.state[stateKey] }}
                                                </el-descriptions-item>
                                            </template>
                                        </el-descriptions>
                                    </el-card>
                                    </Link>
                                    </section>
                            </template>
                        </draggable>
                    <!-- </template> -->
                    <!-- </el-col>
                        </el-row> -->
                    <el-card shadow="never" v-else>
                        <el-empty />
                    </el-card>
                </el-col>

                <el-col v-if="fields" :sm="8" :md="6">
                    <el-card shadow="never" body-style="padding: 10px 20px" class="mb-3">
                        <template #header>Add {{ title }}</template>
                        <AdminForm submitLabel="Add" :fields="fields" :successclear="true" :link="addLink" method="POST" />
                    </el-card>
                </el-col>
            </el-row>
        </div>
    </div>
</AuthenticatedLayout>
</template>
