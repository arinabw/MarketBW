<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminStore } from '@/stores/useAdminStore'
import { ArrowLeft, Package, Tag, Users, LogOut } from 'lucide-vue-next'
import AppButton from '@/components/ui/AppButton.vue'

const router = useRouter()
const adminStore = useAdminStore()

onMounted(async () => {
  await adminStore.loadCategories()
  await adminStore.loadProducts()
})

const handleLogout = () => {
  adminStore.logout()
  router.push('/admin')
}
</script>

<template>
  <div class="min-h-screen" style="background-color: #FFD9D9">
    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-xl shadow-soft border border-white/20">
      <div class="container-custom">
        <div class="flex items-center justify-between py-4">
          <div class="flex items-center gap-4">
            <button @click="router.push('/')" class="flex items-center gap-2 text-sm" style="color: #4D0011">
              <ArrowLeft class="w-4 h-4" />
              На сайт
            </button>
            <h1 class="text-2xl font-bold" style="color: #4D0011">Панель управления</h1>
          </div>
          <button @click="handleLogout" class="flex items-center gap-2 text-sm" style="color: #4D0011">
            <LogOut class="w-4 h-4" />
            Выйти
          </button>
        </div>
      </div>
    </header>

    <!-- Content -->
    <main class="container-custom py-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-soft p-6 border border-white/20">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm" style="color: #4D0011">Всего товаров</p>
              <p class="text-3xl font-bold mt-1">{{ adminStore.products.length }}</p>
            </div>
            <div class="p-3 rounded-full" style="background-color: #FFD9D9">
              <Package class="w-6 h-6" style="color: #BD7880" />
            </div>
          </div>
        </div>

        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-soft p-6 border border-white/20">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm" style="color: #4D0011">Категорий</p>
              <p class="text-3xl font-bold mt-1">{{ adminStore.categories.length }}</p>
            </div>
            <div class="p-3 rounded-full" style="background-color: #FFD9D9">
              <Tag class="w-6 h-6" style="color: #BD7880" />
            </div>
          </div>
        </div>

        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-soft p-6 border border-white/20">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm" style="color: #4D0011">Пользователей</p>
              <p class="text-3xl font-bold mt-1">1</p>
            </div>
            <div class="p-3 rounded-full" style="background-color: #FFD9D9">
              <Users class="w-6 h-6" style="color: #BD7880" />
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-soft p-6 border border-white/20">
          <h2 class="text-lg font-bold mb-4" style="color: #4D0011">Быстрые действия</h2>
          <div class="space-y-3">
            <button
              @click="router.push('/admin/categories')"
              class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-surface-50 transition-colors"
              style="color: #4D0011"
            >
              <Tag class="w-5 h-5" />
              <span>Управление категориями</span>
            </button>
            <button
              @click="router.push('/admin/products')"
              class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-surface-50 transition-colors"
              style="color: #4D0011"
            >
              <Package class="w-5 h-5" />
              <span>Управление товарами</span>
            </button>
          </div>
        </div>

        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-soft p-6 border border-white/20">
          <h2 class="text-lg font-bold mb-4" style="color: #4D0011">Информация</h2>
          <div class="space-y-3 text-sm" style="color: #4D0011">
            <p>База данных: SQLite</p>
            <p>Пароль админа: admin123</p>
            <p>Данные хранятся в папке: data/</p>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
