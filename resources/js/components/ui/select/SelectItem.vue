<template>
  <div
    class="relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
    @click="handleClick"
  >
    <span class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
      <Check v-if="selected" class="h-4 w-4" />
    </span>
    <slot />
  </div>
</template>

<script setup lang="ts">
import { Check } from 'lucide-vue-next';

interface Props {
  value: string;
  selected?: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  select: [value: string];
}>();

const handleClick = () => {
  emit('select', props.value);
};
</script>
