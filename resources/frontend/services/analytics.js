// GA4 Analytics initializer using Vite env var
// Set VITE_GA_MEASUREMENT_ID in your .env files

export function initGA() {
  try {
    const measurementId = import.meta.env.VITE_GA_MEASUREMENT_ID;
    if (!measurementId) {
      console.warn('GA disabled: VITE_GA_MEASUREMENT_ID not set');
      return;
    }

    if (window.__gaInitialized) return;

    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${measurementId}`;
    document.head.appendChild(script);

    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag(){ window.dataLayer.push(arguments); };
    window.gtag('js', new Date());
    window.gtag('config', measurementId);

    window.__gaInitialized = true;
  } catch (err) {
    console.error('Failed to initialize GA:', err);
  }
}

export function registerRouterTracking(router) {
  const measurementId = import.meta.env.VITE_GA_MEASUREMENT_ID;
  if (!measurementId) return;

  router.afterEach((to) => {
    if (window.gtag) {
      window.gtag('config', measurementId, {
        page_path: to.fullPath,
      });
    }
  });
}
