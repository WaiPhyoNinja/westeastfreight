<script setup>
import { computed } from 'vue';
import { useLanguage } from '@/composables/useLanguage';
const props = defineProps({ data: { type: Object, required: true } });
const { getTranslation } = useLanguage();
const content = computed(() => getTranslation(props.data.translations) || {});
const items = computed(() => typeof content.value.items === 'string'
  ? content.value.items.split('\n').filter(Boolean).map(line => line.split('|').map(value => value.trim())) : []);
</script>
<template>
  <section class="content-section py-5">
    <div class="container" :style="{ textAlign: ['left', 'center', 'right'].includes(content.alignment) ? content.alignment : 'left' }">
      <img v-if="data.image_url" :src="data.image_url" alt="" class="img-fluid mb-4">
      <p v-if="content.eyebrow || content.tagline" class="section-title__tagline">{{ content.eyebrow || content.tagline }}</p>
      <h1 v-if="content.heading || content.headline">{{ content.heading || content.headline }}</h1>
      <div v-if="content.body || content.content" v-html="content.body || content.content"></div>
      <p v-if="content.subheading">{{ content.subheading }}</p>
      <div v-if="items.length" class="row mt-4">
        <div v-for="(item, index) in items" :key="index" class="col-md-4 mb-4"><h3>{{ item[0] }}</h3><p>{{ item[1] }}</p></div>
      </div>
      <a v-if="content.button_url" :href="content.button_url" class="thm-btn">{{ content.button_label }}</a>
    </div>
  </section>
</template>
