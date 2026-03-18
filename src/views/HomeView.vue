<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { ArrowRight, Star, Heart } from 'lucide-vue-next'
import AppButton from '@/components/ui/AppButton.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppCardContent from '@/components/ui/AppCardContent.vue'
import { getFeaturedProducts, categories, reviews } from '@/lib/data'
import { formatPrice } from '@/lib/utils'

const featuredProducts = computed(() => getFeaturedProducts())
const featuredReviews = computed(() => reviews.slice(0, 3))
</script>

<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <section class="relative overflow-hidden gradient-bg hero-section">
      <div class="absolute inset-0 bg-gradient-to-br from-white/90 via-white/80 to-gold-50/50"></div>
      <div class="container-custom relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div class="space-y-8 animate-fade-in-up">
            <div class="inline-flex items-center px-4 py-2 bg-white/80 backdrop-blur-sm rounded-full border border-gold-200 shadow-soft">
              <span class="w-2 h-2 bg-gold-500 rounded-full mr-2 animate-pulse"></span>
              <span class="text-sm font-medium text-text-secondary">Ручная работа с любовью</span>
            </div>
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-display font-bold text-gradient-gold leading-tight">
              Уникальные украшения из бисера
            </h1>
            <p class="text-xl text-text-secondary leading-relaxed max-w-xl">
              Каждое изделие создано с любовью и вниманием к деталям.
              Откройте для себя мир нежной красоты и элегантности.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
              <RouterLink to="/catalog" custom v-slot="{ navigate }">
                <AppButton
                  size="lg"
                  variant="premium"
                  @click="navigate"
                >
                  Смотреть каталог
                  <ArrowRight class="ml-2 w-5 h-5" />
                </AppButton>
              </RouterLink>
              <RouterLink to="/contact" custom v-slot="{ navigate }">
                <AppButton
                  variant="outline"
                  size="lg"
                  class="border-2 border-gold-500 text-gold-600 hover:bg-gold-50 hover:shadow-gold"
                  @click="navigate"
                >
                  Связаться с мастером
                </AppButton>
              </RouterLink>
            </div>
          </div>
          <div class="relative">
            <div class="relative z-10">
              <div class="relative rounded-3xl overflow-hidden shadow-soft-xl">
                <img
                  src="/images/hero-beadwork.jpg"
                  alt="Украшения из бисера"
                  class="w-full h-auto transform hover:scale-105 transition-transform duration-700 image-hover"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>
              </div>
            </div>
            <div class="absolute -top-8 -right-8 w-32 h-32 bg-gold-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-8 -left-8 w-40 h-40 bg-gold-400/10 rounded-full blur-3xl"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section class="section-padding bg-white">
      <div class="container-custom">
        <div class="text-center mb-16">
          <h2 class="section-title">Категории изделий</h2>
          <p class="text-text-secondary text-lg max-w-2xl mx-auto">
            Выберите категорию, чтобы ознакомиться с коллекцией уникальных украшений
          </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <RouterLink
            v-for="category in categories"
            :key="category.id"
            :to="`/catalog?category=${category.id}`"
            class="group block"
          >
            <AppCard class="card-premium overflow-hidden hover:scale-105 transition-transform duration-500">
              <div class="aspect-square relative overflow-hidden">
                <img
                  :src="category.image"
                  :alt="category.name"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 image-hover"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </div>
              <AppCardContent class="p-6 text-center">
                <h3 class="font-display text-xl font-bold text-text-primary mb-2 group-hover:text-gold-500 transition-colors">
                  {{ category.name }}
                </h3>
                <p class="text-text-secondary text-sm">
                  {{ category.description }}
                </p>
              </AppCardContent>
            </AppCard>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="section-padding bg-gradient-to-br from-gold-50 via-white to-silver-50">
      <div class="container-custom">
        <div class="text-center mb-16">
          <h2 class="section-title">Популярные изделия</h2>
          <p class="text-text-secondary text-lg max-w-2xl mx-auto">
            Самые любимые работы наших клиентов
          </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <RouterLink
            v-for="product in featuredProducts"
            :key="product.id"
            :to="`/product/${product.id}`"
            class="group block"
          >
            <AppCard class="card-premium overflow-hidden product-card">
              <div class="aspect-square relative overflow-hidden">
                <img
                  :src="product.images[0]"
                  :alt="product.name"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 image-hover"
                />
                <div v-if="product.featured" class="absolute top-4 right-4 bg-gradient-to-r from-gold-500 to-gold-400 text-white px-4 py-1.5 rounded-full text-xs font-bold shadow-gold">
                  Хит
                </div>
              </div>
              <AppCardContent class="p-6">
                <h3 class="font-display text-xl font-bold text-text-primary mb-2 group-hover:text-gold-500 transition-colors">
                  {{ product.name }}
                </h3>
                <p class="text-text-secondary text-sm mb-4 line-clamp-2">
                  {{ product.description }}
                </p>
                <div class="flex items-center justify-between">
                  <span class="text-xl font-bold text-gold-600">
                    {{ formatPrice(product.price) }}
                  </span>
                  <div class="flex items-center space-x-1">
                    <Heart class="w-4 h-4 text-gold-500" />
                    <span class="text-sm text-text-secondary">В избранное</span>
                  </div>
                </div>
              </AppCardContent>
            </AppCard>
          </RouterLink>
        </div>
        <div class="text-center mt-12">
          <RouterLink to="/catalog" custom v-slot="{ navigate }">
            <AppButton
              variant="outline"
              size="lg"
              class="border-2 border-gold-500 text-gold-600 hover:bg-gold-50 hover:shadow-gold"
              @click="navigate"
            >
              Смотреть все изделия
              <ArrowRight class="ml-2 w-5 h-5" />
            </AppButton>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Reviews Section -->
    <section class="section-padding bg-white">
      <div class="container-custom">
        <div class="text-center mb-16">
          <h2 class="section-title">Отзывы клиентов</h2>
          <p class="text-text-secondary text-lg max-w-2xl mx-auto">
            Что говорят о наших изделиях
          </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <AppCard v-for="review in featuredReviews" :key="review.id" class="card-premium p-6">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 gradient-gold rounded-xl flex items-center justify-center text-white font-bold shadow-gold">
                {{ review.author.charAt(0) }}
              </div>
              <div class="ml-4">
                <h4 class="font-display font-bold text-text-primary">{{ review.author }}</h4>
                <div class="flex items-center">
                  <Star
                    v-for="i in 5"
                    :key="i"
                    class="w-4 h-4"
                    :class="i <= review.rating ? 'text-gold-400 fill-current' : 'text-silver-300'"
                  />
                </div>
              </div>
            </div>
            <p class="text-text-secondary italic">
              "{{ review.text }}"
            </p>
          </AppCard>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="section-padding bg-gradient-to-r from-gold-500 via-gold-400 to-gold-500 text-white relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-gold-600 to-gold-500 opacity-50"></div>
      <div class="container-custom text-center relative z-10">
        <h2 class="text-3xl md:text-4xl lg:text-5xl font-display font-bold mb-6">
          Не нашли то, что искали?
        </h2>
        <p class="text-lg md:text-xl mb-8 max-w-2xl mx-auto opacity-90">
          Я создаю украшения на заказ по вашим индивидуальным пожеланиям.
          Свяжитесь со мной, и мы вместе создадим уникальное изделие!
        </p>
        <RouterLink to="/contact" custom v-slot="{ navigate }">
          <AppButton
            size="lg"
            variant="outline"
            class="bg-white text-gold-600 hover:bg-gold-50 shadow-gold hover:shadow-gold-lg"
            @click="navigate"
          >
            Заказать индивидуальное изделие
            <ArrowRight class="ml-2 w-5 h-5" />
          </AppButton>
        </RouterLink>
      </div>
    </section>
  </div>
</template>
