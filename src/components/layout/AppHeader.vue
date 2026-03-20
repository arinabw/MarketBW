<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { Menu, X, Phone, Mail } from 'lucide-vue-next'
import { useMenuStore } from '@/stores/useMenuStore'
import { env } from '@/lib/env'
import AppButton from '@/components/ui/AppButton.vue'

const menuStore = useMenuStore()
const route = useRoute()
const scrolled = ref(false)

const navigation = [
  { name: 'Главная', href: '/' },
  { name: 'Каталог', href: '/catalog' },
  { name: 'Контакты', href: '/contact' },
  { name: 'FAQ', href: '/faq' },
]

const isActive = (href) => {
  if (href === '/') return route.path === '/'
  return route.path.startsWith(href)
}

const onScroll = () => {
  scrolled.value = window.scrollY > 20
}

onMounted(() => window.addEventListener('scroll', onScroll))
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<template>
  <header
    class="sticky top-0 z-50 transition-all duration-300 border-b"
    :class="scrolled ? 'bg-[#A24C61]/95 backdrop-blur-md shadow-soft border-white/20' : 'bg-[#A24C61] border-transparent'"
  >
    <div class="container-custom">
      <div class="flex items-center justify-between h-16 md:h-18">
        <RouterLink to="/" class="flex items-center gap-3 group">
          <div class="w-9 h-9 bg-gradient-to-br from-primary-500 to-accent-500 rounded-lg flex items-center justify-center shadow-glow group-hover:shadow-glow-lg transition-shadow duration-300">
            <span class="text-white font-bold text-base">BW</span>
          </div>
          <div class="flex items-baseline gap-1.5">
            <span class="font-display text-lg text-text-secondary font-normal">Bead</span>
            <span class="font-display text-lg font-bold text-gradient">Wonder</span>
          </div>
        </RouterLink>

        <nav class="hidden md:flex items-center gap-8">
          <RouterLink
            v-for="item in navigation"
            :key="item.name"
            :to="item.href"
            class="text-sm font-medium tracking-wide transition-colors duration-200 relative py-1"
            :class="isActive(item.href)
              ? 'text-white'
              : 'text-white/80 hover:text-white'"
          >
            {{ item.name }}
            <span
              v-if="isActive(item.href)"
              class="absolute -bottom-0.5 left-0 right-0 h-0.5 bg-white rounded-full"
            ></span>
          </RouterLink>
        </nav>

        <div class="flex items-center gap-4">
          <a
            :href="`tel:${env.contactPhone}`"
            class="hidden lg:flex items-center gap-2 text-sm text-white/80 hover:text-white transition-colors"
          >
            <Phone class="w-4 h-4" />
            <span class="font-medium">{{ env.contactPhone }}</span>
          </a>

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
      </div>

      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div v-if="menuStore.isMenuOpen" class="md:hidden pb-4">
          <nav class="bg-white rounded-2xl border border-surface-200 shadow-soft-lg p-2 space-y-0.5">
            <RouterLink
              v-for="item in navigation"
              :key="item.name"
              :to="item.href"
              class="block px-4 py-3 rounded-xl text-sm font-medium transition-colors"
              :class="isActive(item.href)
                ? 'bg-[#A24C61] text-white'
                : 'text-text-secondary hover:bg-[#A24C61]/10 hover:text-[#A24C61]'"
              @click="menuStore.closeMenu"
            >
              {{ item.name }}
            </RouterLink>
            <div class="pt-3 mt-2 border-t border-surface-200 space-y-0.5">
              <a
                :href="`tel:${env.contactPhone}`"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-text-secondary hover:text-[#A24C61] hover:bg-[#A24C61]/10 transition-colors"
              >
                <Phone class="w-4 h-4" />
                <span>{{ env.contactPhone }}</span>
              </a>
              <a
                :href="`mailto:${env.contactEmail}`"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-text-secondary hover:text-[#A24C61] hover:bg-[#A24C61]/10 transition-colors"
              >
                <Mail class="w-4 h-4" />
                <span>{{ env.contactEmail }}</span>
              </a>
            </div>
          </nav>
        </div>
      </Transition>
    </div>
  </header>
</template>
