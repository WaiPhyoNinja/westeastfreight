<script setup>
import { onMounted, watch, ref } from 'vue';
import Footer from '../layouts/Footer.vue';
import Header from '../layouts/Header.vue';
import Chat from './Chat.vue';
import MobileSlider from './MobileSlider.vue';
import ScrollToTop from './ScrollToTop.vue';
import SearchPopup from './SearchPopup.vue';
import SliderWidget from './SliderWidget.vue';
import BlockRenderer from './BlockRenderer.vue';
import NotFound from './NotFound.vue';
import { useBlocks } from '@/composables/useBlocks';
import { useRoute } from 'vue-router';

const route = useRoute();
const { blocks, loading, error, fetchPageBlocks } = useBlocks();
const pageNotFound = ref(false);

const loadPage = async () => {
  if (route.path.startsWith('/preview/pages/') && window.__PAGE_PREVIEW__) {
    blocks.value = window.__PAGE_PREVIEW__.blocks;
    pageNotFound.value = false;
    return;
  }
  // Reset page not found state
  pageNotFound.value = false;
  
  // Build permalink from route params
  let permalink = route.path;
  
  // Fetch page blocks based on permalink
  const page = await fetchPageBlocks(permalink);
  
  // If no page found and blocks are empty, show 404
  if (!page && blocks.value.length === 0) {
    pageNotFound.value = true;
  }
};

onMounted(async () => {
  await loadPage();

  // Initialize Bootstrap dropdowns for language switcher
  if (window.bootstrap) {
    const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
    [...dropdownElementList].map(dropdownToggleEl => new window.bootstrap.Dropdown(dropdownToggleEl));
  }
});

// Watch for route changes and reload page
watch(() => route.path, async () => {
  await loadPage();
});
</script>

<template>
    <div class="page-wrapper">
        <div v-if="route.path.startsWith('/preview/pages/')" class="preview-notice">Preview — saved content only. This does not publish the page.</div>
        <Header />
        
        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
          <p>Loading...</p>
        </div>
        
        <div v-else-if="error" class="loading-state" role="alert">
          <p>Unable to load this page. <button type="button" @click="loadPage">Try again</button></p>
        </div>
        <!-- 404 Page -->
        <NotFound v-else-if="pageNotFound" />
        
        <!-- Dynamic Blocks -->
        <BlockRenderer v-else :blocks="blocks" />
    
        <Footer />
    </div>
    <Chat />
    <MobileSlider />
    <SearchPopup />
    <ScrollToTop />
    <SliderWidget />
</template>

<style scoped>
.preview-notice { padding: 12px 20px; background: #fff3df; color: #062f3a; text-align: center; font-size: 14px; }
/* All styles are loaded from the main CSS files */
.loading-state {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
}
</style>
