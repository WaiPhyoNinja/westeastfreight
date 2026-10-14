<script setup>
import { computed, ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useLanguage } from '@/composables/useLanguage'
import { useAuthTranslation } from '@/locales/auth'
import { quoteTranslations } from '@/locales/quote'
import { apiRequest } from '@/services/http'
import Swal from 'sweetalert2'

const route = useRoute()
const router = useRouter()
const { user, logout } = useAuth()
const { currentLanguage, setLanguage, initLanguage } = useLanguage()
const t = computed(() => useAuthTranslation(currentLanguage.value))
const quoteLabel = computed(() => (quoteTranslations[currentLanguage.value] || quoteTranslations['en-US']).pageTitle)
const customerMenus = ref([])
const menuLabel = item => item.translations?.find(value => value.languages_code === currentLanguage.value)?.title || item.translations?.find(value => value.languages_code === 'en-US')?.title || ''
const safeMenuUrl = url => typeof url === 'string' && (/^\/(?!\/)/.test(url) || /^https?:\/\//i.test(url)) ? url : null
const menuItems = group => (group?.items || []).filter(item => safeMenuUrl(item.url))
const topMenu = ref(null)
const closeTopMenus = () => topMenu.value?.querySelectorAll('details[open]').forEach(item => item.open = false)
const onOutsideClick = event => { if (!topMenu.value?.contains(event.target)) closeTopMenus() }
const open = ref(false)
const mobile = ref(false)
const desktopCollapsed = ref(false)
const toggle = ref(null)
const sidebar = ref(null)
const logo = ref('/assets/img/logo.png')
const busy = ref(false)
const links = computed(() => [
    { to: '/my-orders', label: t.value.myRequestQuote, icon: 'fa-file-invoice', active: ['/my-orders', '/orders'].includes(route.path) || (route.path === '/my-account' && route.query.tab === 'orders') },
    { to: '/profile', label: t.value.profileInfo, icon: 'fa-user', active: route.path === '/profile' || (route.path === '/my-account' && route.query.tab !== 'password' && route.query.tab !== 'orders') },
    { to: '/my-account?tab=password', label: t.value.changePassword, icon: 'fa-lock', active: route.path === '/my-account' && route.query.tab === 'password' },
])
const close = async () => { open.value = false; await nextTick(); toggle.value?.focus() }
const toggleMenu = async () => {
    closeTopMenus()
    if (!mobile.value) { desktopCollapsed.value = !desktopCollapsed.value; return }
    open.value = !open.value
    if (open.value) { await nextTick(); sidebar.value?.querySelector('a,button')?.focus() }
}
const onKey = event => {
    if (event.key === 'Escape') {
        const active = topMenu.value?.querySelector('details[open]')
        if (active) { active.open = false; active.querySelector('summary')?.focus() }
    }
    if (!mobile.value || !open.value) return
    if (event.key === 'Escape') { event.preventDefault(); close(); return }
    if (event.key !== 'Tab') return
    const elements = [...sidebar.value.querySelectorAll('a,button,select')].filter(el => !el.disabled)
    const first = elements[0], last = elements.at(-1)
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus() }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus() }
}
const resize = () => { mobile.value = window.innerWidth < 992; if (!mobile.value) open.value = false }
watch(() => route.fullPath, () => { open.value = false; closeTopMenus() })
watch(currentLanguage, lang => {
    document.body.classList.toggle('myanmar-lang', lang === 'mm')
    document.body.classList.toggle('thai-lang', lang === 'th')
}, { immediate: true })
watch(open, value => { document.body.style.overflow = value && mobile.value ? 'hidden' : '' })
let disposed = false
const menusError = ref(false)
const loadHeader = async () => {
    menusError.value = false
    const results = await Promise.allSettled([
        apiRequest('items/menu?filter[menu_id][_eq]=customer_navigation', undefined, 'GET'),
        apiRequest('items/header', undefined, 'GET'),
    ])
    if (disposed) return
    if (results[0].status === 'fulfilled') customerMenus.value = results[0].value.data?.[0]?.items || []
    else menusError.value = true
    if (results[1].status === 'fulfilled') {
        const path = results[1].value.data?.[0]?.logo
        if (path) logo.value = /^(https?:\/\/|\/)/i.test(path) ? path : `/storage/${path}`
    }
}
const groupDescription = group => group.translations?.find(item => item.languages_code === currentLanguage.value)?.description || group.translations?.find(item => item.languages_code === 'en-US')?.description || ''
const navigateMenu = (event, item) => {
    closeTopMenus()
    if (item.target !== '_blank' && item.url.startsWith('/') && !event.ctrlKey && !event.metaKey && !event.shiftKey && !event.altKey && event.button === 0) {
        event.preventDefault()
        router.push(item.url)
    }
}
onMounted(() => {
    initLanguage(); resize()
    window.addEventListener('resize', resize)
    document.addEventListener('keydown', onKey)
    document.addEventListener('click', onOutsideClick)
    loadHeader()
})
onUnmounted(() => { disposed = true; document.removeEventListener('click', onOutsideClick); window.removeEventListener('resize', resize); document.removeEventListener('keydown', onKey); document.body.style.overflow = '' })
const signOut = async () => {
    if (busy.value) return
    busy.value = true
    try { await logout(); await router.push('/') }
    catch (error) { await Swal.fire({ icon: 'error', text: error.message, confirmButtonColor: '#fd5523' }) }
    finally { busy.value = false }
}
</script>

<template>
    <div class="customer-layout" :class="{ 'sidebar-collapsed': desktopCollapsed && !mobile }">
        <header class="customer-topbar" :inert="mobile && open">
            <router-link to="/my-orders" class="customer-brand" aria-label="West East Freight — My Quotes"><img :src="logo" alt="West East Freight" @error="logo = '/assets/img/logo.png'" /></router-link>
            <nav ref="topMenu" class="customer-header-nav" aria-label="Main navigation">
                <details v-for="(group, index) in customerMenus" :key="`${group.menu_id || group.id}-${index}`" name="customer-header-menu" :class="{ 'is-current': menuItems(group).some(item => item.url === route.fullPath) }">
                    <summary><span>{{ menuLabel(group) }}</span> <i class="fas fa-chevron-down" aria-hidden="true"></i></summary>
                    <div class="customer-header-dropdown" :class="{ 'customer-mega-menu': group.feature_image || group.items?.length }">
                        <div class="customer-menu-links">
                            <a v-for="item in menuItems(group)" :key="item.id" :href="safeMenuUrl(item.url)" :target="item.target" rel="noopener noreferrer" @click="navigateMenu($event, item)"><strong>{{ menuLabel(item) }}</strong><i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                        </div>
                        <div v-if="group.feature_image || group.items?.length" class="customer-menu-feature">
                            <strong>{{ menuLabel(group).toUpperCase() }}</strong>
                            <p v-if="groupDescription(group)">{{ groupDescription(group) }}</p>
                            <img :src="group.feature_image || '/assets/img/banner-two-map-1.png'" alt="" @error="group.feature_image = '/assets/img/banner-two-map-1.png'" />
                        </div>
                    </div>
                </details>
                <button v-if="menusError" type="button" class="customer-menu-retry" @click="loadHeader">Reload menus</button>
            </nav>

        </header>
        <div v-if="mobile && open" class="customer-backdrop" @click="close"></div>
        <aside ref="sidebar" id="customer-sidebar" class="customer-sidebar" :class="{ 'is-open': open }" :inert="mobile ? !open : desktopCollapsed" aria-label="Account navigation">
            <button class="customer-close" type="button" @click="close" aria-label="Close account menu">×</button>
            <div class="customer-identity"><div class="customer-avatar" aria-hidden="true">{{ (user?.first_name || user?.name || 'U').slice(0, 1).toUpperCase() }}</div><strong>{{ user?.name || [user?.first_name, user?.last_name].filter(Boolean).join(' ') }}</strong><p>{{ user?.email }}</p></div>
            <nav><router-link v-for="link in links" :key="link.to" :to="link.to" :class="{ active: link.active }" :aria-current="link.active ? 'page' : undefined"><i :class="['fas', link.icon]" aria-hidden="true"></i>{{ link.label }}</router-link></nav>
            <div class="customer-sidebar-bottom">
                <label class="customer-language"><span>Language / ဘာသာစကား</span><select :value="currentLanguage" @change="setLanguage($event.target.value)"><option value="en-US">English</option><option value="mm">မြန်မာ</option><option value="th">ไทย</option></select></label>
                <button type="button" class="customer-logout" @click="signOut" :disabled="busy"><i class="fas fa-sign-out-alt" aria-hidden="true"></i>{{ t.logout }}</button>
            </div>
        </aside>
        <main class="customer-content" :inert="mobile && open"><slot /></main>
    </div>
</template>

<style scoped>
.customer-layout{--header-height:90px;min-height:100vh;background:#f4f5f9;color:#062f3a}
.customer-topbar{position:fixed;inset:0 0 auto;height:var(--header-height);display:flex;align-items:center;gap:28px;background:white;border-top:2px solid #fd5523;box-shadow:0 4px 20px #062f3a09;z-index:1002}
.customer-brand{width:250px;height:88px;display:flex;align-items:center;padding:22px 40px 22px 26px;background:#fd5523;clip-path:polygon(0 0,82% 0,100% 100%,0 100%);flex-shrink:0}
.customer-brand img{width:100%;max-height:46px;object-fit:contain}
.customer-header-nav{display:flex;align-items:center;justify-content:flex-start;flex:1;flex-wrap:nowrap;overflow-x:auto;gap:clamp(14px,2vw,28px);min-width:0;height:100%;padding:0 28px}
.customer-header-nav details{height:100%;flex-shrink:0}
.customer-header-nav summary{position:relative;display:flex;align-items:center;justify-content:center;gap:14px;height:100%;min-width:122px;padding:0 14px;list-style:none;cursor:pointer;font-family:Arial,Helvetica,sans-serif;font-size:14px;font-weight:700;line-height:1.35;letter-spacing:.15px;text-transform:uppercase;color:#34425d}
.customer-header-nav summary span{display:block;max-width:94px;text-wrap:balance;overflow-wrap:anywhere}
.customer-header-nav summary::-webkit-details-marker{display:none}
.customer-header-nav summary i{font-size:11px;flex-shrink:0;transition:transform .15s}
.customer-header-nav details[open] summary,.customer-header-nav .is-current summary{color:#fd5523}
.customer-header-nav details[open] summary::after,.customer-header-nav .is-current summary::after{content:'';position:absolute;bottom:10px;left:18px;right:18px;height:3px;background:#fd5523;border-radius:3px}
.customer-header-nav details[open] summary i{transform:rotate(180deg)}
.customer-header-dropdown{position:fixed;top:calc(var(--header-height) + 10px);left:50%;transform:translateX(-50%);width:min(720px,calc(100vw - 48px));padding:24px;background:#fff;border:1px solid #e9edef;border-radius:16px;box-shadow:0 16px 44px #062f3a24;max-height:calc(100dvh - var(--header-height) - 30px);overflow:auto}
.customer-mega-menu{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:24px}
.customer-menu-links{min-width:0}
.customer-menu-links a{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:18px 14px;border-radius:9px;color:#062f3a;line-height:1.6}
.customer-menu-links strong{font-size:15px;font-weight:600;overflow-wrap:anywhere}
.customer-menu-links a i{font-size:12px;flex-shrink:0;color:#fd5523}
.customer-menu-links a:hover{background:#fff3ed;color:#e84917}
.customer-menu-feature{min-width:0;padding:22px;background:#062f3a;color:#fff;border-radius:12px;display:flex;flex-direction:column;gap:16px}
.customer-menu-feature strong{font-size:16px;line-height:1.5;overflow-wrap:anywhere}
.customer-menu-feature p{font-size:14px;line-height:1.8;color:#d6e2e5;margin:0;overflow-wrap:anywhere}
.customer-menu-feature img{display:block;width:100%;height:170px;object-fit:contain;border-radius:8px;background:#0c3a45;margin-top:auto}
.customer-menu-retry{border:0;border-radius:6px;padding:8px 14px;background:#fff3ed;color:#062f3a}
.customer-sidebar{position:fixed;top:var(--header-height);bottom:0;left:0;width:250px;display:flex;flex-direction:column;background:#062f3a;color:white;z-index:1001;overflow-y:auto}
.customer-identity{padding:28px 22px 24px;border-bottom:1px solid #ffffff20;overflow-wrap:anywhere}
.customer-avatar{width:42px;height:42px;display:grid;place-items:center;background:#ffffff18;border-radius:50%;font-size:20px;margin-bottom:12px}
.customer-identity strong{font-size:15px;line-height:1.6;display:block}
.customer-identity p{font-size:12px;color:#c0cdd1;line-height:1.6;margin:6px 0 0}
.customer-sidebar nav{padding:20px 12px}
.customer-sidebar nav a,.customer-logout{display:flex;align-items:center;gap:12px;padding:14px;color:#e7eef0;font-size:14px;line-height:1.6;border-radius:6px;margin-bottom:6px}
.customer-sidebar nav a i{width:18px;flex-shrink:0}
.customer-sidebar nav a:hover{background:#ffffff18}
.customer-sidebar nav a.active{background:#fd5523;color:white}
.customer-sidebar-bottom{padding:20px 22px;margin-top:auto;border-top:1px solid #ffffff20}
.customer-language{display:block;font-size:12px;color:#c0cdd1;margin-bottom:16px}
.customer-language select{display:block;width:100%;background:#123f4a;border:1px solid #ffffff30;color:white;border-radius:5px;padding:10px;margin-top:8px}
.customer-logout{background:none;border:0;padding:10px 0;width:100%;text-align:left;margin:0}
.customer-logout:disabled{opacity:.5}
.customer-close{display:none}
.customer-content{padding:calc(var(--header-height) + 28px) 28px 36px;margin-left:250px;min-width:0}
.customer-content :deep(.page-wrapper){background:transparent;overflow:visible}
.customer-content :deep(.my-account-section){padding:0}
.customer-content :deep(.my-account-section>.container){max-width:1200px;padding:0}
.customer-content :deep(.my-account-section>.container>.row){margin:0}
.customer-content :deep(.my-account-section>.container>.row>.col-12){padding:0;min-width:0}
.customer-content :deep(.account-content-box){margin-bottom:0;border:1px solid #e6e6e6;border-radius:10px;box-shadow:none;padding:28px}
.customer-content :deep(.freight-heading){padding:0;margin:0 auto 24px;max-width:1200px;background:transparent;border:0}
.customer-content :deep(.freight-heading p){display:none}
.customer-content :deep(.freight-heading h1){color:#062f3a;font-size:26px}
.customer-content :deep(.freight-heading .container){padding:0}
.customer-content :deep(.freight-page){background:white;padding:28px;border:1px solid #e6e6e6;border-radius:10px}
.customer-content :deep(.freight-card .route-grid){grid-template-columns:1fr 1fr}
.customer-content :deep(.route-panel .freight-grid){grid-template-columns:1fr}
.customer-layout :is(a,button,select,summary):focus-visible{outline:3px solid #ffad8e;outline-offset:3px}
@media(max-width:991px){.customer-layout{--header-height:120px}.customer-topbar{display:block}.customer-brand{width:190px;height:64px;padding:14px 36px 14px 20px}.customer-header-nav{height:54px;width:100%;padding:0 12px;gap:6px;justify-content:flex-start;flex-wrap:nowrap;overflow-x:auto}.customer-header-nav details{flex-shrink:0}.customer-header-nav summary{min-width:0;font-size:13px;gap:18px;padding:0 14px}.customer-header-nav summary span{max-width:80px}.customer-header-nav summary i{font-size:12px}.customer-header-nav details[open] summary::after,.customer-header-nav .is-current summary::after{bottom:0;left:14px;right:14px}.customer-header-dropdown{left:12px;transform:none;width:calc(100vw - 24px)}.customer-sidebar{display:none}.customer-content{margin-left:0;padding:calc(var(--header-height) + 20px) 16px 24px}.customer-content :deep(.account-content-box),.customer-content :deep(.freight-page){padding:20px 16px}}
@media(max-width:600px){.customer-mega-menu{grid-template-columns:1fr;gap:16px;padding:16px}.customer-menu-links a{padding:14px 10px}.customer-menu-feature{padding:18px}.customer-menu-feature img{height:135px}.customer-content :deep(.freight-card .route-grid),.customer-content :deep(.freight-grid){grid-template-columns:1fr}}
</style>
