<template>
<template v-if="!isAuthenticated || isPagePreview">
    <footer class="site-footer">
        <div class="site-footer__shape-bg"></div>
        <div class="container">
            <div class="site-footer__top">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="footer-widget__column footer-widget__newsletter">
                            <h3 class="footer-widget__newsletter-title">Subscribe To Our Newsletter To<br> Get Latest Update</h3>
                            <form class="footer-widget__newsletter-form" @submit.prevent="handleSubscribe">
                                <div class="footer-widget__newsletter-form-input-box">
                                    <input type="email" placeholder="Enter email" name="email" v-model="email" required>
                                </div>
                                <button type="submit" class="footer-widget__newsletter-btn thm-btn">
                                    Subscribe <span><i class="icon-right-arrow"></i></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="site-footer__middle">
                <div class="row">
                    <div class="col-xl-3 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="100ms">
                        <div class="footer-widget__column footer-widget__about">
                            <div class="footer-widget__logo">
                                <router-link to="/">
                                    <img :src="footerLogoUrl || '/assets/img/west-east-freight-meta-footer.png'" width="170" alt="Footer Logo">
                                </router-link>
                            </div>
                            <p class="footer-widget__about-text">
                                {{ footerTranslation?.footer_content || '' }}
                            </p>
                            <div class="site-footer__social">
                                <a v-for="social in socialIcons" :key="social.id" :href="social.link" target="_blank" rel="noopener noreferrer">
                                    <img :src="getSocialIconUrl(social.icon)" :alt="social.name || social.link" style="width: 16px; height: 16px; object-fit: contain;">
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="200ms">
                        <div class="footer-widget__column footer-widget__usefull-link">
                            <div class="footer-widget__title-box">
                                <h3 class="footer-widget__title">{{ quickLinksTitle }}</h3>
                            </div>
                            <div class="footer-widget__link-box">
                                <ul class="footer-widget__link list-unstyled">
                                    <li v-for="item in quickLinksMenu" :key="item.id">
                                        <a v-if="isExternalLink(item)" :href="item.url" :target="item.target || '_blank'" rel="noopener noreferrer">{{ getMenuItemTranslation(item)?.title || item.url }}</a>
                                        <router-link v-else :to="getMenuItemUrl(item)">{{ getMenuItemTranslation(item)?.title || item.url }}</router-link>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 wow fadeInUp animated" data-wow-delay="300ms" style="visibility: visible; animation-delay: 300ms;">
                            <div class="footer-widget__column footer-widget__services">
                                <div class="footer-widget__title-box">
                                    <h3 class="footer-widget__title">{{ servicesTitle }}</h3>
                                </div>
                                <ul class="footer-widget__link list-unstyled">
                                    <li v-for="item in servicesMenu" :key="item.id">
                                        <a v-if="isExternalLink(item)" :href="item.url" :target="item.target || '_blank'" rel="noopener noreferrer">{{ getMenuItemTranslation(item)?.title || item.url }}</a>
                                        <router-link v-else :to="getMenuItemUrl(item)">{{ getMenuItemTranslation(item)?.title || item.url }}</router-link>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="400ms">
                        <div class="footer-widget__column footer-widget__contact">
                            <div class="footer-widget__title-box">
                                <h3 class="footer-widget__title">{{ t.contact }}</h3>
                            </div>
                            <ul class="footer-widget__contact-list list-unstyled">
                                <li>
                                    <div class="icon">
                                        <span class="icon-phone-call"></span>
                                    </div>
                                    <div class="content">
                                        <p><a :href="'tel:' + (currentTranslation?.phone || '')">
                                            {{ currentTranslation?.phone || '' }}
                                        </a></p>
                                    </div>
                                </li>
                                <li>
                                    <div class="icon">
                                        <span class="icon-email"></span>
                                    </div>
                                    <div class="content">
                                        <p><a :href="'mailto:' + (currentTranslation?.email || '')">
                                            {{ currentTranslation?.email || '' }}
                                        </a></p>
                                    </div>
                                </li>
                                <li>
                                    <div class="icon">
                                        <span class="icon-location1"></span>
                                    </div>
                                    <div class="content">
                                        <p>{{ currentTranslation?.address || '' }}</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="site-footer__bottom">
                <div class="container">
                    <div class="site-footer__bottom-inner">
                        <p class="site-footer__bottom-text">© Copywright 2025 by <a href="/">westeastfreight.com</a> All Rights Reserved.</p>
                        <ul class="list-unstyled site-footer__bottom-menu">
                            <li v-for="item in bottomMenuItems" :key="item.id">
                                <a v-if="isExternalLink(item)" :href="item.url" :target="item.target || '_blank'" rel="noopener noreferrer">{{ getMenuItemTranslation(item)?.title || item.url }}</a>
                                <router-link v-else :to="getMenuItemUrl(item)">{{ getMenuItemTranslation(item)?.title || item.url }}</router-link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>
</template>

<script setup>
const isPagePreview = !!window.__PAGE_PREVIEW__;
import { useAuth } from '@/composables/useAuth';
const { isAuthenticated } = useAuth();
import { ref, onMounted, computed } from 'vue';
import directus from '@/services/directus';
import { apiRequest } from '@/services/http';
import Swal from 'sweetalert2';
import { readItems } from '@/services/directus';
import { useLanguage } from '@/composables/useLanguage';
import { commonTranslations } from '@/locales/common';

const { currentLanguage, getTranslation } = useLanguage();
const t = computed(() => commonTranslations[currentLanguage.value] || commonTranslations['en-US']);
const email = ref('');
const locationData = ref(null);
const footerData = ref(null);
const socialIcons = ref([]);
const quickLinksMenu = ref([]);
const quickLinksMenuData = ref(null);
const servicesMenu = ref([]);
const servicesMenuData = ref(null);
const bottomMenuItems = ref([]);

const currentTranslation = computed(() => {
  if (!locationData.value || !locationData.value.translations) return null;
  return getTranslation(locationData.value.translations);
});

const footerTranslation = computed(() => {
  if (!footerData.value || !footerData.value.translations) return null;
  return getTranslation(footerData.value.translations);
});

const quickLinksTitle = computed(() => {
  if (!quickLinksMenuData.value?.translations) return quickLinksMenuData.value?.title || '';
  const translation = getTranslation(quickLinksMenuData.value.translations);
  return translation?.menu_header || quickLinksMenuData.value?.title || '';
});

const servicesTitle = computed(() => {
  if (!servicesMenuData.value?.translations) return servicesMenuData.value?.title || '';
  const translation = getTranslation(servicesMenuData.value.translations);
  return translation?.menu_header || servicesMenuData.value?.title || '';
});

const footerLogoUrl = computed(() => {
  if (!footerData.value?.footer_logo) return '';
  const baseUrl = '/api/frontend';
  return `/storage/${footerData.value.footer_logo}`;
});

const getSocialIconUrl = (iconId) => {
  if (!iconId) return '';
  const baseUrl = '/api/frontend';
  return `/storage/${iconId}`;
};

const getMenuItemTranslation = (item) => {
  if (!item.translations || !Array.isArray(item.translations)) return null;
  return getTranslation(item.translations);
};

const getMenuItemUrl = (item) => {
  if (item.type === 'Posts' && item.posts) {
    return `/post/${item.posts}`;
  }
  return item.url || '#';
};
const isExternalLink = (item) => /^https?:\/\//i.test(item.url || '') || item.target === '_blank';

const handleSubscribe = async () => {
    try {
        await apiRequest('items/newsletter', { email: email.value });
        email.value = '';
        await Swal.fire({ icon: 'success', text: 'Your subscription request has been received.' });
    } catch (error) {
        await Swal.fire({ icon: 'error', text: error.message });
    }
};

onMounted(async () => {
  try {
    // Fetch location data from Directus
    const locationResponse = await directus.request(
      readItems('locations', {
        fields: ['*', 'translations.*'],
        filter: { status: { _eq: 'published' } }
      })
    );
    
    if (locationResponse && locationResponse.length > 0) {
      locationData.value = locationResponse[0];
    }

    // Fetch footer data from Directus
    const footerResponse = await directus.request(
      readItems('footer', {
        fields: ['*', 'translations.*'],
        filter: { status: { _eq: true } }
      })
    );
    
    if (footerResponse && footerResponse.length > 0) {
      footerData.value = footerResponse[0];
      
    }

    // Fetch social icons from Directus
    const socialResponse = await directus.request(
      readItems('social_icons', {
        fields: ['*', 'translations.*'],
        filter: { status: { _eq: true } }
      })
    );
    
    if (socialResponse && socialResponse.length > 0) {
      socialIcons.value = socialResponse;
     
    }

    // Fetch quick links menu from Directus
    const menuResponse = await directus.request(
      readItems('menu', {
        fields: ['*', 'items.*', 'items.translations.*', 'translations.*'],
        filter: { menu_id: { _eq: 'quick_links' }, is_active: { _eq: true } }
      })
    );
    
    if (menuResponse && menuResponse.length > 0) {
      quickLinksMenuData.value = menuResponse[0];
      quickLinksMenu.value = menuResponse[0].items || [];
    
    }

    // Fetch services menu from Directus
    const servicesResponse = await directus.request(
      readItems('menu', {
        fields: ['*', 'items.*', 'items.translations.*', 'translations.*'],
        filter: { menu_id: { _eq: 'solutions' }, is_active: { _eq: true } }
      })
    );
    
    if (servicesResponse && servicesResponse.length > 0) {
      servicesMenuData.value = servicesResponse[0];
      servicesMenu.value = servicesResponse[0].items || [];
    
    }

    const bottomResponse = await directus.request(
      readItems('menu', {
        fields: ['*', 'items.*', 'items.translations.*'],
        filter: { menu_id: { _eq: 'footer_bottom' }, is_active: { _eq: true } }
      })
    );
    bottomMenuItems.value = bottomResponse?.[0]?.items || [];
  } catch (error) {
    console.error('Error fetching data:', error);
  }
});
</script>

<style>
/* Myanmar language support for footer */
body.myanmar-lang footer .footer-widget__title {
    font-size: 0.95em;
}

body.myanmar-lang footer .footer-widget__link a,
body.myanmar-lang footer .footer-widget__about-text,
body.myanmar-lang footer .footer-widget__contact-list p {
    font-size: 1em;
}

/* Thai language support for footer */
body.thai-lang footer .footer-widget__title {
    font-size: 0.95em;
}

body.thai-lang footer .footer-widget__link a,
body.thai-lang footer .footer-widget__about-text,
body.thai-lang footer .footer-widget__contact-list p {
    font-size: 1em;
}
</style>
