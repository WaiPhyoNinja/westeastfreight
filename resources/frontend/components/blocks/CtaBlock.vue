<template>
  <section class="cta-section" :style="{ backgroundColor: blockData?.background_color || '#f8f9fa' }">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-8">
          <h2>{{ translation?.title || blockData?.title }}</h2>
          <p>{{ translation?.description || blockData?.description }}</p>
        </div>
        <div class="col-lg-4 text-end">
          <router-link v-if="blockData?.button_link" :to="blockData.button_link" class="thm-btn">
            {{ translation?.button_text || blockData?.button_text }}
            <span><i class="icon-right-arrow"></i></span>
          </router-link>
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
</script>

<style scoped>
.cta-section {
  padding: 60px 0;
}
</style>
