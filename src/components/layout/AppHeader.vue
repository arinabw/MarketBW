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
  <header class="bg-white/80 backdrop-blur-md shadow-soft sticky top-0 z-50">
    <div class="container-custom">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <RouterLink to="/" class="flex items-center space-x-2">
          <div class="w-8 h-8 bg-gradient-to-r from-dusty-rose to-pastel-purple rounded-full flex items-center justify-center">
            <span class="text-white font-bold text-sm">BW</span>
          </div>
          <span class="font-playfair text-xl text-text-rose">{{ env.siteName }}</span>
        </RouterLink>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center space-x-8">
          <RouterLink
            v-for="item in navigation"
            :key="item.name"
            :to="item.href"
            class="nav-link text-text-dark hover:text-text-rose transition-colors duration-300"
          >
            {{ item.name }}
          </RouterLink>
        </nav>

        <!-- Contact Info -->
        <div class="hidden lg:flex items-center space-x-4">
          <a
            :href="`tel:${env.contactPhone}`"
            class="flex items-center space-x-1 text-text-medium hover:text-text-rose transition-colors"
          >
            <Phone class="w-4 h-4" />
            <span class="text-sm">{{ env.contactPhone }}</span>
          </a>
          <a
            :href="`mailto:${env.contactEmail}`"
            class="flex items-center space-x-1 text-text-medium hover:text-text-rose transition-colors"
          >
            <Mail class="w-4 h-4" />
            <span class="text-sm hidden xl:inline">{{ env.contactEmail }}</span>
          </a>
        </div>

        <!-- Mobile menu button -->
        <AppButton
          variant="ghost"
          size="icon"
          class="md:hidden"
          @click="menuStore.toggleMenu"
        >
          <X v-if="menuStore.isMenuOpen" class="w-5 h-5" />
          <Menu v-else class="w-5 h-5" />
        </AppButton>
      </div>

      <!-- Mobile Navigation -->
      <div v-if="menuStore.isMenuOpen" class="md:hidden">
        <div class="px-2 pt-2 pb-3 space-y-1 bg-white/95 backdrop-blur-md rounded-lg mt-2">
          <RouterLink
            v-for="item in navigation"
            :key="item.name"
            :to="item.href"
            class="block px-3 py-2 text-text-dark hover:text-text-rose hover:bg-rose-light rounded-md transition-colors duration-300"
            @click="menuStore.closeMenu"
          >
            {{ item.name }}
          </RouterLink>
          <div class="pt-4 pb-2 border-t border-lavender/20">
            <a
              :href="`tel:${env.contactPhone}`"
              class="flex items-center space-x-2 px-3 py-2 text-text-medium hover:text-text-rose transition-colors"
            >
              <Phone class="w-4 h-4" />
              <span>{{ env.contactPhone }}</span>
            </a>
            <a
              :href="`mailto:${env.contactEmail}`"
              class="flex items-center space-x-2 px-3 py-2 text-text-medium hover:text-text-rose transition-colors"
            >
              <Mail class="w-4 h-4" />
              <span>{{ env.contactEmail }}</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>
