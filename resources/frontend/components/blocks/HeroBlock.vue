<template>
  <section class="hero-section">
    <div class="container">
      <div class="row">
        <div class="col-lg-6">
          <h1 class="hero-title">{{ translation?.title || blockData?.title }}</h1>
          <p class="hero-description">{{ translation?.description || blockData?.description }}</p>
          <router-link v-if="blockData?.button_link" :to="blockData.button_link" class="thm-btn">
            {{ translation?.button_text || blockData?.button_text }}
            <span><i class="icon-right-arrow"></i></span>
          </router-link>
        </div>
        <div class="col-lg-6">
          <img v-if="blockData?.image" :src="getImageUrl(blockData?.image)" :alt="translation?.title || blockData?.title" class="img-fluid">
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import { useLanguage } from '@/composables/useLanguage';

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
});

const { getTranslation } = useLanguage();

const blockData = computed(() => props.data);

const translation = computed(() => {
  if (!blockData.value?.translations) return null;
  return getTranslation(blockData.value.translations);
});

const getImageUrl = (imageId) => {
  if (!imageId) return '';
  if (imageId.startsWith('http')) return imageId;
  const baseUrl = '/api/frontend';
  return `/storage/${imageId}`;
};
</script>

<style scoped>
.hero-section {
  padding: 80px 0;
}

.hero-title {
  font-size: 48px;
  font-weight: bold;
  margin-bottom: 20px;
}

.hero-description {
  font-size: 18px;
  margin-bottom: 30px;
}
</style>
