<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { Phone, Mail, MapPin, Instagram, Send, MessageCircle } from 'lucide-vue-next'
import { env } from '@/lib/env'

const currentYear = new Date().getFullYear()
const version = "v1.0.5"

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
  <footer class="bg-gradient-to-br from-silver-900 to-silver-950 text-white border-t border-silver-800">
    <div class="container-custom">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 py-16">
        <!-- О компании -->
        <div class="space-y-4">
          <div class="flex items-center space-x-3">
            <div class="w-12 h-12 gradient-gold rounded-xl flex items-center justify-center shadow-gold">
              <span class="text-white font-bold text-2xl">BW</span>
            </div>
            <span class="font-display text-2xl font-bold">{{ env.siteName }}</span>
          </div>
          <p class="text-silver-300 text-sm leading-relaxed">
            Создаю уникальные украшения из бисера ручной работы с любовью и вниманием к каждой детали.
          </p>
        </div>

        <!-- Навигация -->
        <div class="space-y-4">
          <h3 class="font-display text-lg font-bold text-gold-400">Навигация</h3>
          <ul class="space-y-3">
            <li v-for="item in navigation" :key="item.name">
              <RouterLink
                :to="item.href"
                class="text-silver-300 hover:text-gold-400 transition-colors duration-300 text-sm font-medium tracking-wide"
              >
                {{ item.name }}
              </RouterLink>
            </li>
          </ul>
        </div>

        <!-- Контакты -->
        <div class="space-y-4">
          <h3 class="font-display text-lg font-bold text-gold-400">Контакты</h3>
          <div class="space-y-3">
            <a
              :href="`tel:${env.contactPhone}`"
              class="flex items-center space-x-3 text-silver-300 hover:text-gold-400 transition-colors group"
            >
              <div class="w-10 h-10 bg-gold-500/10 rounded-lg flex items-center justify-center group-hover:bg-gold-500/20 transition-colors">
                <Phone class="w-5 h-5 text-gold-400" />
              </div>
              <span class="text-sm font-medium">{{ env.contactPhone }}</span>
            </a>
            <a
              :href="`mailto:${env.contactEmail}`"
              class="flex items-center space-x-3 text-silver-300 hover:text-gold-400 transition-colors group"
            >
              <div class="w-10 h-10 bg-gold-500/10 rounded-lg flex items-center justify-center group-hover:bg-gold-500/20 transition-colors">
                <Mail class="w-5 h-5 text-gold-400" />
              </div>
              <span class="text-sm font-medium">{{ env.contactEmail }}</span>
            </a>
          </div>
        </div>

        <!-- Социальные сети -->
        <div class="space-y-4">
          <h3 class="font-display text-lg font-bold text-gold-400">Социальные сети</h3>
          <div class="flex space-x-3">
            <a
              v-for="social in socialLinks"
              :key="social.name"
              :href="social.href"
              target="_blank"
              rel="noopener noreferrer"
              class="w-12 h-12 bg-silver-800 border-2 border-silver-700 rounded-xl flex items-center justify-center hover:bg-gold-500 hover:border-gold-500 hover:text-white transition-all duration-300 shadow-soft hover:shadow-gold"
              :aria-label="social.name"
            >
              <component :is="social.icon" class="w-5 h-5 text-silver-400 group-hover:text-white" />
            </a>
          </div>
          <p class="text-silver-300 text-sm">
            Подписывайтесь, чтобы первыми узнавать о новинках и акциях!
          </p>
        </div>
      </div>

      <!-- Нижняя часть футера -->
      <div class="border-t border-silver-800 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
          <p class="text-silver-300 text-sm">
            © {{ currentYear }} {{ env.siteName }}. Все права защищены.
          </p>
          <div class="flex flex-col md:flex-row items-center space-y-2 md:space-y-0 md:space-x-4">
            <p class="text-silver-300 text-sm">
              Создано с ❤️ для ценителей бисерного искусства
            </p>
            <p class="text-gold-400 text-xs font-medium">
              Версия: {{ version }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>
