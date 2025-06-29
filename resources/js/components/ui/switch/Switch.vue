<template>
  <SwitchPrimitive.Root
    v-bind="forwardedProps"
    :class="cn(
      'peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=unchecked]:bg-input',
      className
    )"
  >
    <SwitchPrimitive.Thumb
      :class="cn('pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform data-[state=checked]:translate-x-5 data-[state=unchecked]:translate-x-0')"
    />
  </SwitchPrimitive.Root>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { SwitchRoot, SwitchThumb, useForwardPropsEmits } from 'reka-ui'
import { cn } from '@/lib/utils'

const SwitchPrimitive = {
  Root: SwitchRoot,
  Thumb: SwitchThumb,
}

interface Props {
  class?: string
  defaultChecked?: boolean
  checked?: boolean
  disabled?: boolean
  required?: boolean
  name?: string
  value?: string
  id?: string
}

interface Emits {
  (e: 'update:checked', value: boolean): void
}

const props = defineProps<Props>()
const emits = defineEmits<Emits>()

const forwardedProps = useForwardPropsEmits(props, emits)

const className = computed(() => props.class)
</script>
