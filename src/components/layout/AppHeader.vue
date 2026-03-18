<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { Menu, X, Phone, Mail } from 'lucide-vue-next'
import { useMenuStore } from '@/stores/useMenuStore'
import { env } from '@/lib/env'
import AppButton from '@/components/ui/AppButton.vue'

const menuStore = useMenuStore()

const navigation = [
  { name: 'Главная', href: '/' },
  { name: 'Каталог', href: '/catalog' },
  { name: 'Контакты', href: '/contact' },
  { name: 'FAQ', href: '/faq' },
]
</script>

<template>
  <header class="glass-effect shadow-modern sticky top-0 z-50 border-b border-surface-100">
    <div class="container-custom">
      <div class="flex items-center justify-between h-20">
        <!-- Logo -->
        <RouterLink to="/" class="flex items-center space-x-3 group">
          <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-accent-500 rounded-xl flex items-center justify-center shadow-glow group-hover:shadow-glow-lg transition-all duration-300">
            <span class="text-white font-bold text-lg">BW</span>
          </div>
          <span class="font-display text-xl font-bold text-text-primary group-hover:text-gradient transition-all duration-300">{{ env.siteName }}</span>
        </RouterLink>

        <!-- Desktop Navigation -->
          <nav class="hidden md:flex items-center space-x-8">
          <RouterLink
            v-for="item in navigation"
            :key="item.name"
            :to="item.href"
            class="nav-link text-text-secondary hover:text-primary-500 transition-colors duration-300 font-medium"
          >
            {{ item.name }}
          </RouterLink>
        </nav>

        <!-- Contact Info -->
        <div class="hidden lg:flex items-center space-x-6">
          <a
            :href="`tel:${env.contactPhone}`"
            class="flex items-center space-x-2 text-text-secondary hover:text-primary-500 transition-colors group"
          >
            <div class="w-8 h-8 bg-primary-50 rounded-lg flex items-center justify-center group-hover:bg-primary-100 transition-colors">
              <Phone class="w-4 h-4 text-primary-500" />
            </div>
            <span class="text-sm font-medium">{{ env.contactPhone }}</span>
          </a>
          <a
            :href="`mailto:${env.contactEmail}`"
            class="flex items-center space-x-2 text-text-secondary hover:text-primary-500 transition-colors group"
          >
            <div class="w-8 h-8 bg-primary-50 rounded-lg flex items-center justify-center group-hover:bg-primary-100 transition-colors">
              <Mail class="w-4 h-4 text-primary-500" />
            </div>
            <span class="text-sm font-medium hidden xl:inline">{{ env.contactEmail }}</span>
          </a>
        </div>

        <!-- Mobile menu button -->
        <AppButton
          variant="ghost"
          size="icon"
          class="md:hidden hover:bg-surface-100"
          @click="menuStore.toggleMenu"
        >
          <X v-if="menuStore.isMenuOpen" class="w-6 h-6 text-text-primary" />
          <Menu v-else class="w-6 h-6 text-text-primary" />
        </AppButton>
      </div>

      <!-- Mobile Navigation -->
      <div v-if="menuStore.isMenuOpen" class="md:hidden pb-4">
        <div class="px-2 pt-2 pb-3 space-y-1 bg-white/95 backdrop-blur-xl rounded-2xl mt-2 border border-surface-100 shadow-modern-lg">
          <RouterLink
            v-for="item in navigation"
            :key="item.name"
            :to="item.href"
            class="block px-4 py-3 text-text-secondary hover:text-primary-500 hover:bg-primary-50 rounded-xl transition-colors duration-300 font-medium"
            @click="menuStore.closeMenu"
          >
            {{ item.name }}
          </RouterLink>
          <div class="pt-4 pb-2 border-t border-surface-200">
            <a
              :href="`tel:${env.contactPhone}`"
              class="flex items-center space-x-3 px-4 py-3 text-text-secondary hover:text-primary-500 transition-colors"
            >
              <div class="w-8 h-8 bg-primary-50 rounded-lg flex items-center justify-center">
                <Phone class="w-4 h-4 text-primary-500" />
              </div>
              <span class="font-medium">{{ env.contactPhone }}</span>
            </a>
            <a
              :href="`mailto:${env.contactEmail}`"
              class="flex items-center space-x-3 px-4 py-3 text-text-secondary hover:text-primary-500 transition-colors"
            >
              <div class="w-8 h-8 bg-primary-50 rounded-lg flex items-center justify-center">
                <Mail class="w-4 h-4 text-primary-500" />
              </div>
              <span class="font-medium">{{ env.contactEmail }}</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>
