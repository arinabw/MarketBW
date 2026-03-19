<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { ArrowRight, Star, Heart, Sparkles } from 'lucide-vue-next'
import AppButton from '@/components/ui/AppButton.vue'
import { getFeaturedProducts, getCategories, getReviews } from '@/api/public'
import type { Review } from '@/lib/catalog-types'
import { formatPrice } from '@/lib/utils'

const featuredProducts = ref<any[]>([])
const categories = ref<any[]>([])
const featuredReviews = ref<Review[]>([])
const isLoading = ref(true)

onMounted(async () => {
  try {
    const [products, cats, allReviews] = await Promise.all([
      getFeaturedProducts(),
      getCategories(),
      getReviews(),
    ])
    featuredProducts.value = products
    categories.value = cats
    featuredReviews.value = allReviews.slice(0, 3)
  } catch (error) {
    console.error('Error loading data:', error)
    featuredProducts.value = []
    categories.value = []
    featuredReviews.value = []
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <div>
    <!-- Hero -->
    <section class="hero-section relative overflow-hidden bg-gradient-to-br from-primary-50/80 via-white to-accent-50/60">
      <div class="container-custom relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
          <div class="space-y-7 animate-fade-in-up">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/80 backdrop-blur-sm rounded-full border border-primary-200/60 shadow-soft">
              <Sparkles class="w-4 h-4 text-primary-500" />
              <span class="text-sm font-medium text-text-secondary">Bead Wonder — бисерные чудеса</span>
            </div>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-display font-bold leading-tight text-text-primary">
              Маленькие чудеса
              <span class="text-gradient">из бисера</span>
            </h1>
            <p class="text-lg text-text-secondary leading-relaxed max-w-lg">
              Каждая бусина — произведение искусства. Уникальные украшения ручной работы, созданные с любовью и вниманием к каждой детали.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 pt-2">
              <RouterLink to="/catalog" custom v-slot="{ navigate }">
                <AppButton size="lg" variant="modern" @click="navigate">
                  Открыть каталог
                  <ArrowRight class="ml-2 w-5 h-5" />
                </AppButton>
              </RouterLink>
              <RouterLink to="/contact" custom v-slot="{ navigate }">
                <AppButton variant="outline" size="lg" @click="navigate">
                  Связаться с мастером
                </AppButton>
              </RouterLink>
            </div>
          </div>
          <div class="relative hidden lg:block">
            <div class="rounded-3xl overflow-hidden shadow-soft-xl">
              <img
                src="/images/hero-beadwork.svg"
                alt="Bead Wonder — украшения из бисера"
                class="w-full h-auto object-cover"
              />
            </div>
            <div class="absolute -top-6 -right-6 w-24 h-24 bg-primary-400/15 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-6 -left-6 w-32 h-32 bg-accent-400/10 rounded-full blur-2xl"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories -->
    <section class="section-padding bg-white">
      <div class="container-custom">
        <div class="text-center mb-14">
          <h2 class="section-title">Категории изделий</h2>
          <p class="text-text-secondary max-w-xl mx-auto">
            Выберите категорию и откройте для себя чудо бисерного искусства
          </p>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
          <RouterLink
            v-for="category in categories"
            :key="category.id"
            :to="`/catalog?category=${category.id}`"
            class="group block"
          >
            <div class="rounded-2xl overflow-hidden shadow-soft hover:shadow-soft-lg transition-all duration-500 hover:-translate-y-1">
              <div class="aspect-[4/5] relative overflow-hidden">
                <img
                  :src="category.image"
                  :alt="category.name"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/10 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-4 md:p-5">
                  <h3 class="font-display text-base md:text-lg font-bold text-white">
                    {{ category.name }}
                  </h3>
                  <p class="text-white/70 text-xs md:text-sm mt-0.5 line-clamp-1">
                    {{ category.description }}
                  </p>
                </div>
              </div>
            </div>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="section-padding bg-surface-50">
      <div class="container-custom">
        <div class="text-center mb-14">
          <h2 class="section-title">Популярные изделия</h2>
          <p class="text-text-secondary max-w-xl mx-auto">
            Самые любимые чудеса наших клиентов
          </p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          <RouterLink
            v-for="product in featuredProducts"
            :key="product.id"
            :to="`/product/${product.id}`"
            class="group block"
          >
            <div class="product-card">
              <div class="aspect-square relative overflow-hidden">
                <img
                  :src="product.images[0]"
                  :alt="product.name"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                />
                <div v-if="product.featured" class="absolute top-3 right-3 bg-primary-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-glow">
                  Хит
                </div>
              </div>
              <div class="p-4 md:p-5">
                <h3 class="font-display text-sm md:text-base font-bold text-text-primary mb-1 group-hover:text-primary-600 transition-colors line-clamp-1">
                  {{ product.name }}
                </h3>
                <p class="text-text-muted text-xs md:text-sm mb-3 line-clamp-2">
                  {{ product.description }}
                </p>
                <div class="flex items-center justify-between">
                  <span class="text-base md:text-lg font-bold text-primary-600">
                    {{ formatPrice(product.price) }}
                  </span>
                  <Heart class="w-4 h-4 text-surface-400 group-hover:text-primary-400 transition-colors" />
                </div>
              </div>
            </div>
          </RouterLink>
        </div>
        <div class="text-center mt-12">
          <RouterLink to="/catalog" custom v-slot="{ navigate }">
            <AppButton variant="outline" size="lg" @click="navigate">
              Все изделия
              <ArrowRight class="ml-2 w-5 h-5" />
            </AppButton>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Reviews -->
    <section class="section-padding bg-white">
      <div class="container-custom">
        <div class="text-center mb-14">
          <h2 class="section-title">Отзывы клиентов</h2>
          <p class="text-text-secondary max-w-xl mx-auto">
            Что говорят о наших бисерных чудесах
          </p>
        </div>
        <div v-if="featuredReviews.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div v-for="review in featuredReviews" :key="review.id" class="card-modern p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-11 h-11 bg-gradient-to-br from-primary-400 to-accent-400 rounded-full flex items-center justify-center text-white font-bold text-sm">
                {{ review.author.charAt(0) }}
              </div>
              <div>
                <h4 class="font-display font-bold text-sm text-text-primary">{{ review.author }}</h4>
                <div class="flex items-center gap-0.5 mt-0.5">
                  <Star
                    v-for="i in 5"
                    :key="i"
                    class="w-3.5 h-3.5"
                    :class="i <= review.rating ? 'text-primary-400 fill-current' : 'text-surface-300'"
                  />
                </div>
              </div>
            </div>
            <p class="text-text-secondary text-sm leading-relaxed italic">
              "{{ review.text }}"
            </p>
          </div>
        </div>
        <p v-else class="text-center text-text-secondary text-sm">
          Отзывы появятся здесь после загрузки с сервера.
        </p>
      </div>
    </section>

    <!-- CTA -->
    <section class="section-padding bg-gradient-to-r from-primary-600 via-primary-500 to-accent-500 relative overflow-hidden">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(255,255,255,0.08),transparent_60%)]"></div>
      <div class="container-custom text-center relative z-10">
        <h2 class="text-3xl md:text-4xl font-display font-bold mb-4 text-surface-900">
          Создадим ваше собственное чудо?
        </h2>
        <p class="text-lg mb-8 max-w-xl mx-auto text-surface-800">
          Я создаю украшения на заказ по вашим индивидуальным пожеланиям.
          Каждое изделие Bead Wonder — это уникальная история.
        </p>
        <RouterLink to="/contact" custom v-slot="{ navigate }">
          <AppButton size="lg" variant="secondary" @click="navigate">
            Заказать индивидуальное изделие
            <ArrowRight class="ml-2 w-5 h-5" />
          </AppButton>
        </RouterLink>
      </div>
    </section>
  </div>
</template>
