<script setup lang="ts">
import { type HTMLAttributes, ref, watch } from 'vue'
import {
  SelectRoot,
  type SelectRootEmits,
  type SelectRootProps,
} from 'reka-ui'

interface Props extends Omit<SelectRootProps, 'modelValue'> {
  class?: HTMLAttributes['class']
  modelValue?: string
}

const props = defineProps<Props>()
const emits = defineEmits<{
  'update:modelValue': [value: string]
}>()

const internalValue = ref(props.modelValue)

watch(() => props.modelValue, (newValue) => {
  internalValue.value = newValue
})

const handleValueChange = (value: string) => {
  internalValue.value = value
  emits('update:modelValue', value)
}
</script>

<template>
  <SelectRoot :model-value="internalValue" @update:model-value="handleValueChange">
    <slot />
  </SelectRoot>
</template>
