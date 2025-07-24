<template>
  <el-card shadow="never">
    <template #header>
      {{$page.props.locale.filters}}
      <el-button @click="resetFilters()" class="float-right" size="small">{{$page.props.locale.reset}}</el-button>
    </template>
    <el-form label-position="top">
      <el-form-item v-for="filter in filters" :label="filter.label">
        <Date v-if="
            [
              'year',
              'month',
              'date',
              'years',
              'datetime',
              'week',
            ].includes(filter.type)" :field="filter" v-model="filterData[filter.key]" />
        
        <SelectField v-else-if="
            [
              'select',
              'list',
            ].includes(filter.type)"
            :filterOption="filterOption" :field="filter" v-model="filterData[filter.key]" />
        <Field v-else-if="
        [
          'name',
          'email',
          'address',
          'tel',
          'textarea',
          'text',
          'number',
          'searchselect',
          'time',
          'file',
          'checkbox',
          'button'
        ].includes(filter.type)" :field="filter" v-model="filterData[filter.key]" />
        <el-radio-group v-model="filterData[filter.key]" v-else-if="filter.type == 'radio'" class="flex gap-1 flex-col"
          style="width: 100%">
          <el-radio style="width: 100%; margin: 0px !important; margin-bottom: 5px" v-for="opt in filter.options"
            :value="opt.value" border>{{ opt.label }}</el-radio>
        </el-radio-group>

        <!-- <el-select clearable filterable :multiple="filter.multiple" :disabled="!filter.options || filter.options.length == 0" v-model="filterData[filter.key]" v-if="filter.type == 'select'" placeholder="Выберите...">
          <el-option v-for="opt in filter.options" :label="opt.label" :value="opt.value" />
        </el-select> -->
        <!-- <el-select v-model="filterData[filter.key]" v-else-if="filter.type == 'multiple'" placeholder="Выберите..."
          multiple>
          <el-option v-for="opt in filter.options" :label="opt.label" :value="opt.value" />
        </el-select> -->
        <!-- <el-checkbox-group v-model="filterData[filter.key]" v-else-if="filter.type == 'checkbox'"
          class="flex gap-1 flex-col" style="width: 100%">
          <el-checkbox style="width: 100%; margin: 0px !important" v-for="opt in filter.options" :value="opt.value"
            border>{{ opt.label }}</el-checkbox>
        </el-checkbox-group> -->



        <!-- <el-date-picker :disabled-date="(date) =>
          (filter.mindate
            ? date.getTime() / 1000 <= filter.mindate
            : false) ||
          (filter.maxdate
            ? date.getTime() / 1000 >= filter.maxdate
            : false)
          " :format="filter.type == 'year'
            ? 'YYYY'
            : filter.type == 'datetime'
              ? 'DD.MM.YYYY HH:mm'
              : 'DD.MM.YYYY'
            " :firstDayOfWeek="1" :required="filter.required" style="width: 100%" :value-format="filter.type == 'year'
                ? 'YYYY'
                : filter.type == 'datetime'
                  ? 'YYYY-MM-DD H:m:s'
                  : 'YYYY-MM-DD'
                " v-else-if="
                  [
                    'year',
                    'month',
                    'date',
                    'dates',
                    'months',
                    'years',
                    'datetime',
                    'week',
                  ].includes(filter.type)
                " v-model="filterData[filter.key]" :type="filter.type" :placeholder="filter.placeholder">
        </el-date-picker> -->

      </el-form-item>
      <el-button @click="applyFilters()" style="width: 100%" type="primary">{{$page.props.locale.apply}}</el-button>
    </el-form>
  </el-card>
</template>
<script setup>
import Date from "./Form/Date.vue";
import Field from "./Form/Field.vue";
import SelectField from "./Form/SelectField.vue";
import { reactive } from "vue";

const { filters } = defineProps({
  filters: Array,
});


function filterOption(condition) {
  let exist = true;
  Object.keys(condition).forEach((key) => {
    if (Array.isArray(condition[key])) {
      if (!condition[key].includes(filterData.value[key])) {
        exist = false;
      }
    } else if (condition[key] != filterData.value[key]) {
      exist = false;
    }
  });
  return exist;
}

let valuesTemp = new Object();

const emit = defineEmits(["filtered"]);

filters.forEach((f) => {
  if (["checkbox", "multiple"].includes(f.type)) {
    valuesTemp[f.key] = [];
  } else {
    valuesTemp[f.key] = null;
  }
});
const filterData = reactive(valuesTemp);

function resetFilters() {
  filters.forEach((f) => {
    if (["checkbox", "multiple"].includes(f.type)) {
      filterData[f.key] = [];
    } else {
      filterData[f.key] = null;
    }
  });
  applyFilters();
}

function applyFilters() {
  let result = {};
  Object.keys(filterData).forEach((k) => {
    if (
      filterData[k] != null ||
      (Array.isArray(filterData[k]) && filterData[k].length > 0)
    ) {
      result[k] = filterData[k];
    }
  });
  emit("filtered", result);
}
</script>