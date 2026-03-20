<script setup>
import { RouterLink } from 'vue-router'
import { Phone, Mail, Instagram, Send, MessageCircle } from 'lucide-vue-next'
import { env } from '@/lib/env'

const currentYear = new Date().getFullYear()

const navigation = [
  { name: 'Главная', href: '/' },
  { name: 'Каталог', href: '/catalog' },
  { name: 'Контакты', href: '/contact' },
  { name: 'FAQ', href: '/faq' },
]

const socialLinks = [
  { name: 'Instagram', href: env.instagram, icon: Instagram },
  { name: 'Telegram', href: env.telegram, icon: Send },
  { name: 'VK', href: env.vk, icon: MessageCircle },
]
</script>

<template>
  <footer class="bg-[#A24C61] text-white">
    <div class="container-custom py-14">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
        <div class="space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-white/20 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-base">BW</span>
            </div>
            <div class="flex items-baseline gap-1.5">
              <span class="font-display text-lg text-white/80 font-normal">Bead</span>
              <span class="font-display text-lg font-bold text-white">Wonder</span>
            </div>
          </div>
          <p class="text-white/70 text-sm leading-relaxed">
            Бисерные чудеса ручной работы — каждое изделие создано с любовью и вниманием к каждой бусине.
          </p>
        </div>

        <div>
          <h3 class="text-xs font-bold text-white/80 uppercase tracking-widest mb-4">Навигация</h3>
          <ul class="space-y-2.5">
            <li v-for="item in navigation" :key="item.name">
              <RouterLink
                :to="item.href"
                class="text-white/70 hover:text-white transition-colors text-sm"
              >
                {{ item.name }}
              </RouterLink>
            </li>
          </ul>
        </div>

        <div>
          <h3 class="text-xs font-bold text-white/80 uppercase tracking-widest mb-4">Контакты</h3>
          <div class="space-y-3">
            <a
              :href="`tel:${env.contactPhone}`"
              class="flex items-center gap-3 text-white/70 hover:text-white transition-colors text-sm"
            >
              <Phone class="w-4 h-4 flex-shrink-0" />
              <span>{{ env.contactPhone }}</span>
            </a>
            <a
              :href="`mailto:${env.contactEmail}`"
              class="flex items-center gap-3 text-white/70 hover:text-white transition-colors text-sm"
            >
              <Mail class="w-4 h-4 flex-shrink-0" />
              <span>{{ env.contactEmail }}</span>
            </a>
          </div>
        </div>

        <div>
          <h3 class="text-xs font-bold text-white/80 uppercase tracking-widest mb-4">Соцсети</h3>
          <div class="flex gap-3">
            <a
              v-for="social in socialLinks"
              :key="social.name"
              :href="social.href"
              target="_blank"
              rel="noopener noreferrer"
              class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center text-white/70 hover:bg-white/20 hover:text-white transition-all duration-300"
              :aria-label="social.name"
            >
              <component :is="social.icon" class="w-4 h-4" />
            </a>
          </div>
          <p class="text-white/60 text-xs mt-3">
            Подписывайтесь на новинки и акции
          </p>
        </div>
      </div>
    </div>

    <div class="border-t border-white/20">
      <div class="container-custom py-5 flex flex-col sm:flex-row justify-between items-center gap-2">
        <p class="text-white/60 text-xs">
          &copy; {{ currentYear }} {{ env.siteName }}. Все права защищены.
        </p>
        <p class="text-white/50 text-xs italic">
          Где каждая бусина — маленькое чудо
        </p>
      </div>
    </div>
  </footer>
</template>
