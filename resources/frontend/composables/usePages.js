import { ref } from 'vue';
import directus from '@/services/directus';
import { readItems } from '@/services/directus';

export function usePages() {
  const pages = ref([]);
  const loading = ref(false);
  const error = ref(null);

  // Fetch all published pages for navigation
  const fetchAllPages = async () => {
    loading.value = true;
    error.value = null;

    try {
      const response = await directus.request(
        readItems('pages', {
          fields: [
            'id',
            'title',
            'permalink',
            'parent_page',
            'sort',
            'status',
            'translations.*'
          ],
          filter: {
            status: { _eq: 'published' }
          },
          sort: ['sort', 'title']
        })
      );

      pages.value = response || [];
      return response;
    } catch (err) {
      console.error('Error fetching pages:', err);
      error.value = err;
      return [];
    } finally {
      loading.value = false;
    }
  };

  // Build hierarchical menu structure
  const buildMenuTree = (pagesList) => {
    const pageMap = {};
    const rootPages = [];

    // Create a map of all pages
    pagesList.forEach(page => {
      pageMap[page.id] = { ...page, children: [] };
    });

    // Build tree structure
    pagesList.forEach(page => {
      if (page.parent_page) {
        const parent = pageMap[page.parent_page];
        if (parent) {
          parent.children.push(pageMap[page.id]);
        }
      } else {
        rootPages.push(pageMap[page.id]);
      }
    });

    return rootPages;
  };

  return {
    pages,
    loading,
    error,
    fetchAllPages,
    buildMenuTree
  };
}
