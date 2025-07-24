<template>
<el-date-picker @change="emit('change')" :disabled-date="
  (date) =>
    (field.mindate
      ? date.getTime() / 1000 <= field.mindate
      : false) ||
    (field.maxdate
      ? date.getTime() / 1000 >= field.maxdate
      : false)
" :format="
  field.type == 'year'
    ? 'YYYY'
    : field.type == 'datetime'
    ? 'DD.MM.YYYY HH:mm'
    : 'DD.MM.YYYY'
" :disabled="false" :firstDayOfWeek="1" :required="field.required" style="width: 100%" :value-format="
  field.type == 'year'
    ? 'YYYY'
    : field.type == 'datetime'
    ? 'YYYY-MM-DD H:m:s'
    : 'YYYY-MM-DD'
" v-model.trim="model" :type="field.type" :placeholder="field.placeholder">
</el-date-picker>
</template>

<script setup>
    const model = defineModel();
    const {field} = defineProps({
        field: Object
    });
    const emit = defineEmits([
      'change'
    ]);
</script>