<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminLogin from '@/components/admin/AdminLogin.vue'
import AdminDashboard from '@/components/admin/AdminDashboard.vue'
import AdminCategories from '@/components/admin/AdminCategories.vue'
import AdminProducts from '@/components/admin/AdminProducts.vue'
import { useAdminStore } from '@/stores/useAdminStore'

const route = useRoute()
const router = useRouter()
const adminStore = useAdminStore()

// Проверяем, авторизован ли пользователь
const isAuthenticated = computed(() => adminStore.isAuthenticated)

// Роутинг для админ-панели
const currentView = computed(() => {
  const path = route.path
  if (path === '/admin') return 'login'
  if (path === '/admin/dashboard') return 'dashboard'
  if (path === '/admin/categories') return 'categories'
  if (path === '/admin/products') return 'products'
  return 'login'
})

// Защита роутов
const handleRouteChange = () => {
  if (!isAuthenticated.value && currentView.value !== 'login') {
    router.push('/admin')
  }
}

// Слушаем изменения маршрута
router.afterEach(handleRouteChange)
</script>

<template>
  <div>
    <AdminLogin v-if="currentView === 'login'" />
    <AdminDashboard v-else-if="currentView === 'dashboard'" />
    <AdminCategories v-else-if="currentView === 'categories'" />
    <AdminProducts v-else-if="currentView === 'products'" />
  </div>
</template>
