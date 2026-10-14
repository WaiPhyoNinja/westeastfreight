import { createRouter, createWebHistory } from 'vue-router'
import Index from '../components/index.vue'
import Login from '../components/auth/Login.vue'
import MyAccount from '../components/MyAccount.vue'
import ResetPassword from '../components/ResetPassword.vue'
import RequestQuote from '../components/RequestQuote.vue'
import NotFound from '../components/NotFound.vue'
import { useLanguage } from '@/composables/useLanguage'
import { useAuth } from '@/composables/useAuth'

const routes = [
  { path: '/my-orders/:id(\\d+)', name: 'QuoteDetails', component: () => import('../components/QuoteDetails.vue'), meta: { requiresAuth: true } },
  {
    path: '/',
    name: 'Home',
    component: Index
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresGuest: true }
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: ResetPassword,
    meta: { requiresGuest: true }
  },
  {
    path: '/my-account',
    name: 'MyAccount',
    component: MyAccount,
    meta: { requiresAuth: true }
  },
  {
    path: '/request-quote',
    name: 'RequestQuote',
    component: RequestQuote
  },
  {
    path: '/my-orders',
    name: 'MyOrders',
    component: MyAccount,
    meta: { requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'Profile',
    component: MyAccount,
    meta: { requiresAuth: true }
  },
  {
    path: '/orders',
    name: 'Orders',
    component: MyAccount,
    meta: { requiresAuth: true }
  },
  {
    path: '/services/:slug',
    name: 'ServiceDetails',
    component: Index
  },
  {
    path: '/solutions/:slug',
    name: 'SolutionDetails',
    component: Index
  },
  {
    path: '/:slug',
    name: 'DynamicPage',
    component: Index
  },
  {
    path: '/:parent/:slug',
    name: 'NestedPage',
    component: Index
  },
  {
    path: '/:parent/:child/:slug',
    name: 'DeepNestedPage',
    component: Index
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFound
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

// Navigation Guards
router.beforeEach(async (to, from, next) => {
  const { setLanguage } = useLanguage()
  const locale = to.path.split('/')[1]
  if (['en', 'my', 'th'].includes(locale)) setLanguage(locale === 'my' ? 'mm' : locale)
  const { isAuthenticated, checkAuth } = useAuth()
  
  // Check authentication status
  await checkAuth()
  
  // Redirect to home if logged in user tries to access login page
  if (isAuthenticated.value && (to.meta.requiresGuest || to.path === '/')) {
    next('/my-orders')
  }
  // Redirect to login if guest tries to access protected route
  else if (to.meta.requiresAuth && !isAuthenticated.value) {
    next('/login')
  }
  // Proceed normally
  else {
    next()
  }
})

export default router