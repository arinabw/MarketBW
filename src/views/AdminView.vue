<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AdminLogin from '@/components/admin/AdminLogin.vue'
import AdminDashboard from '@/components/admin/AdminDashboard.vue'
import AdminCategories from '@/components/admin/AdminCategories.vue'
import AdminProducts from '@/components/admin/AdminProducts.vue'

const route = useRoute()

// Роутинг для админ-панели (защита /admin/* — в router/index.js beforeEach)
const currentView = computed(() => {
  const path = route.path
  if (path === '/admin') return 'login'
  if (path === '/admin/dashboard') return 'dashboard'
  if (path === '/admin/categories') return 'categories'
  if (path === '/admin/products') return 'products'
  return 'login'
})
</script>

<template>
  <div>
    <AdminLogin v-if="currentView === 'login'" />
    <AdminDashboard v-else-if="currentView === 'dashboard'" />
    <AdminCategories v-else-if="currentView === 'categories'" />
    <AdminProducts v-else-if="currentView === 'products'" />
  </div>
</template>
