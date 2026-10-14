import { ref } from 'vue';
import content, { readItems } from '@/services/directus';
export function useBlocks() {
  const blocks = ref([]), loading = ref(false), error = ref(null);
  let requestId = 0;
  const fetchPageBlocks = async (permalink) => {
    const id = ++requestId;
    loading.value = true;
    error.value = null;
    blocks.value = [];
    try {
      const pages = await content.request(readItems('pages', { filter: { permalink: { _eq: permalink } } }));
      if (id !== requestId) return null;
      const page = pages[0] || null;
      blocks.value = page?.blocks || [];
      return page;
    } catch (err) {
      if (id === requestId) error.value = err;
      return null;
    } finally {
      if (id === requestId) loading.value = false;
    }
  };
  return { blocks, loading, error, fetchPageBlocks };
}
