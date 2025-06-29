<template>
  <span v-if="value" class="pointer-events-none">
    {{ displayValue }}
  </span>
  <span v-else class="pointer-events-none text-muted-foreground">
    {{ placeholder }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  value?: string;
  placeholder?: string;
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Select an option...',
});

const displayValue = computed(() => {
  return props.value || '';
});
</script>
