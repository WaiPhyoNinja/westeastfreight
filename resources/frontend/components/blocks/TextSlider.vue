<script setup>
import { computed } from 'vue';
import { useLanguage } from '@/composables/useLanguage';
const props = defineProps({ data: { type: Object, required: true } });
const { getTranslation } = useLanguage();
const items = computed(() => getTranslation(props.data.translations)?.items || []);
</script>
<template>
  <section v-if="items.length" class="sliding-text-one">
    <div class="sliding-text-one__wrap">
      <ul class="list-unstyled d-flex flex-wrap justify-content-center gap-4">
        <li v-for="(item, index) in items" :key="index"><h2 class="sliding-text__title">{{ item.text }}</h2></li>
      </ul>
    </div>
  </section>
</template>

<style scoped>
.sliding-text-one__wrap { padding: 0 20px; }
.sliding-text__title { font-size: clamp(28px, 6vw, 95px); overflow-wrap: anywhere; }
</style>
