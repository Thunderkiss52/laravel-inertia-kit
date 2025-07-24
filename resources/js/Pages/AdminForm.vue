<template>
<el-config-provider :locale="curLocale == 'en' ? en : curLocale == 'ru' ? ru : curLocale == 'kz' ? kz : null">

  <div ref="captcha" />
  <div v-loading="form.processing || savingTemplate">
  <div class="flex justify-between gap-2">
    <!-- <div> -->
      <template v-if="(props.templates && props.templates.length > 0) || templatesData.length > 0">
        <el-scrollbar always>
        <draggable class="flex gap-2 flex-row pb-2" style="width: 100%" :animation="200" v-model="templatesData" @end="onDragEnd" item-key="id">
          <template #item="{ element: ind }">
            <el-button-group style="display: flex; margin: 0px; margin-bottom: 5px; margin-left: 5px" >
              <el-button :icon="Icons.Document" @click="applyTemplate(ind)"size="small" type="primary">
                {{ind.name}}
              </el-button>
              <el-button @click="destroyTemplate(ind)" style="margin: 0px" type="danger" :icon="Icons.Delete" />
            </el-button-group>
          </template>
        </draggable>
        </el-scrollbar>
      </template>
    <!-- </div> -->
    <div v-if="props.draftsLink">
      <el-button @click="saveTemplate()" size="small" type="primary" :icon="Icons.Plus">Save template</el-button>
    </div>
  </div>
    <el-result
      v-if="isError"
      icon="error"
      title="Error"
      subTitle="Failed to send data"
    >
      <template #extra>
        <el-button
          @click="
            isError = false;
            sendData();
          "
          type="primary"
          size="medium"
          >Repeat</el-button
        >
        <el-button @click="isError = false" type="primary" size="medium"
          >Cancel</el-button
        >
      </template>
    </el-result>

    <!--<el-result v-if="noAccess" icon="warning" title="Ошибка" subTitle="Нет доступа"></el-result> -->
    <el-empty
      v-if="noAccess"
      image="/gif/key.gif"
      description="Have no access"
      :image-size="200"
    ></el-empty>
  <div v-if="form.errors" class="flex gap-2 flex-col pb-3">
    <template v-for="i in Object.keys(form.errors)">
      <el-alert v-if="!Object.keys(form).includes(i)" effect="dark" :closable="false" :title="form.errors[i]" type="error" show-icon />
    </template>
  </div>

    <template v-if="!form.processing">
      <template v-for="row in props.fields">
        <template v-for="(field, index) in row">
          <el-alert
            v-if="field.required && field.type === 'select' && !field.options"
            :key="index"
            effect="dark"
            title="Attention"
            style="margin-bottom: 10px"
            :description="
              'For the required field «' +
              field.label +
              '» no available values'
            "
            type="error"
            :closable="false"
            show-icon
          />
        </template>
      </template>
    </template>

    <!--description-->
    <el-descriptions
      v-if="props.infodata"
      class="margin-top"
      :column="3"
      border
    >
      <el-descriptions-item
        v-bind:key="index"
        v-for="(info, index) in props.infodata"
      >
        <template #label>
          <i v-if="info.icon" :class="info.icon"></i>
          {{ info.label }}
        </template>
        {{ form[info.key] }}
      </el-descriptions-item>
    </el-descriptions>
    <el-form
      @submit.native.prevent="sendData()"
      v-if="props.fields.length > 0"
      autocomplete="off"
      :hidden="isError || noAccess"
      :disabled="form.processing"
      :model="values"
      ref="formRef"
      :class="`admin-form-${random}`"
      :label-position="props.labelPosition"
      label-width="100px"
    >
      <el-row
        :gutter="20"
        v-for="(row, index) in props.fields"
        v-bind:key="index"
      >
        <template v-for="(field, index) in row">
          <el-col
            v-if="
              (!field.visibleif || filterOption(field.visibleif)) &&
              (!field.displayifset || checkSettedOption(field.displayifset))
            "
            :sm="24"
            :md="vertical ? 24 : 24 / row.length"
          >
            <el-form-item
              :error="field.startKey ? form.errors[field.startKey] : form.errors[getErrorKey(field.key)]"
              :prop="field.key ?? field.startKey"
              :required="field.required"
              :label="
                field.type != 'button' && field.type != 'checkbox'
                  ? disabled[field.key]
                    ? '[Blocked] ' + field.label
                    : field.label
                  : null
              "
            >
              <DateRange 
                v-if="
                  [
                    'timerange',
                    'years',
                    'datetimerange',
                    'daterange',
                    'monthrange'
                  ].includes(field.type)"
                  v-model:startKey="form[field.startKey]"
                  v-model:endKey="form[field.endKey]"
                  :field="field"
                  @change="() => setStatusNotSave([field.startKey,field.endKey])"
              />
              <ChooseFile v-else-if="field.type == 'filelist'" v-model="form[field.key]" :mimeTypes="field.accept" :limit="field.limit" :link="field.source" />
              <el-radio-group
                @change="
                  setStatusNotSave(field.key);
                  field.onselect ? playaction(field.onselect) : null;
                "
                :required="field.required"
                style="width: 100%"
                v-model="form[field.key]"
                v-else-if="field.type == 'radio'"
              >
                <template v-if="field.style == 'button'">
                  <el-radio-button
                    v-bind:key="option.value"
                    v-for="option in field.options"
                    :disabled="option.disabled"
                    :value="option.value"
                    >{{ option.label }}</el-radio-button
                  >
                </template>
                <el-table
                  fit
                  v-else-if="field.style == 'table'"
                  :data="field.options"
                >
                  <el-table-column
                    v-for="colkey in Object.keys(field.columns)"
                    header-align="center"
                    align="center"
                    :prop="colkey"
                    :label="field.columns[colkey]"
                  />
                  <el-table-column header-align="center" align="right">
                    <template #default="scope">
                      <el-button
                        v-if="form[field.key] == scope.row.id"
                        disabled
                        type="success"
                        size="small"
                        >Selected</el-button
                      >
                      <el-button
                        v-else
                        type="primary"
                        @click="form[field.key] = scope.row.id"
                        size="small"
                        >Select</el-button
                      >
                    </template>
                  </el-table-column>
                </el-table>
                <template v-else>
                  <el-radio
                    v-bind:key="option.value"
                    v-for="option in field.options"
                    :value="option.value"
                    >{{ option.label }}</el-radio
                  >
                </template>
              </el-radio-group>
                <el-link
                  v-if="
                    field.item &&
                    field.item.length > 0 &&
                    form[field.key] &&
                    form[field.key] > 0
                  "
                  :icon="Icons.Link"
                  :underline="false"
                  target="_blank"
                  :href="'/' + field.item + '/' + form[field.key]"
                  type="primary"
                  >Go to page {{ field.label.toLowerCase() }}</el-link
                >

              

              <template v-else-if="field.type == 'table'">
                <el-button
                  v-if="field.changeRows && form[field.key] && form[field.key].length > 0"
                  :icon="Icons.Download"
                  type="warning"
                  @click="
                    Array.isArray(form[field.key])
                      ? form[field.key].unshift({})
                      : (form[field.key] = [{}])
                  "
                  style="width: 100%"
                  >Add row above</el-button
                >
                <el-table
                  :data.sync="form[field.key]"
                  border
                  style="width: 100%; margin: 10px 0"
                >
                  <el-table-column width="50" label="№">
                    <template #default="scope">
                      {{ scope.$index + 1 }}
                    </template>
                  </el-table-column>
                  <el-table-column
                    v-bind:key="key"
                    v-for="(column, key) in field.columns"
                    :prop="key"
                    :label="column.label"
                  >
                    <template #default="scope">
                        <template v-if="column.edit == false">{{
                          scope.row[column.key]
                        }}</template>
                        <template v-else>
                        <el-input
                          required
                          v-if="
                          ['year',
                          'month',
                          'date',
                          'dates',
                          'months',
                          'years',
                          'datetime',
                          'week',
                          'datetimerange',
                          'daterange',
                          'monthrange'].includes(column.type)"
                          type="date"
                          :placeholder="column.label"
                          size="small"
                          v-model="scope.row[column.key]"
                        />
                      <el-input
                        required
                        v-else
                        :placeholder="column.label"
                        size="small"
                        v-model="scope.row[column.key]"
                      />
                      </template>
                    </template>
                  </el-table-column>
                  <el-table-column v-if="field.changeRows" width="180">
                    <template #default="scope">
                      <el-button-group>
                          <el-button
                            size="small"
                            @click.native.prevent="
                              form[field.key].splice(scope.$index, 0, {})
                            "
                            style="width: 33%"
                            type="warning"
                            :icon="Icons.Top"
                          ></el-button>
                          <el-button
                            size="small"
                            @click.native.prevent="
                              form[field.key].splice(scope.$index + 1, 0, {})
                            "
                            style="width: 33%"
                            type="warning"
                            :icon="Icons.Bottom"
                          ></el-button>
                          <el-button
                            size="small"
                            @click.native.prevent="
                              form[field.key].splice(scope.$index, 1)
                            "
                            style="width: 33%"
                            type="danger"
                            :icon="Icons.Delete"
                          ></el-button>
                        </el-button-group>
                    </template>
                  </el-table-column>
                </el-table>
                <el-button
                  v-if="field.changeRows"
                  :icon="Icons.Upload"
                  type="warning"
                  @click="
                    Array.isArray(form[field.key])
                      ? form[field.key].push({})
                      : (form[field.key] = [{}])
                  "
                  style="width: 100%"
                  >Add row below</el-button
                >
              </template>


              <!-- staffList -->
              <template v-else-if="field.type == 'staffList'">
                <el-autocomplete
                  autocomplete="off"
                  @input="setStatusNotSave(field.key)"
                  :type="field.type"
                  :maxlength="
                    field.maxlength ?? (field.mask ? field.mask.length : null)
                  "
                  show-word-limit
                  :required="field.required"
                  :trigger-on-focus="false"
                  :fetch-suggestions="searchStaff"
                  :placeholder="field.placeholder ?? 'John'
                  "
                  v-model="form[field.key]"
                  style="width: 100%"
                  :prefix-icon="field.icon ? Icons[field.icon] : Icons.Position"
                />
              </template>

              <!--table-->
              <!-- <template v-else-if="field.type == 'tableView'">
                <admin-table
                  :mini="field.mini ?? false"
                  :item_name="field.item ?? null"
                  :initrows="
                    field.key && form[field.key] ? form[field.key] : []
                  "
                  :docs="field.docs ?? []"
                  :columnActions="field.columnActions ?? []"
                  :columns="field.columns ?? {}"
                  :rowColorFunction="field.rowColor ?? null"
                  :massActions="field.massActions ?? []"
                  style="padding: 20px 0"
                />
              </template> -->
              
              <BulletList v-else-if="field.type == 'bulletlist'" @change="setStatusNotSave(field.key)" :field="field" v-model="form[field.key]" />
              
              <Date v-else-if="
                  [
                    'year',
                    'month',
                    'date',
                    'years',
                    'datetime',
                    'week',
                  ].includes(field.type)"
                  @change="setStatusNotSave(field.key)" :field="field" v-model="form[field.key]" />
              <SelectField v-else-if="
                  [
                    'select',
                    'list',
                  ].includes(field.type)"
                  :filterOption="filterOption"
                  @change="setStatusNotSave(field.key)" :field="field" v-model="form[field.key]" />

              <template v-else-if="field.type != 'hidden'">
                
                  <Field v-if="
                  [
                    'name',
                    'email',
                    'address',
                    'tel',
                    'textarea',
                    'markdown-textarea',
                    'text',
                    'number',
                    'searchselect',
                    'dragmultiplesearchselect',
                    'time',
                    'file',
                    'checkbox',
                    'button',
                    'password'
                  ].includes(field.type)"
                  :filterValues="getFilterValues"

                  @change="setStatusNotSave(field.key)" :field="field" v-model="form[field.key]" />
              </template>
              <div
                v-else-if="field.type == 'select' && field.allowCreate"
                class="sub-title"
              >
                Select an item from the list or enter a new one and press Enter
              </div>

              <template v-if="field.actions">
                <el-link
                  v-bind:key="index"
                  v-for="(action, index) in field.actions"
                  :icon="action.icon"
                  :disabled="form[field.key] ? false : true"
                  :underline="false"
                  target="_blank"
                  :href="action.link(form[field.key])"
                  :type="action.type ?? 'primary'"
                  >{{ action.label }}</el-link
                >
              </template>
            </el-form-item>
          </el-col>
          
              </template>
      </el-row>
      <div>
        <slot name="after" />
      </div>
      <template v-if="!props.hideSubmit">
        <section
          style="
            display: flex;
            flex-direction: row;
            gap: 10px;
            justify-content: end;
          "
        >
          <div>
          <!-- <el-affix :target="`.admin-form-${random}`" position="bottom" :offset="20"> -->
            <el-button
              :loading="captchaProcess"
              native-type="submit"
              type="primary"
              >{{ props.submitLabel }}</el-button
            >
            <el-button
              native-type="reset"
              v-if="props.initialvalueslink == null && props.initdata == null"
              type="info"
              >{{$page.props.locale.reset}}</el-button
            >
            <!-- </el-affix> -->
          </div>
        </section>
      </template>
    </el-form>
  </div>
  </el-config-provider>
</template>
<script setup>
import draggable from 'vuedraggable';
import axios from "axios";
import * as Icons from "@element-plus/icons-vue";
import { ref, onBeforeMount, onUpdated, onMounted, watch } from "vue";
import { usePage } from '@inertiajs/vue3';
import { useForm } from "laravel-precognition-vue-inertia";
import { useSmartCaptcha } from "vue3-smart-captcha";
import { mask as vMask } from "vue-the-mask";
import "element-plus/theme-chalk/index.css";
import Date from "./Form/Date.vue";
import Field from "./Form/Field.vue";
import DateRange from "./Form/DateRange.vue";
import BulletList from "./Form/BulletList.vue";
import SelectField from "./Form/SelectField.vue";
import ChooseFile from "./Form/ChooseFile.vue";
import { ElConfigProvider, ElMessageBox } from "element-plus";
import dayjs from "dayjs";
import ru from 'element-plus/es/locale/lang/ru';
import en from 'element-plus/es/locale/lang/en';
import kz from 'element-plus/es/locale/lang/kk';
import 'dayjs/locale/ru';
import 'dayjs/locale/en';
import 'dayjs/locale/kk';

const page = usePage();
const curLocale = (page.props.layout && page.props.layout.locale && page.props.layout.locale.target ? page.props.layout.locale.target : 'ru').toLowerCase();


if(curLocale != 'en') {
dayjs.locale(curLocale == "kz" ? 'kk' : curLocale, {
  weekStart: 1, // Понедельник — первый день недели
});
}

const formRef = ref(null);
let change_watcher_count = 0;
let isError = false;
let noAccess = false;
let disabled = [];
let values = {};
const savingTemplate = ref(false);
const captchaProcess = ref(false);
const captcha = ref();
const templatesData = ref([]);
let firstCaptcha = null;

function saveTemplate() {
  ElMessageBox.prompt('Name', 'Save form template.', {
    inputErrorMessage: 'Enter the template name',
  })
    .then(({ value }) => {
      savingTemplate.value = true;
      axios.post(props.draftsLink, {
        name: value,
        data: form.data()
      })
    .then((res) => {
      ElMessage.success(`Template ${value} saved`);
      fetchTemplates();
    })
    .catch((e) => {
      ElMessage.error(`Failed to apply the template ${value}`);
    })
    .finally(() => {
      savingTemplate.value = false;
    });
    });
}

function applyTemplate(template) {
      savingTemplate.value = true;
  axios.get(route('forms.draft.show', template.id))
  .then((res) => {
    Object.keys(res.data).forEach((k) => {
      form[k] = res.data[k]; 
    });
    ElMessage.success(`Template ${template.name} Apply`);
  })
  .catch((e) => {
    ElMessage.error(`Failed to apply the template ${template.name}`);
  })
  .finally(() => {
      savingTemplate.value = false;
    });
}



function fetchTemplates() {
  axios.get(props.draftsLink).then((res) => {
      templatesData.value = res.data;
  });
}

const sitekey = "ysc1_pJpyB4olX8lL1Yvaq6pkmMYAJV2aR6onYnPqjQVB8aa7d5a9";

function initCaptcha() {
  firstCaptcha = useSmartCaptcha(
    captcha,
    {
      sitekey: sitekey,
      hl: curLocale,
      invisible: true,
      hideShield: true,
    },
    true,
    1000
  );
  firstCaptcha.subscribeTo("success", (token) => {
    form.captcha = token;
    sendAfterCheck();
  });
  console.log("initCaptcha");
}

function getErrorKey(key) {
  return key.replace("[", ".").replace("]", "");
}

const random = Math.random();

const props = defineProps({
  templates: {
    type: Array,
    default: null
  },
  draftsLink: {
    type: String,
    default: null
  },
  precognition: {
    type: Boolean,
    default: true,
  },
  captcha: {
    type: Boolean,
    default: false,
  },
  initialvalueslink: {
    type: String,
    default: null,
  },
  initdata: {
    type: Object,
    default: {},
  },
  labelPosition: {
    type: String,
    default: "top",
  },
  itemName: {
    type: String,
    default: null,
  },

  successclear: {
    type: Boolean,
    default: false,
  },

  needupdate: {
    type: Boolean,
    default: true,
  },
  method: {
    type: String,
    required: true,
    default: "post",
  },
  submitLabel: {
    type: String,
    default: "Save",
  },
  link: {
    type: String,
    required: true,
  },
  fields: {
    type: Array,
    required: true,
    default: () => [],
  },

  infodata: {
    type: Array,
    default: () => [],
  },
  hideSubmit: {
    type: Boolean,
    default: false,
  },
  vertical: {
    type: Boolean,
    default: false,
  },
  enableSaveStatus: {
    type: Boolean,
    default: false,
  },
});

if (props.captcha) {
  initCaptcha();
}
let valuesTemp = new Object();
if (props.method) {
  valuesTemp["_method"] = props.method;
}

if (props.captcha) {
  valuesTemp["captcha"] = null;
}

props.fields.forEach((row) => {
  row.forEach((field) => {
    if(field.type == 'daterange' || field.type == 'timerange') {
      valuesTemp[field.startKey] = props.initdata[field.startKey] ?? null;
      valuesTemp[field.endKey] = props.initdata[field.endKey] ?? null;
    } else if(field.type == 'markdown-textarea') {
      if(props.initdata[field.key] == null) {
        valuesTemp[field.key] =  '';
      } else {
        valuesTemp[field.key] = props.initdata[field.key]; 
      }
      
    } else {
      valuesTemp[field.key] = props.initdata[field.key] ?? null;
    }
  });
});

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


function filterOption(condition) {
  let exist = true;
  Object.keys(condition).forEach((key) => {
    if (Array.isArray(condition[key])) {
      if (!condition[key].includes(form[key])) {
        exist = false;
      }
    } else if(Array.isArray(form[key])) {
      if (!form[key].includes(condition[key])) {
        exist = false;
      }
    } else if (condition[key] != form[key]) {
      exist = false;
    }
  });
  return exist;
}

function checkSettedOption(condition) {
  // console.log(condition);
  let exist = true;
  condition.forEach((key) => {
    
    if (form[key] == null || form[key] == undefined) {
      exist = false;
    }
  });
  return exist;
}

function getFilterValues(condition) {
  let result = {};
  condition.forEach((key) => {
    result[key] = form[key];
  });
  return result;
}

const form = useForm('POST', props.link, valuesTemp);

async function playaction(action) {
  new Promise( (resolve, reject) => action);
}
function setStatusNotSave(fkey) {
  emit("changed");
  if (!props.captcha && props.precognition) {
    form.validate(fkey);
  }
  if (props.enableSaveStatus) {
    if (
      (props.initialvalueslink == null &&
        props.initdata == null &&
        change_watcher_count > 0) ||
      ((props.initialvalueslink || props.initdata) && change_watcher_count > 1)
    ) {
      window.onbeforeunload = () => true;
    }

    change_watcher_count += 1;

  }
}

function sendAfterCheck() {
  captchaProcess.value = false;
let updates = {};
    
props.fields.forEach((row) => {
    row.forEach((field) => {
      if(field.type == 'dragmultiplesearchselect' && Array.isArray(form.data()[field.key])) {
        updates[field.key] = form.data()[field.key].map(f => f.id);
      }
    });
    
});
form.transform((data) => ({
        ...data,
        ...updates
        }));
  form.post(props.link, {
    forceFormData: true,
    preserveScroll: true,
    preserveState: true,
    onError: function (error) {
    if(firstCaptcha) {
      firstCaptcha.reset();
    }
      console.log(error);
      form.captcha = null;
    },
    onSuccess: function () {
      emit("success");
      if(props.successclear) {
        form.reset();
      }
    },
  });
}

function checkCaptcha() {
  captchaProcess.value = true;
  if(firstCaptcha) {
  firstCaptcha.reset();
  firstCaptcha.execute();
  }
}

const sendData = async function () {
  isError = false;
  if (!form.processing && !captchaProcess.value) {
    if (props.captcha) {
      checkCaptcha();
    } else {
      sendAfterCheck();
    }
  }
};

const getValues = () => form.data();

const setValue = function (key, value) {
  let updates = {};
  updates[key] = value;
  form.transform((data) => ({
    ...data,
    ...updates,
  }));
};

const setErrors = function (errors) {
  form.errors = errors;
};

const isValid = async function () {
  if (!form.isDirty) {
    await form.validate();
    await new Promise((resolve) => setTimeout(resolve, 2000));
  }
  return !form.hasErrors && !form.validating;
};

defineExpose({
  sendData,
  getValues,
  setErrors,
  setValue,
  isValid,
});

const emit = defineEmits(["success", "changed"]);

onMounted(() => {
  if(!props.templates && props.draftsLink) {
    fetchTemplates();
  }
});

onBeforeMount(function () {
  if (
    props.initialvalueslink == null &&
    props.initdata == null
  ) {
    noAccess = true;
  }

  let temp_disabled = [];
  props.fields.forEach((row) => {
    row.forEach((field) => {
      if (field.disabled) {
        temp_disabled[field.key] = true;
      }

      if (field.initvalue) {
        form[field.key] = field.initvalue;
      }

      if (
        (field.type == "table" || field.type == "bulletlist") &&
        props.initialvalueslink == null &&
        props.initdata == null
      ) {
        form[field.key] = [{}];
      }
    });
  });
  disabled = temp_disabled;
});

function destroyTemplate(template) {

ElMessageBox.confirm(
    `Delete Template ${template.name}?`,
    'Attention',
    {
      type: 'warning',
    }
  )
    .then(() => {
      savingTemplate.value = true;
      axios.delete(route('forms.draft.destroy', template.id))
      .then((res) => {
        Object.keys(res.data).forEach((k) => {
          form[k] = res.data[k]; 
        });
        ElMessage.warning(`Template ${template.name} deleted`);
        fetchTemplates();
      })
      .catch((e) => {
        ElMessage.error(`Failed to delete the template ${template.name}`);
      })
      .finally(() => {
          savingTemplate.value = false;
        });
    });
}

onUpdated(() => {
  if(props.captcha) {
    initCaptcha();
  }
});
</script>
