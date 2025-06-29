<template>
  <div class="relative w-full overflow-auto">
    <div class="h-full w-full rounded-[inherit]">
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  orientation?: 'vertical' | 'horizontal';
  type?: 'auto' | 'always' | 'scroll' | 'hover';
}

withDefaults(defineProps<Props>(), {
  orientation: 'vertical',
  type: 'hover',
});
</script>
