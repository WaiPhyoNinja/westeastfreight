<template>
  <component 
    v-for="block in blocks" 
    :key="block.id"
    :is="getBlockComponent(block.collection)"
    :data="block.data"
  />
</template>

<script setup>
import { defineAsyncComponent } from 'vue';
import NativeBlock from './blocks/NativeBlock.vue';

const props = defineProps({
  blocks: {
    type: Array,
    required: true,
    default: () => []
  }
});

// Dynamically import block components
const blockComponents = {
  'block_service_image': defineAsyncComponent(() => import('./blocks/ServiceImage.vue')),
  'block_about_us': defineAsyncComponent(() => import('./blocks/AboutContent.vue')),
  'block_map_content': defineAsyncComponent(() => import('./blocks/MapContact.vue')),
  'block_info': defineAsyncComponent(() => import('./blocks/Info.vue')),
  'block_contact_card': defineAsyncComponent(() => import('./blocks/ContactCard.vue')),
  'block_quotes': defineAsyncComponent(() => import('./blocks/Quote.vue')),
  'block_services': defineAsyncComponent(() => import('./blocks/Services.vue')),
  'block_faqs': defineAsyncComponent(() => import('./blocks/FAQ.vue')),
  'block_textslider': defineAsyncComponent(() => import('./blocks/TextSlider.vue')),
  'block_processflow': defineAsyncComponent(() => import('./blocks/ProcessFlow.vue')),
  'block_branded': defineAsyncComponent(() => import('./blocks/Branded.vue')),
  'block_masthead': defineAsyncComponent(() => import('./blocks/MastheadBlock.vue')),
  'block_header': defineAsyncComponent(() => import('./blocks/PageMasthead.vue')),
  'block_hero': defineAsyncComponent(() => import('./blocks/HeroBlock.vue')),
  'block_features': defineAsyncComponent(() => import('./blocks/FeaturesBlock.vue')),
  'block_content': defineAsyncComponent(() => import('./blocks/ContentBlock.vue')),
  'block_cta': defineAsyncComponent(() => import('./blocks/CtaBlock.vue')),
  'block_service_details': defineAsyncComponent(() => import('./blocks/ServiceDetailsBlock.vue'))
};

const getBlockComponent = (collection) => {
  if (collection.startsWith('native_') || collection === 'block_richtext') return NativeBlock;
  return blockComponents[collection] || 'div';
};
</script>
