<!-- Example component showing how to use useLanguage composable -->
<script setup>
import { ref, onMounted, computed } from 'vue';
import directus from '@/services/directus';
import { readItems } from '@/services/directus';
import { useLanguage } from '@/composables/useLanguage';

const { currentLanguage, getTranslation } = useLanguage();
const data = ref(null);

// Example: Get translated data
const translatedContent = computed(() => {
  if (!data.value || !data.value.translations) return null;
  return getTranslation(data.value.translations);
});

onMounted(async () => {
  try {
    // Fetch any collection with translations
    const response = await directus.request(
      readItems('locations', {
        fields: ['*', 'translations.*']
      })
    );
    
    if (response && response.length > 0) {
      data.value = response[0];
    }
  } catch (error) {
    console.error('Error fetching data:', error);
  }
});
</script>

<template>
  <div>
    <!-- Use translated content -->
    <h1>{{ translatedContent?.title }}</h1>
    <p>{{ translatedContent?.description }}</p>
    
    <!-- Current language: {{ currentLanguage }} -->
  </div>
</template>
