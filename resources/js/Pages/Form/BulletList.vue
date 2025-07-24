<template>
    <div class="flex flex-col gap-2" style="width: 100%">
      <div v-if="!field.max || !Array.isArray(model) || field.max > model.length" class="flex flex-row gap-2" style="width: 100%">
        <div v-if="field.fields" class="flex gap-2" style="width: 100%">
          <el-input v-for="f in Object.keys(field.fields)" :placeholder="field.fields[f]" show-word-limit :maxlength="field.maxlength" v-model="newData[f]" size="small" style="width: 100%" />
        </div>
        <el-input v-else show-word-limit v-model="newData" :maxlength="field.maxlength" @keyup.enter="handleEnter" size="small" style="width: 100%" />
        <el-button @click="handleEnter" size="small" type="primary" :icon="Icons.Plus" />
      </div>
      
      <draggable class="flex gap-2 flex-col" style="width: 100%" :group="{ name: 'form-bulletlist', pull: true, put: true }" :animation="200" v-model="model" item-key="id">
        <template #item="{ element: ind, index: i }">
          <div class="flex gap-2" style="width: 100%">
            <div v-if="field.fields" class="flex gap-2" style="width: 100%">
              <el-input v-for="f in Object.keys(field.fields)" :placeholder="field.fields[f]" show-word-limit :maxlength="field.maxlength" v-model="model[i][f]" size="small" style="width: 100%" />
            </div>
            <el-input v-else show-word-limit :maxlength="field.maxlength" v-model="model[i]" size="small" style="width: 100%" />
            <el-button @click="removeKey(i)" size="small" type="danger" :icon="Icons.Delete" />
          </div>
        </template>
      </draggable>
    </div>
</template>

<script setup>
import * as Icons from "@element-plus/icons-vue";
import { ref, onBeforeMount, onUpdated, watch } from "vue";
import draggable from 'vuedraggable';
const model = defineModel();
if(!Array.isArray(model.value)) {
model.value = [];
}
const {
    field
} = defineProps({
    field: Object
});
const emit = defineEmits([
    'change'
]);
const newData = ref(field.fields ? {} : "");
watch(model, () => emit('change'));
function removeKey(index) {
  model.value = model.value.filter((_, i) => i !== index);
}

function handleEnter() {
  if(typeof newData.value === 'object' || newData.value.length > 0) {
    if(!Array.isArray(model.value)) {
      model.value = [newData.value];
    } else {
      model.value.push(newData.value);
    }
    newData.value = field.fields ? {} : "";
  }
}
</script>
