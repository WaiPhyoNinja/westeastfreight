<template>
  <section class="features-section">
    <div class="container">
      <div class="section-title text-center">
        <h2>{{ translation?.title || blockData?.title }}</h2>
        <p>{{ translation?.subtitle || blockData?.subtitle }}</p>
      </div>
      <div class="row">
        <div 
          v-for="(feature, index) in features" 
          :key="index" 
          class="col-lg-4 col-md-6 mb-4"
        >
          <div class="feature-card">
            <div class="feature-icon">
              <i :class="feature.icon"></i>
            </div>
            <h3>{{ getFeatureTranslation(feature)?.title || feature.title }}</h3>
            <p>{{ getFeatureTranslation(feature)?.description || feature.description }}</p>
          </div>
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

const features = computed(() => {
  return blockData.value?.features || [];
});

const getFeatureTranslation = (feature) => {
  if (!feature?.translations) return null;
  return getTranslation(feature.translations);
};
</script>

<style scoped>
.features-section {
  padding: 80px 0;
}

.section-title {
  margin-bottom: 50px;
}

.feature-card {
  padding: 30px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  transition: all 0.3s;
}

.feature-card:hover {
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.feature-icon {
  font-size: 48px;
  margin-bottom: 20px;
  color: var(--tanspot-base);
}
</style>
