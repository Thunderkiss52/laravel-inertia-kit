<template>
  <template v-if="field.checkboxes">
        <el-checkbox-group v-if="field.multiple" v-model="model" class="flex gap-1 flex-col"
          style="width: 100%">
          <template v-for="option in field.options">
          <el-checkbox style="width: 100%; margin: 0px !important; margin-bottom: 5px" v-if="!option.availableif || filterOption(option.availableif)"
            :value="option.value" :label="option.label" border />
            </template>
        </el-checkbox-group>
        <el-radio-group v-else v-model="model" class="flex gap-1 flex-col"
          style="width: 100%">
          <template v-for="option in field.options">
          <el-radio style="width: 100%; margin: 0px !important; margin-bottom: 5px" v-if="!option.availableif || filterOption(option.availableif)"
            :value="option.value" border>{{ option.label }}</el-radio>
            </template>
        </el-radio-group>
  </template>
  <el-select v-else @change="
                  emit('change');
                  field.type == 'list'
                    ? (changeStartValue[field.item] = true)
                    : '';
                  field.onselect ? playaction(field.onselect) : null;
                " clearable :allow-create="field.allowCreate" :default-first-option="true" filterable :multiple="field.multiple" :remote="field.type == 'list'"  :remote-method="
                  async (query) => await searchInList(field.item, query)
                " :required="field.required" v-model="model" style="width: 100%" :placeholder="
                  field.type == 'list'
                      ? 'Start typing...'
                      : field.placeholder
                  ">
      <template v-if="field.type == 'list'">
          <el-option v-if="
                      ((field.initlabel && field.initlabel != null) ||
                        (field.labelKey &&
                          field.labelKey in values
                          )) &&
                      !changeStartValue[field.item]
                    " :label="field.initlabel" :value="field.initvalue ?? model">
          </el-option>
          <el-option v-bind:key="index" v-for="(option, index) in searchResults[field.item]" :label="option.label" :value="option.id">
              <span style="float: left">{{
                      option.label ? option.label.substr(0, 85) : option.id
                    }}</span>
          </el-option>
      </template>
      <template v-else>
          <template v-for="(option, index) in field.options">
              <el-option v-if="
                        !option.availableif ||
                        filterOption(option.availableif)
                      " v-bind:key="index" :label="option.label" :value="option.value" :disabled="option.disabled">
              </el-option>
          </template>
      </template>
  </el-select>
</template>

<script setup>
const model = defineModel();
if(field.multiple && !Array.isArray(model.value)) {
    model.value = [];
  }

const {
    field,
    filterOption
} = defineProps({
    field: Object,
    filterOption: Function
});
const emit = defineEmits([
    'change'
]);



async function searchInList(item, queryString) {
  if (queryString.length > 0) {
    serverIsLoadings[item] = true;
    searchResults[item] = $store.state[item].filter((it) => {
      return it.label.toLowerCase().indexOf(queryString.toLowerCase()) > -1;
    });
    serverIsLoadings[item] = false;
  } else {
    searchResults[item] = [];
    serverIsLoadings[item] = false;
  }
}
</script>
