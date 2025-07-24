<template>
<el-time-picker :class="timePut" v-model="picker" v-if="field.type == 'timerange'" @change="() => {startKey = picker[0]; endKey = picker[1]; emit('change')}" is-range style="width: 100%" value-format="HH:mm" format="HH:mm" :required="field.required" :placeholder="field.placeholder" start-placeholder="Start" end-placeholder="End" />
<el-date-picker @change="() => {startKey = picker[0]; endKey = picker[1]; emit('change')}" v-model="picker" :type="field.type" :placeholder="field.placeholder"  is-range :disabled-date="
                  (date) =>
    (field.mindate
      ? date.getTime() / 1000 <= field.mindate
      : false) ||
    (field.maxdate
      ? date.getTime() / 1000 >= field.maxdate
      : false)
" :format="
  field.type == 'years'
    ? 'YYYY'
    : field.type == 'datetimerange'
    ? 'DD.MM.YYYY HH:mm'
    : 'DD.MM.YYYY'
" :firstDayOfWeek="1" :required="field.required" style="width: 100%" :value-format="
  field.type == 'year'
    ? 'YYYY'
    : field.type == 'datetimerange'
    ? 'YYYY-MM-DD H:m:s'
    : 'YYYY-MM-DD'
" start-placeholder="Start" end-placeholder="End" v-else-if="
  ['datetimerange', 'daterange', 'monthrange'].includes(
    field.type
  )
"/>
</template>

<script setup>
import CustomSelect from "../CustomSelect.vue";
import * as Icons from "@element-plus/icons-vue";
import { mask as vMask } from "vue-the-mask";
import {ref, onMounted} from 'vue';
const startKey = defineModel('startKey');
const endKey = defineModel('endKey');

function generateRandomString(length) {
  const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  let result = '';

  for (let i = 0; i < length; i++) {
    const randomIndex = Math.floor(Math.random() * characters.length);
    result += characters[randomIndex];
  }

  return result;
}

const picker = ref([startKey.value, endKey.value]);
const timePut = generateRandomString(10);



onMounted(() => {
  var els = document.getElementsByClassName(timePut);
  if(els.length > 0) {
    els = els[0].getElementsByClassName('el-range-input');
  
Array.prototype.forEach.call(els, function(el) {
    el.addEventListener('input', function(e) {
        // console.log(e.target);
        let clear = e.target._value.replace(/[^0-9:]/g, '');
        if(clear.length == 1 && !["1","2","0"].includes(clear)) {
            clear = `0${clear}`;
        } else
        if(clear.length > 2 && !clear.includes(":")) {
            clear = clear.slice(0, 2)+":"+clear.slice(2);
        }
        clear = clear.slice(0, 5);
        // console.log(clear);

        e.target.value = clear;
    });
});
  }
});

const {
    field
} = defineProps({
    field: Object
});
const emit = defineEmits([
    'change'
]);
</script>