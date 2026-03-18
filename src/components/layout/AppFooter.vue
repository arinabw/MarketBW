<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { Phone, Mail, MapPin, Instagram, Send, MessageCircle } from 'lucide-vue-next'
import { env } from '@/lib/env'

const currentYear = new Date().getFullYear()
const version = "v1.0.1"

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
  <footer class="bg-gradient-to-br from-surface-50 to-white border-t border-surface-200">
    <div class="container-custom">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 py-16">
        <!-- О компании -->
        <div class="space-y-4">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-accent-500 rounded-xl flex items-center justify-center shadow-glow">
              <span class="text-white font-bold text-lg">BW</span>
            </div>
            <span class="font-display text-xl font-bold text-text-primary">MarketBW</span>
          </div>
          <p class="text-text-secondary text-sm leading-relaxed">
            Создаю уникальные украшения из бисера ручной работы с любовью и вниманием к каждой детали.
          </p>
        </div>

        <!-- Навигация -->
        <div class="space-y-4">
          <h3 class="font-display text-lg font-bold text-text-primary">Навигация</h3>
          <ul class="space-y-3">
            <li v-for="item in navigation" :key="item.name">
              <RouterLink
                :to="item.href"
                class="text-text-secondary hover:text-primary-500 transition-colors duration-300 text-sm font-medium"
              >
                {{ item.name }}
              </RouterLink>
            </li>
          </ul>
        </div>

        <!-- Контакты -->
        <div class="space-y-4">
          <h3 class="font-display text-lg font-bold text-text-primary">Контакты</h3>
          <div class="space-y-3">
            <a
              :href="`tel:${env.contactPhone}`"
              class="flex items-center space-x-3 text-text-secondary hover:text-primary-500 transition-colors group"
            >
              <div class="w-8 h-8 bg-primary-50 rounded-lg flex items-center justify-center group-hover:bg-primary-100 transition-colors">
                <Phone class="w-4 h-4 text-primary-500" />
              </div>
              <span class="text-sm font-medium">{{ env.contactPhone }}</span>
            </a>
            <a
              :href="`mailto:${env.contactEmail}`"
              class="flex items-center space-x-3 text-text-secondary hover:text-primary-500 transition-colors group"
            >
              <div class="w-8 h-8 bg-primary-50 rounded-lg flex items-center justify-center group-hover:bg-primary-100 transition-colors">
                <Mail class="w-4 h-4 text-primary-500" />
              </div>
              <span class="text-sm font-medium">{{ env.contactEmail }}</span>
            </a>
          </div>
        </div>

        <!-- Социальные сети -->
        <div class="space-y-4">
          <h3 class="font-display text-lg font-bold text-text-primary">Социальные сети</h3>
          <div class="flex space-x-3">
            <a
              v-for="social in socialLinks"
              :key="social.name"
              :href="social.href"
              target="_blank"
              rel="noopener noreferrer"
              class="w-10 h-10 bg-white border-2 border-surface-200 rounded-xl flex items-center justify-center hover:bg-gradient-to-br hover:from-primary-500 hover:to-accent-500 hover:border-transparent hover:text-white transition-all duration-300 shadow-modern hover:shadow-glow"
              :aria-label="social.name"
            >
              <component :is="social.icon" class="w-5 h-5 text-text-secondary group-hover:text-white" />
            </a>
          </div>
          <p class="text-text-secondary text-sm">
            Подписывайтесь, чтобы первыми узнавать о новинках и акциях!
          </p>
        </div>
      </div>

      <!-- Нижняя часть футера -->
      <div class="border-t border-surface-200 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
          <p class="text-text-secondary text-sm">
            © {{ currentYear }} MarketBW. Все права защищены.
          </p>
          <div class="flex flex-col md:flex-row items-center space-y-2 md:space-y-0 md:space-x-4">
            <p class="text-text-secondary text-sm">
              Создано с ❤️ для ценителей бисерного искусства
            </p>
            <p class="text-text-tertiary text-xs">
              Версия: {{ version }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>
