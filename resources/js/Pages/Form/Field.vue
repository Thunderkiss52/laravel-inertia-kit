<template>
<template v-if="field.type == 'number'">
    <el-input autocomplete="off" @input="emit('change')" type="text" :maxlength="
                    field.maxlength ?? (field.mask ? field.mask.length : null)
                  " show-word-limit :required="field.required" :placeholder="field.placeholder" :icon="field.icon ? Icons[field.icon] : null" v-model.trim.number="model" />
</template>
<vue-markdown-editor v-else-if="field.type == 'markdown-textarea'" v-model="model" height="400px" />

<DragMultipleSelect v-else-if="field.type == 'dragmultiplesearchselect'" :label="field.label" :link="field.link" v-model="model" :multiple="field.multiple" :canSearch="field.canSearch" :fields="field.fields" :addLink="field.addLink" :emptyFetch="field.emptyFetch" />

<template v-else-if="field.type == 'searchselect'">

<CustomSelect v-if="field.small" @input="emit('change')" :small="true" :label="field.label" v-model="model" :linkIncludes="field.linkIncludes && field.linkIncludes.length > 0 ? filterValues(field.linkIncludes) : null" :link="field.link" :multiple="field.multiple" :canSearch="field.canSearch" :fields="field.fields" :addLink="field.addLink" :emptyFetch="field.emptyFetch" />

<el-scrollbar v-else height="600px" style="border: 0.5px solid #c1c1c1; border-radius: 5px; width: 100%; padding: 10px">
    <CustomSelect @input="emit('change')" :small="false" :label="field.label" v-model="model" :linkIncludes="field.linkIncludes && field.linkIncludes.length > 0 ? filterValues(field.linkIncludes) : null" :link="field.link" :multiple="field.multiple" :canSearch="field.canSearch" :fields="field.fields" :addLink="field.addLink" :emptyFetch="field.emptyFetch" />
</el-scrollbar>
</template>
<template v-else-if="field.type == 'time'">
    <el-time-select v-model="model" :step="field.step" start="00:00" end="23:00" value-format="HH:mm" format="HH:mm" :placeholder="field.placeholder" />
</template>
<template v-else-if="field.type == 'file'">
    <el-upload style="width: 100%" :accept="field.accept ?? null" drag :limit="field.multiple ? 10 : 1" :on-change="
                    async (file, fileList) =>
                      processFile(file, field.multiple)
                  " :multiple="field.multiple" :auto-upload="false">
        <el-icon class="el-icon--upload">
            <Icons.UploadFilled />
        </el-icon>
        <div class="el-upload__text">
          Move or <em>select a file</em>
        </div>
        <!--<template #tip>
                                        <div class="el-upload__tip">
                                            jpg/png files with a size less than 500kb
                                        </div>
                                    </template>
        <el-button size="small" style="width: 100%" type="primary">Выберите файл</el-button> -->
    </el-upload>
</template>

<template v-else-if="field.type == 'checkbox'">
    <el-checkbox @input="emit('change')" :prefix-icon="field.icon ? Icons[field.icon] : null" :required="field.required" v-model="model">{{ field.label }}</el-checkbox>
</template>

<el-button :type="field.button_type" @click="playaction(field.action)" style="width: 100%" v-else-if="field.type == 'button'">{{ field.label }}</el-button>
<el-autocomplete autocomplete="off" @input="emit('change')" v-else-if="field.type == 'name'" :type="field.type" :maxlength="
                  field.maxlength ?? (field.mask ? field.mask.length : null)
                " show-word-limit :required="field.required" :trigger-on-focus="false" :fetch-suggestions="searchName" :placeholder="
                  field.placeholder ?? 'Thomas'
                " v-model="model" style="width: 100%" :prefix-icon="field.icon ? Icons[field.icon] : null" />
<!-- email -->
<template v-else-if="field.type == 'email'">
    <el-autocomplete autocomplete="off" @input="emit('change')" :type="field.type" :maxlength="
                    field.maxlength ?? (field.mask ? field.mask.length : null)
                  " show-word-limit :required="field.required" :trigger-on-focus="false" :fetch-suggestions="searchEmail" :placeholder="field.placeholder ?? 'example@mail.ru'
                  " v-model="model" style="width: 100%" :prefix-icon="field.icon ? Icons[field.icon] : Icons.Message" />
    <el-link :disabled="!model || model.length == 0" :icon="Icons.Message" :underline="false" target="_blank" :href="'mailto:' + model" type="primary">Write a letter</el-link>
</template>

<!-- address -->
<template v-else-if="field.type == 'address'">
    <el-autocomplete autocomplete="off" @input="emit('change')" :type="field.type" :maxlength="
                    field.maxlength ?? (field.mask ? field.mask.length : null)
                  " show-word-limit :required="field.required" :trigger-on-focus="false" :fetch-suggestions="searchAdress" :placeholder="field.placeholder ?? '29 Denmark Hill, Camberwell, London SE5 8RS'
                  " v-model="model" style="width: 100%" :prefix-icon="field.icon ? Icons[field.icon] : Icons.Position" />
    <el-link :disabled="!model || model.length == 0" :icon="Icons.Position" :underline="false" target="_blank" :href="
                    'https://yandex.ru/maps/213/moscow/search/' +
                    model
                  " type="primary">Show on the map</el-link>
</template>

<template v-else-if="field.type == 'tel'">

    <el-input autocomplete="off" @input="emit('change')" :type="field.type" :autosize="
                    field.type == 'textarea' ? { minRows: 2, maxRows: 4 } : ''
                  " :maxlength="
                    field.maxlength ?? (field.mask ? field.mask.length : null)
                  " show-word-limit v-mask="'###########'" :required="field.required" :placeholder="field.placeholder ?? '81234567890'
                  " v-model="model" :prefix-icon="field.icon ? Icons[field.icon] : Icons.Phone" />
    <el-link :disabled="!model || model.length == 0" :icon="Icons.Phone" :underline="false" target="_blank" :href="'tel:' + model" type="primary">Call</el-link>
    <el-link :disabled="!model || model.length == 0" :icon="Icons.ChatSquare" :underline="false" target="_blank" :href="'https://api.whatscom/send/?phone=' + model" type="primary">WhatsApp</el-link>
    <el-link :disabled="!model || model.length == 0" :icon="Icons.Position" :underline="false" target="_blank" :href="'https://t.me/' + model" type="primary">Telegram</el-link>
</template>
<template v-else>
<el-input autocomplete="off" @input="emit('change')" v-if="field.mask" :type="field.type" :autosize="
                    field.type == 'textarea' ? { minRows: 2, maxRows: 4 } : ''
                  " v-mask="field.mask" :maxlength="
                    field.maxlength ?? (field.mask ? field.mask.length : null)
                  " show-word-limit :required="field.required" :placeholder="field.placeholder" v-model="model" :prefix-icon="field.icon ? Icons[field.icon] : null" />
<el-input autocomplete="off" @input="emit('change')" v-else :type="field.type" :autosize="
                    field.type == 'textarea' ? { minRows: 2, maxRows: 4 } : ''
                  " :maxlength="
                    field.maxlength ?? (field.mask ? field.mask.length : null)
                  " show-word-limit :required="field.required" :placeholder="field.placeholder" v-model="model" :prefix-icon="field.icon ? Icons[field.icon] : null" />
</template>
</template>

<script setup>
import ruRU from '@kangc/v-md-editor/lib/lang/ru-RU';
import CustomSelect from "../CustomSelect.vue";
import DragMultipleSelect from "../DragMultipleSelect.vue";
import * as Icons from "@element-plus/icons-vue";
import { mask as vMask } from "vue-the-mask";

import VueMarkdownEditor from '@kangc/v-md-editor';
import '@kangc/v-md-editor/lib/style/base-editor.css';
import vuepressTheme from '@kangc/v-md-editor/lib/theme/vuepress.js';
import '@kangc/v-md-editor/lib/theme/style/vuepress.css';
import Prism from 'prismjs';
VueMarkdownEditor.use(vuepressTheme, {
  Prism,
});
VueMarkdownEditor.lang.use('ru-RU', ruRU);

const model = defineModel();
const {
    field,
    filterValues
} = defineProps({
    field: Object,
    filterValues: Function
});
const emit = defineEmits([
    'change'
]);

function processFile(file, multiple = false) {
  // setStatusNotSave(key);
  if (multiple) {
    if (Array.isArray(model.value)) {
      model.value.push(file.raw);
    } else {
      model.value = [file.raw];
    }
  } else {
    model.value = file.raw;
  }
}


function searchEmail(queryString, cb) {
  let context = this;
  axios
    .post(
      $config.dadata.baseURL + "4_1/rs/suggest/email",
      {
        query: queryString,
      },
      {
        headers: {
          "Content-Type": "application/json",
          authorization: "Token " + $config.dadata.token,
        },
      }
    )
    .then((response) => {
      cb(response.suggestions);
    })
    .catch((exception) => {
      context.$message.error("Failed to retrieve data");
    });
}

function searchName(queryString, cb) {
  let context = this;
  axios
    .post(
      $config.dadata.baseURL + "4_1/rs/suggest/fio",
      {
        query: queryString,
      },
      {
        headers: {
          "Content-Type": "application/json",
          authorization: "Token " + $config.dadata.token,
        },
      }
    )
    .then((response) => {
      cb(response.suggestions);
    })
    .catch((exception) => {
      context.$message.error("Failed to retrieve data");
    });
}
function searchAdress(queryString, cb) {
  let context = this;
  axios
    .post(
      $config.dadata.baseURL + "4_1/rs/suggest/address",
      {
        query: queryString,
      },
      {
        headers: {
          "Content-Type": "application/json",
          authorization: "Token " + $config.dadata.token,
        },
      }
    )
    .then((response) => {
      cb(
        response.suggestions.filter((sug) => {
          return sug.data.city == "London";
        })
      );
    })
    .catch((exception) => {
      context.$message.error("Failed to retrieve data");
    });
}

</script>
