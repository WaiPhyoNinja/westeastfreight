
<script setup>
const isPagePreview = !!window.__PAGE_PREVIEW__;
import { ref, onMounted, computed, watch } from 'vue';
import directus from '@/services/directus';
import { readItems } from '@/services/directus';
import { useLanguage } from '@/composables/useLanguage';
import { useAuth } from '@/composables/useAuth';
import { useAuthTranslation } from '@/locales/auth';
import { useMobileNav } from '@/composables/useMobileNav';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';

const { currentLanguage, setLanguage, initLanguage, getTranslation } = useLanguage();
const { user, isAuthenticated, logout, checkAuth } = useAuth();
const { toggleMobileNav } = useMobileNav();
const router = useRouter();
const t = computed(() => useAuthTranslation(currentLanguage.value));
const locationData = ref(null);
const socialIcons = ref([]);
const menuItems = ref([]);
const headerData = ref(null);
const isDropdownOpen = ref(false);
const isDropdownOpenSticky = ref(false);
const showUserDropdown = ref(false);
const showUserDropdownSticky = ref(false);
const showContactPopup = ref(false);

const currentTranslation = computed(() => {
  if (!locationData.value || !locationData.value.translations) return null;
  return getTranslation(locationData.value.translations);
});

const changeLanguage = (lang) => {
  setLanguage(lang);
  isDropdownOpen.value = false;
  isDropdownOpenSticky.value = false;
};

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
  isDropdownOpenSticky.value = false;
};

const toggleDropdownSticky = () => {
  isDropdownOpenSticky.value = !isDropdownOpenSticky.value;
  isDropdownOpen.value = false;
};

// Language display info
const languages = [
  { code: 'en', name: 'English', flag: 'us', fullCode: 'en-US' },
  { code: 'mm', name: 'Myanmar', flag: 'mm', fullCode: 'mm' },
  { code: 'th', name: 'ThaiLand', flag: 'th', fullCode: 'th' }
];

const currentLanguageInfo = computed(() => {
  return languages.find(lang => lang.fullCode === currentLanguage.value) || languages[0];
});

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

// Watch for language changes and apply body class
watch(currentLanguage, (newLang) => {
  document.body.classList.remove('myanmar-lang', 'thai-lang');
  
  // Add appropriate class based on language
  if (newLang === 'mm') {
    document.body.classList.add('myanmar-lang');
  } else if (newLang === 'th') {
    document.body.classList.add('thai-lang');
  }
}, { immediate: true });

const getMenuItemTranslation = (item) => {
  if (!item.translations || !Array.isArray(item.translations)) return null;
  return getTranslation(item.translations);
};

const toggleUserDropdown = () => {
  showUserDropdown.value = !showUserDropdown.value;
  showUserDropdownSticky.value = false;
};

const toggleUserDropdownSticky = () => {
  showUserDropdownSticky.value = !showUserDropdownSticky.value;
  showUserDropdown.value = false;
};

const toggleContactPopup = () => {
  // On mobile, trigger the mobile navigation instead
  if (window.innerWidth <= 991) {
    toggleMobileNav();
  } else {
    // On desktop, show contact popup
    showContactPopup.value = !showContactPopup.value;
  }
};

const handleLogout = async () => {
  const result = await Swal.fire({
    title: t.value.logout,
    text: t.value.logoutConfirm,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#e03e2d',
    cancelButtonColor: '#6c757d',
    confirmButtonText: t.value.yes,
    cancelButtonText: t.value.cancel
  });

  if (result.isConfirmed) {
    await logout();
    showUserDropdown.value = false;
    showUserDropdownSticky.value = false;
    
    Swal.fire({
      title: t.value.logout,
      text: t.value.loggedOut,
      icon: 'success',
      confirmButtonColor: '#e03e2d',
      timer: 1500
    });
    
    router.push('/');
  }
};

onMounted(async () => {
  try {
    // Initialize language from localStorage or default to English
    initLanguage();
    
    // Check authentication status
    await checkAuth();

    // Fetch location data from Directus
    const response = await directus.request(
      readItems('locations', {
        fields: ['*', 'translations.*'],
        filter: { status: { _eq: 'published' } }
      })
    );
    
    if (response && response.length > 0) {
      locationData.value = response[0];
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

    // Fetch header data (logo) from Directus
    const baseUrl = '/api/frontend';
    const headerResponse = await fetch(`${baseUrl}/items/header?fields=*,items.*,translations.*`);
    const headerJson = await headerResponse.json();
    
    if (headerJson.data && headerJson.data.length > 0) {
      headerData.value = headerJson.data[0];
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
      const dropdowns = document.querySelectorAll('.main-menu__language-box');
      let clickedInside = false;
      
      dropdowns.forEach(dropdown => {
        if (dropdown && dropdown.contains(e.target)) {
          clickedInside = true;
        }
      });
      
      if (!clickedInside) {
        isDropdownOpen.value = false;
        isDropdownOpenSticky.value = false;
      }
    });

    // Handle window resize - close contact popup if switching between mobile/desktop
    window.addEventListener('resize', () => {
      if (showContactPopup.value && window.innerWidth <= 991) {
        showContactPopup.value = false;
      }
    });
  } catch (error) {
    console.error('Error fetching location data:', error);
  }
});
</script>

<template>
<template v-if="!isAuthenticated || isPagePreview">
    <header class="main-header">
            <div class="main-menu__top">
                <div class="main-menu__top-inner">
                    <ul class="list-unstyled main-menu__contact-list">
                        <li>
                            <div class="icon">
                                <i class="icon-phone-call"></i>
                            </div>
                            <div class="text">
                                <p><a :href="'tel:' + (currentTranslation?.phone || '')">
                                    {{ currentTranslation?.phone || '' }}
                                </a></p>
                            </div>
                        </li>
                        <li>
                            <div class="icon">
                                <i class="icon-email"></i>
                            </div>
                            <div class="text">
                                <p><a :href="'mailto:' + (currentTranslation?.email || '')">
                                    {{ currentTranslation?.email || '' }}
                                </a></p>
                            </div>
                        </li>
                        <li>
                            <div class="icon">
                                <i class="icon-location1"></i>
                            </div>
                            <div class="text">
                                <p>{{ currentTranslation?.address || '' }}</p>
                            </div>
                        </li>
                    </ul>
                    <p class="main-menu__top-welcome-text">Welcome to Our Tanspot Office</p>
                    <div class="main-menu__top-right">
                        <div class="main-menu__top-time">
                            <div class="main-menu__top-time-icon">
                                <span class="fas fa-clock"></span>
                            </div>
                            <p class="main-menu__top-text">{{ currentTranslation?.opening_hours || '' }}</p>
                        </div>
                        <div class="main-menu__social">
                            <a v-for="social in socialIcons" :key="social.id" :href="social.link" target="_blank" rel="noopener noreferrer">
                                <img :src="getSocialIconUrl(social.icon)" :alt="social.link" style="width: 16px; height: 16px; object-fit: contain;">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <nav class="main-menu">
                <div class="main-menu__wrapper">
                    <div class="main-menu__wrapper-inner">
                        <div class="main-menu__left">
                            <div class="main-menu__logo">
                                <a href="#"><img :src="getLogoUrl || '/assets/img/west-east-freight-meta.png'" width="170" alt="Logo"></a>
                            </div>
                        </div>
                        <div class="main-menu__main-menu-box">
                            <a href="#" class="mobile-nav__toggler" @click.prevent="toggleMobileNav"><i class="fa fa-bars"></i></a>
                            <ul class="main-menu__list" :class="{ 'myanmar-active': currentLanguage === 'mm', 'thai-active': currentLanguage === 'th' }">
                                <li v-for="item in menuItems" :key="item.id" :class="{ 'dropdown': item.type === 'Group' && item.sub_menu && item.sub_menu.length > 0 }">
                                    <a :href="item.url || '#'">{{ getMenuItemTranslation(item)?.title || item.url }}</a>
                                    <ul v-if="item.type === 'Group' && item.sub_menu && item.sub_menu.length > 0" class="shadow-box">
                                        <li v-for="subItem in item.sub_menu" :key="subItem.id">
                                            <a :href="subItem.url || '#'">{{ getMenuItemTranslation(subItem)?.title || subItem.url }}</a>
                                        </li>
                                    </ul>
                                </li>
                                <!-- User Menu / Login -->
                                <li v-if="isAuthenticated">
                                    <a href="#" @click.prevent="router.push('/my-account')">{{ t.myAccount }}</a>
                                </li>
                                <li v-if="isAuthenticated">
                                    <a href="#" @click.prevent="handleLogout">{{ t.logout }}</a>
                                </li>
                                <li v-else>
                                    <a href="#" @click.prevent="router.push('/login')">{{ t.login }}</a>
                                </li>
                            </ul>
                        </div>
                        <div class="main-menu__right">                           
                            <div class="main-menu__search-cart-box">
                                <div class="main-menu__search-cart-box">
                                    <div class="main-menu__language-box dropdown" :class="{ 'show': isDropdownOpen }">
                                        <a href="#" class="main-menu__language dropdown-toggle" @click.prevent="toggleDropdown">
                                            <img :src="`https://flagcdn.com/w20/${currentLanguageInfo.flag}.png`" :alt="currentLanguageInfo.name" class="current-flag">
                                            <span>{{ currentLanguageInfo.name }}</span>
                                        </a>
                                        <ul class="dropdown-menu shadow-box" :class="{ 'show': isDropdownOpen }">
                                            <li v-for="lang in languages" :key="lang.code" :class="{ 'active': currentLanguage === lang.fullCode }">
                                                <a href="#" @click.prevent="changeLanguage(lang.code)">
                                                    <img :src="`https://flagcdn.com/w20/${lang.flag}.png`" :alt="lang.name">
                                                    {{ lang.name }}
                                                    <i v-if="currentLanguage === lang.fullCode" class="fas fa-check"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="main-menu__nav-sidebar-icon">
                                <a class="navSidebar-button" href="#" @click.prevent="toggleContactPopup">
                                    <span class="icon-dots-menu-one"></span>
                                    <span class="icon-dots-menu-two"></span>
                                    <span class="icon-dots-menu-three"></span>
                                </a>
                            </div>
                            <div class="main-menu__btn-box">
                                <a href="#" @click.prevent="router.push('/request-quote')" class="thm-btn">Request Quote<span><i
                                            class="icon-right-arrow"></i></span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
    </header>
    <!-- Sticky Header -->
    <div class="stricky-header stricked-menu main-menu">
        <div class="sticky-header__content"></div><!-- /.sticky-header__content -->
    </div>
    <!-- /.stricky-header -->
     <div class="stricky-header stricked-menu main-menu">
            <div class="sticky-header__content">
                <div class="main-menu__wrapper">
                    <div class="main-menu__wrapper-inner">
                        <div class="main-menu__left">
                            <div class="main-menu__logo">
                                <a href="#"><img :src="getLogoUrl || '/assets/img/west-east-freight-meta.png'" width="170" alt="Logo"></a>
                            </div>
                        </div>
                        <div class="main-menu__main-menu-box">
                            <a href="#" class="mobile-nav__toggler" @click.prevent="toggleMobileNav"><i class="fa fa-bars"></i></a>
                            <ul class="main-menu__list" :class="{ 'myanmar-active': currentLanguage === 'mm', 'thai-active': currentLanguage === 'th' }">
                                <li v-for="item in menuItems" :key="item.id" :class="{ 'dropdown': item.type === 'Group' && item.sub_menu && item.sub_menu.length > 0 }">
                                    <a :href="item.url || '#'">{{ getMenuItemTranslation(item)?.title || item.url }}</a>
                                    <ul v-if="item.type === 'Group' && item.sub_menu && item.sub_menu.length > 0" class="shadow-box">
                                        <li v-for="subItem in item.sub_menu" :key="subItem.id">
                                            <a :href="subItem.url || '#'">{{ getMenuItemTranslation(subItem)?.title || subItem.url }}</a>
                                        </li>
                                    </ul>
                                </li>
                                <!-- User Menu / Login (Sticky) -->
                                <li v-if="isAuthenticated">
                                    <a href="#" @click.prevent="router.push('/my-account')">{{ t.myAccount }}</a>
                                </li>
                                <li v-if="isAuthenticated">
                                    <a href="#" @click.prevent="handleLogout">{{ t.logout }}</a>
                                </li>
                                <li v-else>
                                    <a href="#" @click.prevent="router.push('/login')">{{ t.login }}</a>
                                </li>
                            </ul>
                        </div>
                        <div class="main-menu__right">
                            <div class="main-menu__call">
                                <div class="main-menu__call-icon">
                                    <i class="icon-phone-call"></i>
                                </div>
                                <div class="main-menu__call-content">
                                    <p class="main-menu__call-sub-title">Call Anytime</p>
                                    <h5 class="main-menu__call-number"><a href="tel:9288006780">+92 ( 8800 ) -
                                            6780</a></h5>
                                </div>
                            </div>
                            <div class="main-menu__search-cart-box">
                                <div class="main-menu__language-box dropdown" :class="{ 'show': isDropdownOpenSticky }">
                                    <a href="#" class="main-menu__language dropdown-toggle" @click.prevent="toggleDropdownSticky">
                                        <img :src="`https://flagcdn.com/w20/${currentLanguageInfo.flag}.png`" :alt="currentLanguageInfo.name" class="current-flag">
                                        <span>{{ currentLanguageInfo.name }}</span>
                                    </a>
                                    <ul class="dropdown-menu shadow-box" :class="{ 'show': isDropdownOpenSticky }">
                                        <li v-for="lang in languages" :key="lang.code" :class="{ 'active': currentLanguage === lang.fullCode }">
                                            <a href="#" @click.prevent="changeLanguage(lang.code)">
                                                <img :src="`https://flagcdn.com/w20/${lang.flag}.png`" :alt="lang.name">
                                                {{ lang.name }}
                                                <i v-if="currentLanguage === lang.fullCode" class="fas fa-check"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="main-menu__nav-sidebar-icon">
                                <a class="navSidebar-button" href="#" @click.prevent="toggleContactPopup">
                                    <span class="icon-dots-menu-one"></span>
                                    <span class="icon-dots-menu-two"></span>
                                    <span class="icon-dots-menu-three"></span>
                                </a>
                            </div>
                            <div class="main-menu__btn-box">
                                <a href="#" @click.prevent="router.push('/request-quote')" class="thm-btn">Request Quote<span><i
                                            class="icon-right-arrow"></i></span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</template>
</template>

<style scoped>
.main-menu__language-box {
    position: relative;
}

.main-menu__language {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 15px;
    color: var(--tanspot-black);
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
}

.main-menu__language:hover {
    color: var(--tanspot-base);
}

.main-menu__language .current-flag {
    width: 20px;
    height: auto;
}

.main-menu__language i.fa-chevron-down {
    font-size: 12px;
    margin-left: 4px;
}

.main-menu__language-box .dropdown-menu {
    min-width: 180px;
    padding: 10px 0;
    margin-top: 10px;
    border: 1px solid #e5e5e5;
    border-radius: 4px;
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background: #fff;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.main-menu__language-box .dropdown-menu.show {
    display: block;
}

.main-menu__language-box .dropdown-menu li {
    position: relative;
    list-style: none;
}

.main-menu__language-box .dropdown-menu li.active a {
    background-color: #f5f5f5;
    font-weight: 600;
}

.main-menu__language-box .dropdown-menu li a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 20px;
    color: var(--tanspot-black);
    transition: all 0.3s ease;
    position: relative;
    text-decoration: none;
}

.main-menu__language-box .dropdown-menu li a:hover {
    background-color: var(--tanspot-base);
    color: #fff;
}

.main-menu__language-box .dropdown-menu li a img {
    width: 20px;
    height: auto;
}

.main-menu__language-box .dropdown-menu li a .fa-check {
    margin-left: auto;
    font-size: 14px;
    color: var(--tanspot-base);
}

.main-menu__language-box .dropdown-menu li a:hover .fa-check {
    color: #fff;
}

/* Myanmar language specific styling */
.myanmar-text {
    font-size: 0.85em;
}

.main-menu__list a {
    transition: font-size 0.3s ease;
}

.main-menu__list.myanmar-active a {
    font-size: 0.95em;
}

.main-menu__list.thai-active a {
    font-size: 0.95em;
}

/* Global Myanmar font size */
body.myanmar-lang {
    font-size: 15px;
}

body.myanmar-lang h1,
body.myanmar-lang h2,
body.myanmar-lang h3,
body.myanmar-lang h4,
body.myanmar-lang h5,
body.myanmar-lang h6 {
    font-size: 0.95em;
}

body.myanmar-lang p,
body.myanmar-lang span,
body.myanmar-lang a,
body.myanmar-lang li,
body.myanmar-lang div {
    font-size: 0.95em;
}

body.myanmar-lang .main-menu__call-number,
body.myanmar-lang .main-menu__top-text,
body.myanmar-lang .main-menu__call-sub-title {
    font-size: 0.9em;
}

/* Global Thai font size */
body.thai-lang {
    font-size: 15px;
}

body.thai-lang h1,
body.thai-lang h2,
body.thai-lang h3,
body.thai-lang h4,
body.thai-lang h5,
body.thai-lang h6 {
    font-size: 1em;
}

body.thai-lang p,
body.thai-lang span,
body.thai-lang a,
body.thai-lang li,
body.thai-lang div {
    font-size: 1em;
}


body.thai-lang .main-menu__call-number,
body.thai-lang .main-menu__top-text,
body.thai-lang .main-menu__call-sub-title {
    font-size: 0.95em;
}

/* Contact Popup Sidebar */
.contact-popup-sidebar {
    position: fixed;
    top: 0;
    right: -400px;
    width: 400px;
    height: 100vh;
    z-index: 9999;
    transition: right 0.4s ease;
}

.contact-popup-sidebar.active {
    right: 0;
}

.contact-popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    opacity: 0;
    visibility: hidden;
    transition: all 0.4s ease;
    z-index: -1;
}

.contact-popup-sidebar.active .contact-popup-overlay {
    opacity: 1;
    visibility: visible;
}

.contact-popup-content {
    position: relative;
    width: 100%;
    height: 100%;
    background: #fff;
    box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
    overflow-y: auto;
    z-index: 1;
}

.contact-popup-close {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 40px;
    height: 40px;
    border: none;
    background: var(--tanspot-base);
    color: #fff;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    z-index: 10;
}

.contact-popup-close:hover {
    background: var(--tanspot-black);
    transform: rotate(90deg);
}

.contact-popup-header {
    padding: 40px 30px 20px;
    border-bottom: 1px solid #e5e5e5;
}

.contact-popup-header h3 {
    font-size: 28px;
    font-weight: 700;
    color: var(--tanspot-black);
    margin-bottom: 10px;
}

.contact-popup-header p {
    color: #666;
    margin: 0;
}

.contact-popup-body {
    padding: 30px;
}

.contact-info-item {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 1px solid #f0f0f0;
}

.contact-info-item:last-of-type {
    border-bottom: none;
}

.contact-info-item .icon {
    width: 50px;
    height: 50px;
    background: var(--tanspot-base);
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 20px;
}

.contact-info-item .content h5 {
    font-size: 16px;
    font-weight: 600;
    color: var(--tanspot-black);
    margin-bottom: 8px;
}

.contact-info-item .content p {
    margin: 0;
    color: #666;
    line-height: 1.6;
}

.contact-info-item .content a {
    color: #666;
    text-decoration: none;
    transition: color 0.3s ease;
}

.contact-info-item .content a:hover {
    color: var(--tanspot-base);
}

.contact-social {
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid #f0f0f0;
}

.contact-social h5 {
    font-size: 16px;
    font-weight: 600;
    color: var(--tanspot-black);
    margin-bottom: 15px;
}

.contact-social .social-links {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.contact-social .social-links a {
    width: 40px;
    height: 40px;
    background: #f5f5f5;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.contact-social .social-links a:hover {
    background: var(--tanspot-base);
    transform: translateY(-3px);
}

.contact-social .social-links a img {
    filter: grayscale(100%);
    transition: filter 0.3s ease;
}

.contact-social .social-links a:hover img {
    filter: grayscale(0%) brightness(0) invert(1);
}

/* Mobile Navigation Integration */
@media (max-width: 991px) {
    .contact-popup-sidebar {
        width: 100%;
        right: -100%;
    }
    
    .contact-popup-content {
        width: 320px;
        margin-left: auto;
    }
}

@media (max-width: 575px) {
    .contact-popup-content {
        width: 280px;
    }
    
    .contact-popup-header {
        padding: 30px 20px 15px;
    }
    
    .contact-popup-header h3 {
        font-size: 24px;
    }
    
    .contact-popup-body {
        padding: 20px;
    }
    
    .contact-info-item .icon {
        width: 45px;
        height: 45px;
        font-size: 18px;
    }
}
</style>
