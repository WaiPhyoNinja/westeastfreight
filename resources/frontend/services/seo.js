import directus from '@/services/directus';
import { readItems } from '@/services/directus';

const DIRECTUS_URL = '/api/frontend';

function setOrUpdateMeta(selectorAttr, key, content) {
  if (!content && content !== '') return;
  let tag = document.head.querySelector(`meta[${selectorAttr}="${key}"]`);
  if (!tag) {
    tag = document.createElement('meta');
    tag.setAttribute(selectorAttr, key);
    document.head.appendChild(tag);
  }
  tag.setAttribute('content', String(content));
}

function setCanonical(url) {
  if (!url) return;
  let link = document.head.querySelector('link[rel="canonical"]');
  if (!link) {
    link = document.createElement('link');
    link.setAttribute('rel', 'canonical');
    document.head.appendChild(link);
  }
  link.setAttribute('href', url);
}

function getAssetUrl(assetId) {
  if (!assetId) return null;
  return `/storage/${assetId}`;
}

async function fetchPageSEO(path) {
  try {
    const res = await directus.request(
      readItems('pages', {
        fields: ['title', 'permalink', 'status', 'seo.*'],
        filter: {
          status: { _eq: 'published' },
          permalink: { _eq: path },
        },
        limit: 1,
      })
    );
    if (Array.isArray(res) && res.length > 0) return res[0];
    return null;
  } catch (err) {
    console.error('SEO: fetchPageSEO error', err);
    return null;
  }
}

export async function applySEOForRoute(to) {
  const currentUrl = `${window.location.origin}${to.path}`;
  const page = await fetchPageSEO(to.path);
  const seo = page?.seo || {};

  const title = seo.title || page?.title || 'West East Freight Logistics';
  document.title = title;

  // Description
  if (seo.meta_description) {
    setOrUpdateMeta('name', 'description', seo.meta_description);
  }

  // Robots
  const robots = seo.no_index === true ? 'noindex, nofollow' : 'index, follow';
  setOrUpdateMeta('name', 'robots', robots);

  // Open Graph
  setOrUpdateMeta('property', 'og:title', title);
  if (seo.meta_description) setOrUpdateMeta('property', 'og:description', seo.meta_description);
  setOrUpdateMeta('property', 'og:type', 'website');
  setOrUpdateMeta('property', 'og:url', currentUrl);
  const ogImage = getAssetUrl(seo.og_image);
  if (ogImage) setOrUpdateMeta('property', 'og:image', ogImage);

  // Twitter
  setOrUpdateMeta('name', 'twitter:card', 'summary_large_image');
  setOrUpdateMeta('name', 'twitter:title', title);
  if (seo.meta_description) setOrUpdateMeta('name', 'twitter:description', seo.meta_description);
  if (ogImage) setOrUpdateMeta('name', 'twitter:image', ogImage);

  // Canonical
  setCanonical(currentUrl);
}

export function registerRouterSEO(router) {
  router.afterEach(async (to) => {
    try {
      await applySEOForRoute(to);
    } catch (err) {
      console.error('SEO: failed to apply for route', err);
    }
  });
}
