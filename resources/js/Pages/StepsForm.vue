<template>
    <el-steps class="mb-3" :active="currentStepNumber" align-center>
        <el-step v-for="step in Object.keys(steps)" :title="steps[step].title"
            :description="steps[step].description ?? null" />
    </el-steps>
    <template v-for="t in Object.keys(steps)">
        <div v-show="step == t">
            <AdminForm method="POST" :link="`${link}/create/${t}`" v-if="steps[t].form" :precognition="true"
                :ref="formRefs[t]" :fields="steps[t].form.fields" :hideSubmit="true" />
            <slot v-else :forms="formValues" :name="t" />
        </div>
    </template>
    <el-button-group class="my-3" style="float: right;">
        <el-button :disabled="isSending || !isLoad || step == Object.keys(steps)[0]" @click="previewStep()" type="primary"
            :icon="Icons.ArrowLeft">Back</el-button>
        <el-button :loading="!isLoad" v-if="step != Object.keys(steps)[Object.keys(steps).length - 1]"
            @click="nextStep()" type="primary">
            Next<el-icon class="el-icon--right">
                <Icons.ArrowRight />
            </el-icon>
        </el-button>
        <el-button :loading="!isLoad || isSending" v-if="step == Object.keys(steps)[Object.keys(steps).length - 1]"
            @click="saveData()" type="success">
            Create
        </el-button>
    </el-button-group>
</template>
<script setup>

import AdminForm from './AdminForm.vue';
import * as Icons from '@element-plus/icons-vue';
import { onMounted, ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
const emit = defineEmits(['changed', 'beforeSubmit']);
const { steps, link, method } = defineProps({
    steps: {
        type: Object,
        default: {}
    },
    link: {
        type: String,
    },
    method: {
        type: String,
        default: 'POST'
    }
});


let formRefs = new Object();
Object.keys(steps).forEach(st => {
    formRefs[st] = ref(null);
});


onMounted(() => {
    setTimeout(() => {
        formValues = computed(() => {
            let values = new Object();
            Object.keys(steps).forEach(st => {
                if (steps[st].form) {
                    //console.log(Object.keys(formRefs[st]).includes('_value'));
                    //console.log(formRefs[st].value[0].getValues());
                    //&& formRefs[st]._value.length > 0
                    if (Object.keys(formRefs[st]).includes('_value')) {
                        //console.log(formRefs[st].value[0]);
                        values[st] = formRefs[st].value[0].getValues();
                    }
                }
            });
            return values;
        });
        isLoad.value = true;
    }, 1000)
});

let formValues = {};
const otherData = ref({});

const setData = function (key, value) {
    console.log('setData');
    otherData.value[key] = value;
}

defineExpose({
    setData
});

const isLoad = ref(false);
const isSending = ref(false);

async function saveData() {
    emit('beforeSubmit', formValues);
    isSending.value = true;
    let isValid = false;

    if (steps[step.value].form) {
        isValid = await formRefs[Object.keys(steps)[currentStepNumber.value]].value[0].isValid();
        console.log(isValid);
    } else {
        isValid = true;
    }

    if (isValid) {
        let values = new Object();
        values['_method'] = method;
        Object.keys(steps).forEach(st => {
            console.log(formRefs[st]._value);
            if (Object.keys(formRefs[st]).includes('_value') && formRefs[st]._value != null && formRefs[st]._value.length > 0) {
                console.log(formRefs[st].value[0]);
                values[st] = formRefs[st].value[0].getValues();
                delete values[st]._method;
            }
        });
        router.post(link, {
            ...values,
            ...otherData.value
        })
    }
    isSending.value = false;
}


async function nextStep() {
    isLoad.value = false;
    let isValid = false;
    if (steps[step.value].form) {
        isValid = await formRefs[Object.keys(steps)[currentStepNumber.value]].value[0].isValid();
        console.log(isValid);
    } else {
        isValid = true;
    }
    if (isValid) {
        step.value = Object.keys(steps)[currentStepNumber.value + 1];
    }
    isLoad.value = true;
}


function previewStep() {
    step.value = Object.keys(steps)[currentStepNumber.value - 1];
}

const currentStepNumber = computed(() => {
    let i = 0;
    let current = null;
    Object.keys(steps).forEach(st => {
        if (step.value == st) {
            current = i;
        }
        i++;
    });
    return current;
});



const step = ref(Object.keys(steps)[0]);

watch(step, (newStep, oldStep) => {
    emit('changed', {
        oldStep, oldStep,
        newStep: newStep,
        data: formValues
    });
});
</script>