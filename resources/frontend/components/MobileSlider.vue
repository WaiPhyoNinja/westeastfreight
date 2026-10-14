<script setup>
const isPagePreview = !!window.__PAGE_PREVIEW__;
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import directus from '@/services/directus';
import { readItems } from '@/services/directus';
import { useLanguage } from '@/composables/useLanguage';
import { useMobileNav } from '@/composables/useMobileNav';
import { useAuth } from '@/composables/useAuth';
import { useAuthTranslation } from '@/locales/auth';

const router = useRouter();
const { currentLanguage, getTranslation, setLanguage } = useLanguage();
const { isMobileNavOpen, toggleMobileNav } = useMobileNav();
const { isAuthenticated, user } = useAuth();
const t = computed(() => useAuthTranslation(currentLanguage.value));

const menuItems = ref([]);
const locationData = ref(null);
const socialIcons = ref([]);
const headerData = ref(null);
const isLanguageDropdownOpen = ref(false);

// Language display info
const languages = [
  { code: 'en', name: 'English', flag: 'us', fullCode: 'en-US' },
  { code: 'mm', name: 'Myanmar', flag: 'mm', fullCode: 'mm' },
  { code: 'th', name: 'ThaiLand', flag: 'th', fullCode: 'th' }
];

const currentLanguageInfo = computed(() => {
  return languages.find(lang => lang.fullCode === currentLanguage.value) || languages[0];
});

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

const toggleLanguageDropdown = () => {
  isLanguageDropdownOpen.value = !isLanguageDropdownOpen.value;
};

const changeLanguage = (langCode) => {
  setLanguage(langCode);
  isLanguageDropdownOpen.value = false;
};

const handleNavClick = (url) => {
  if (url && url !== '#') {
    router.push(url);
    toggleMobileNav();
  }
};

onMounted(async () => {
  try {
    // Fetch menu from Directus
    const menuResponse = await directus.request(
      readItems('menu', {
        fields: ['*', 'items.*', 'items.translations.*', 'items.sub_menu.*', 'items.sub_menu.translations.*'],
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


    const baseUrl = '/api/frontend';
    const headerResponse = await fetch(`${baseUrl}/items/header?fields=*,items.*,translations.*`);
    const headerJson = await headerResponse.json();
        
    if (headerJson.data && headerJson.data.length > 0) {
      headerData.value = headerJson.data[0];
    }


  } catch (error) {
    console.error('Error fetching mobile nav data:', error);
  }
});
</script>

<template>
<template v-if="!isAuthenticated || isPagePreview">
    <div class="mobile-nav__wrapper" :class="{ 'expanded': isMobileNavOpen }">
        <div class="mobile-nav__overlay mobile-nav__toggler" @click="toggleMobileNav"></div>
        <!-- /.mobile-nav__overlay -->
        <div class="mobile-nav__content">
            <span class="mobile-nav__close mobile-nav__toggler" @click="toggleMobileNav"><i class="fa fa-times"></i></span>
            <div class="logo-box">
                <a href="#" @click.prevent="handleNavClick('/')" aria-label="logo image">
                    <a href="/"><img :src="getLogoUrl || ''" width="140" alt="west east freight"></a>
                </a>
            </div>
            <!-- /.logo-box -->
            <div class="mobile-nav__container">
                <ul class="main-menu__list">
                    <li v-for="item in menuItems" :key="item.id" :class="{ 'dropdown': item.type === 'Group' && item.sub_menu && item.sub_menu.length > 0 }">
                        <a :href="item.url || '#'" @click.prevent="handleNavClick(item.url)">
                            {{ getMenuItemTranslation(item)?.title || item.url }}
                            <button v-if="item.type === 'Group' && item.sub_menu && item.sub_menu.length > 0" aria-label="dropdown toggler">
                                <i class="fa fa-angle-down"></i>
                            </button>
                        </a>
                        <ul v-if="item.type === 'Group' && item.sub_menu && item.sub_menu.length > 0" class="shadow-box">
                            <li v-for="subItem in item.sub_menu" :key="subItem.id">
                                <a :href="subItem.url || '#'" @click.prevent="handleNavClick(subItem.url)">
                                    {{ getMenuItemTranslation(subItem)?.title || subItem.url }}
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <!-- User Menu Items -->
                    <li v-if="isAuthenticated">
                        <a href="#" @click.prevent="handleNavClick('/my-account')">{{ t.myAccount }}</a>
                    </li>
                    <li v-else>
                        <a href="#" @click.prevent="handleNavClick('/login')">{{ t.login }}</a>
                    </li>
                    <li>
                        <a href="#" @click.prevent="handleNavClick('/request-quote')">{{ t.requestQuote }}</a>
                    </li>
                </ul>
            </div>
            <!-- /.mobile-nav__container -->

            <ul class="mobile-nav__contact list-unstyled">
                <li v-if="currentTranslation?.email">
                    <i class="fa fa-envelope"></i>
                    <a :href="'mailto:' + currentTranslation.email">{{ currentTranslation.email }}</a>
                </li>
                <li v-if="currentTranslation?.phone">
                    <i class="fas fa-phone"></i>
                    <a :href="'tel:' + currentTranslation.phone">{{ currentTranslation.phone }}</a>
                </li>
            </ul><!-- /.mobile-nav__contact -->
            
            <!-- Language Selector -->
            <div class="mobile-nav__language">
                <div class="language-selector" @click="toggleLanguageDropdown">
                    <img :src="`https://flagcdn.com/w20/${currentLanguageInfo.flag}.png`" :alt="currentLanguageInfo.name" class="current-flag">
                    <span>{{ currentLanguageInfo.name }}</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <ul class="language-dropdown" :class="{ 'show': isLanguageDropdownOpen }">
                    <li v-for="lang in languages" :key="lang.code" :class="{ 'active': currentLanguage === lang.fullCode }">
                        <a href="#" @click.prevent="changeLanguage(lang.code)">
                            <img :src="`https://flagcdn.com/w20/${lang.flag}.png`" :alt="lang.name">
                            {{ lang.name }}
                            <i v-if="currentLanguage === lang.fullCode" class="fas fa-check"></i>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="mobile-nav__top">
                <div class="mobile-nav__social" v-if="socialIcons.length > 0">
                    <a v-for="social in socialIcons" :key="social.id" :href="social.link" target="_blank" rel="noopener noreferrer">
                        <img :src="getSocialIconUrl(social.icon)" :alt="social.link" style="width: 16px; height: 16px; object-fit: contain;">
                    </a>
                </div><!-- /.mobile-nav__social -->
            </div><!-- /.mobile-nav__top -->

        </div>
        <!-- /.mobile-nav__content -->
    </div>
</template>
</template>

<style scoped>
.mobile-nav__social a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    margin-right: 8px;
    transition: all 0.3s ease;
}

.mobile-nav__social a:hover {
    background: var(--tanspot-base, #e03e2d);
    transform: translateY(-3px);
}

.mobile-nav__social a img {
    filter: brightness(0) invert(1);
}

/* Language Selector Styles */
.mobile-nav__language {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.language-selector {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.language-selector:hover {
    background: rgba(255, 255, 255, 0.1);
}

.language-selector .current-flag {
    width: 20px;
    height: auto;
}

.language-selector span {
    flex: 1;
    color: #fff;
    font-size: 15px;
    font-weight: 500;
}

.language-selector i {
    color: #fff;
    font-size: 12px;
    transition: transform 0.3s ease;
}

.language-selector:hover i {
    transform: rotate(180deg);
}

.language-dropdown {
    list-style: none;
    padding: 0;
    margin: 10px 0 0 0;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 8px;
    overflow: hidden;
    max-height: 0;
    opacity: 0;
    transition: all 0.3s ease;
}

.language-dropdown.show {
    max-height: 200px;
    opacity: 1;
    margin-top: 10px;
}

.language-dropdown li {
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.language-dropdown li:last-child {
    border-bottom: none;
}

.language-dropdown li.active a {
    background: rgba(224, 62, 45, 0.2);
}

.language-dropdown li a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    color: #fff;
    text-decoration: none;
    transition: all 0.3s ease;
}

.language-dropdown li a:hover {
    background: var(--tanspot-base, #e03e2d);
}

.language-dropdown li a img {
    width: 20px;
    height: auto;
}

.language-dropdown li a .fa-check {
    margin-left: auto;
    color: var(--tanspot-base, #e03e2d);
    font-size: 14px;
}

.language-dropdown li a:hover .fa-check {
    color: #fff;
}
</style>
