<template>
    <div class="xs-sidebar-group info-group info-sidebar">
        <div class="xs-overlay xs-bg-black"></div>
        <div class="xs-sidebar-widget">
            <div class="sidebar-widget-container">
                <div class="widget-heading">
                    <a href="#" class="close-side-widget">X</a>
                </div>
                <div class="sidebar-textwidget">
                    <div class="sidebar-info-contents">
                        <div class="content-inner">
                            <div class="logo">
                                <a href="/"><img :src="getLogoUrl" width="170" alt="Logo"></a>
                            </div>
                            
                            <!-- Menu Links -->
                            <div class="content-box">
                                <ul class="sidebar-menu list-unstyled">
                                    <li v-for="item in menuItems" :key="item.id">
                                        <a :href="item.url || '#'">
                                            {{ getMenuItemTranslation(item)?.title || item.url }}
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <div class="sidebar-contact-info">
                                <h4>Contact Info</h4>
                                <ul class="list-unstyled">
                                    <li v-if="currentTranslation?.address">
                                        <span class="icon-location1"></span> {{ currentTranslation.address }}
                                    </li>
                                    <li v-if="currentTranslation?.phone">
                                        <span class="icon-phone-call"></span>
                                        <a :href="'tel:' + currentTranslation.phone">{{ currentTranslation.phone }}</a>
                                    </li>
                                    <li v-if="currentTranslation?.email">
                                        <span class="icon-email"></span>
                                        <a :href="'mailto:' + currentTranslation.email">{{ currentTranslation.email }}</a>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="thm-social-link1" v-if="socialIcons.length > 0">
                                <ul class="social-box list-unstyled">
                                    <li v-for="social in socialIcons" :key="social.id">
                                        <a :href="social.link" target="_blank" rel="noopener noreferrer">
                                            <img :src="getSocialIconUrl(social.icon)" :alt="social.link" style="width: 16px; height: 16px; object-fit: contain; filter: brightness(0) invert(1);">
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import directus from '@/services/directus';
import { readItems } from '@/services/directus';
import { useLanguage } from '@/composables/useLanguage';

const { currentLanguage, getTranslation } = useLanguage();
const locationData = ref(null);
const socialIcons = ref([]);
const menuItems = ref([]);
const headerData = ref(null);

const currentTranslation = computed(() => {
  if (!locationData.value || !locationData.value.translations) return null;
  return getTranslation(locationData.value.translations);
});

const getMenuItemTranslation = (item) => {
  if (!item.translations || !Array.isArray(item.translations)) return null;
  return getTranslation(item.translations);
};

const getSocialIconUrl = (iconId) => {
  if (!iconId) return '';
  const baseUrl = '/api/frontend';
  return `/storage/${iconId}`;
};

const getLogoUrl = computed(() => {
  if (!headerData.value?.logo) return '';
  const baseUrl = '/api/frontend';
  return `/storage/${headerData.value.logo}`;
});

onMounted(async () => {
  try {
    // Fetch menu from Directus
    const menuResponse = await directus.request(
      readItems('menu', {
        fields: ['*', 'items.*', 'items.translations.*'],
        filter: { menu_id: { _eq: 'header' }, is_active: { _eq: true } }
      })
    );
    
    if (menuResponse && menuResponse.length > 0) {
      menuItems.value = menuResponse[0].items || [];
    }

    // Fetch location data
    const response = await directus.request(
      readItems('locations', {
        fields: ['*', 'translations.*'],
        filter: { status: { _eq: 'published' } }
      })
    );
    
    if (response && response.length > 0) {
      locationData.value = response[0];
    }

    // Fetch social icons
    const socialResponse = await directus.request(
      readItems('social_icons', {
        fields: ['*', 'translations.*'],
        filter: { status: { _eq: true } }
      })
    );
    
    if (socialResponse && socialResponse.length > 0) {
      socialIcons.value = socialResponse;
    }

    // Fetch header data (logo)
    const baseUrl = '/api/frontend';
    const headerResponse = await fetch(`${baseUrl}/items/header?fields=*,items.*,translations.*`);
    const headerJson = await headerResponse.json();
        
    if (headerJson.data && headerJson.data.length > 0) {
      headerData.value = headerJson.data[0];
    }
  } catch (error) {
    console.error('Error fetching slider widget data:', error);
  }
});
</script>

<style scoped>
.sidebar-menu {
    padding-left: 0;
    margin: 20px 0;
}

.sidebar-menu li {
    padding: 8px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-menu li:last-child {
    border-bottom: none;
}

.sidebar-menu li a {
    color: #fff;
    font-size: 15px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.sidebar-menu li a:hover {
    color: var(--tanspot-base, #e03e2d);
    padding-left: 10px;
}
</style>