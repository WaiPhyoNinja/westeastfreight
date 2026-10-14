<template>
  <section class="content-section">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <h2 v-if="translation?.heading || blockData?.heading">
            {{ translation?.heading || blockData?.heading }}
          </h2>
          <div class="content-body" v-html="translation?.content || blockData?.content"></div>
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
.content-section {
  padding: 60px 0;
}

.content-body {
  margin-top: 30px;
}
</style>
