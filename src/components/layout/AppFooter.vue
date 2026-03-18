<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { Phone, Mail, MapPin, Instagram, Send, MessageCircle } from 'lucide-vue-next'
import { env } from '@/lib/env'

const currentYear = new Date().getFullYear()
const version = "v1.0.0"

const navigation = [
  { name: 'Главная', href: '/' },
  { name: 'Каталог', href: '/catalog' },
  { name: 'Контакты', href: '/contact' },
  { name: 'FAQ', href: '/faq' },
]

const socialLinks = [
  {
    name: 'Instagram',
    href: env.instagram,
    icon: Instagram,
  },
  {
    name: 'Telegram',
    href: env.telegram,
    icon: Send,
  },
  {
    name: 'VK',
    href: env.vk,
    icon: MessageCircle,
  },
]
</script>

<template>
  <footer class="bg-gradient-to-br from-rose-light to-lavender/30 border-t border-lavender/20">
    <div class="container-custom">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 py-12">
        <!-- О компании -->
        <div class="space-y-4">
          <div class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gradient-to-r from-dusty-rose to-pastel-purple rounded-full flex items-center justify-center">
              <span class="text-white font-bold text-sm">BW</span>
            </div>
            <span class="font-playfair text-xl text-text-rose">MarketBW</span>
          </div>
          <p class="text-text-medium text-sm leading-relaxed">
            Создаю уникальные украшения из бисера ручной работы с любовью и вниманием к каждой детали.
          </p>
        </div>

        <!-- Навигация -->
        <div class="space-y-4">
          <h3 class="font-playfair text-lg text-text-rose">Навигация</h3>
          <ul class="space-y-2">
            <li v-for="item in navigation" :key="item.name">
              <RouterLink
                :to="item.href"
                class="text-text-medium hover:text-text-rose transition-colors duration-300 text-sm"
              >
                {{ item.name }}
              </RouterLink>
            </li>
          </ul>
        </div>

        <!-- Контакты -->
        <div class="space-y-4">
          <h3 class="font-playfair text-lg text-text-rose">Контакты</h3>
          <div class="space-y-3">
            <a
              :href="`tel:${env.contactPhone}`"
              class="flex items-center space-x-2 text-text-medium hover:text-text-rose transition-colors text-sm"
            >
              <Phone class="w-4 h-4" />
              <span>{{ env.contactPhone }}</span>
            </a>
            <a
              :href="`mailto:${env.contactEmail}`"
              class="flex items-center space-x-2 text-text-medium hover:text-text-rose transition-colors text-sm"
            >
              <Mail class="w-4 h-4" />
              <span>{{ env.contactEmail }}</span>
            </a>
          </div>
        </div>

        <!-- Социальные сети -->
        <div class="space-y-4">
          <h3 class="font-playfair text-lg text-text-rose">Социальные сети</h3>
          <div class="flex space-x-3">
            <a
              v-for="social in socialLinks"
              :key="social.name"
              :href="social.href"
              target="_blank"
              rel="noopener noreferrer"
              class="w-10 h-10 bg-white/80 rounded-full flex items-center justify-center hover:bg-dusty-rose hover:text-white transition-all duration-300 shadow-soft hover:shadow-soft-lg"
              :aria-label="social.name"
            >
              <component :is="social.icon" class="w-5 h-5" />
            </a>
          </div>
          <p class="text-text-medium text-sm">
            Подписывайтесь, чтобы первыми узнавать о новинках и акциях!
          </p>
        </div>
      </div>

      <!-- Нижняя часть футера -->
      <div class="border-t border-lavender/20 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
          <p class="text-text-medium text-sm">
            © {{ currentYear }} MarketBW. Все права защищены.
          </p>
          <div class="flex flex-col md:flex-row items-center space-y-2 md:space-y-0 md:space-x-4">
            <p class="text-text-medium text-sm">
              Создано с ❤️ для ценителей бисерного искусства
            </p>
            <p class="text-text-medium text-xs opacity-75">
              Версия: {{ version }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>
