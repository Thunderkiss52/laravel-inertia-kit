<template>
  <el-drawer style="max-width: 100vw" :size="size" :title="label" v-model="model" direction="rtl">
    <AdminForm
      @success="
        (d) => {
          model = false;
          emit('success');
        }
      "
      :initdata="initdata"
      :fields="fields"
      :draftsLink="draftsLink"
      :link="link"
      :successclear="successclear"
      :submitLabel="submitLabel"
      :method="method"
    />
  </el-drawer>
</template>
<script setup>
import AdminForm from "./AdminForm.vue";

const { size, fields, link, submitLabel, method, label } = defineProps({
  size: {
    type: String,
    default: "540px",
  },
  draftsLink: {
    type: String,
    default: null
  },
  label: {
    type: String,
    required: true,
  },
  successclear: {
    type: Boolean,
    default: false,
  },
  initdata: {
    type: Object,
    required: false,
  },
  fields: {
    type: Array,
    required: true,
  },
  link: String,
  submitLabel: {
    type: String,
    default: "Save",
  },
  method: {
    type: String,
    default: "POST",
  },
});

const emit = defineEmits(["success"]);
const model = defineModel();
</script>
