import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '@/views/HomeView.vue'
import { useAdminStore } from '@/stores/useAdminStore'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/catalog',
      name: 'catalog',
      component: () => import('@/views/CatalogView.vue'),
    },
    {
      path: '/product/:id',
      name: 'product',
      component: () => import('@/views/ProductView.vue'),
    },
    {
      path: '/contact',
      name: 'contact',
      component: () => import('@/views/ContactView.vue'),
    },
    {
      path: '/faq',
      name: 'faq',
      component: () => import('@/views/FAQView.vue'),
    },
    {
      path: '/admin',
      name: 'admin',
      component: () => import('@/views/AdminView.vue'),
    },
    {
      path: '/admin/dashboard',
      name: 'admin-dashboard',
      component: () => import('@/views/AdminView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/categories',
      name: 'admin-categories',
      component: () => import('@/views/AdminView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/admin/products',
      name: 'admin-products',
      component: () => import('@/views/AdminView.vue'),
      meta: { requiresAuth: true }
    },
  ],
})

// Защита роутов админки
router.beforeEach((to, from, next) => {
  const adminStore = useAdminStore()
  
  if (to.meta.requiresAuth && !adminStore.isAuthenticated) {
    next('/admin')
  } else {
    next()
  }
})

export default router
