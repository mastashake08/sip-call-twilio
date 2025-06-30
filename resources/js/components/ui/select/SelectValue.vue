<template>
  <SelectValue :placeholder="placeholder">
    <slot />
  </SelectValue>
</template>

<script setup lang="ts">
import { SelectValue } from 'reka-ui'

interface Props {
  placeholder?: string
}

defineProps<Props>()
</script>
