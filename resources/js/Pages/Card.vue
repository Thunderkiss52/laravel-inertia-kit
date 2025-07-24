<template>
<el-card shadow="never" :body-style="`padding: ${small ? 10 : 20}px`">
    <div :class="{
                      'gap-1': small,
                      'gap-3': !small
                    }" class="lg:flex lg:items-center lg:justify-between">

          <el-image
              v-if="item.icon"
              :style="`height: 100%; width: ${small ? 40 : 50}px`"
              class="mr-1.5  shrink-0 text-gray-400"
              :src="item.icon"
            >
              <template #placeholder>
                <el-skeleton animated>
                  <template #template>
                    <el-skeleton-item
                      variant="image"
                      style="width: 50pxl"
                    />
                  </template>
                </el-skeleton>
              </template>
              <template #error>
                <el-skeleton>
                  <template #template>
                    <el-skeleton-item
                      variant="image"
                      style="width: 50px"
                    />
                  </template>
                </el-skeleton>
              </template>
            </el-image>
        <div class="min-w-0 flex-1">
            <div class="flex justify-between">
            <div>
                <div v-if="item.hint" class="flex items-center text-sm text-gray-500">
                    {{ item.hint }}
                </div>
                <h2 v-if="item.label"
                    :class="{
                      'sm:text-1md': small,
                      'sm:text-3xl': !small,
                      'text-1xl/7':  small,
                      'text-2xl/7':  !small,
                    }"

                    class="font-bold text-gray-900 sm:truncate sm:tracking-tight">
                    {{
                        item.label
                    }}
                </h2>
                </div>
                <el-button-group v-if="item.actions" size="small" style="float: right">
                    <a v-for="action in item.actions"
                        :href="action.url"
                    >
                        <el-button
                            :icon="Icons[action.icon]"
                            size="small"
                            type="primary"
                            >{{action.label}}</el-button
                        >
                    </a>
                </el-button-group>
            </div>
            <div v-if="item.features"
                class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6"
            >
                <div v-for="feature in item.features"
                    :class="{
                      'mt-0': small,
                      'mt-2': !small
                    }"
                    class="flex items-center text-sm text-gray-500"
                >
                <el-image
              v-if="feature.icon"
              class="mr-1.5 size-5 shrink-0 text-gray-400"
              :src="feature.icon"
            >
              <template #placeholder>
                <el-skeleton animated>
                  <template #template>
                    <el-skeleton-item
                      variant="image"
                      style="width: 50px"
                    />
                  </template>
                </el-skeleton>
              </template>
              <template #error>
                <el-skeleton>
                  <template #template>
                    <el-skeleton-item
                      variant="image"
                      style="width: 50px"
                    />
                  </template>
                </el-skeleton>
              </template>
            </el-image>
            {{ feature.label }}
                </div>
            </div>
        </div>
    </div>
</el-card>
</template>
<script setup>
import * as Icons from "@element-plus/icons-vue";
const { item, small } = defineProps({item: Object, small: {
  type: Boolean,
  default: false
}});
</script>