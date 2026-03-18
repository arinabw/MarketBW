<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { ArrowRight, Heart } from 'lucide-vue-next'
import AppButton from '@/components/ui/AppButton.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppCardContent from '@/components/ui/AppCardContent.vue'
import { products, categories } from '@/lib/data'
import { formatPrice } from '@/lib/utils'

const route = useRoute()

const selectedCategory = computed(() => route.query.category as string | undefined)

const filteredProducts = computed(() => {
  if (!selectedCategory.value) return products
  return products.filter(product => product.category === selectedCategory.value)
})

const selectedCategoryName = computed(() => {
  if (!selectedCategory.value) return 'Все изделия'
  const category = categories.find(cat => cat.id === selectedCategory.value)
  return category ? category.name : 'Все изделия'
})
</script>

<template>
  <div class="min-h-screen">
    <!-- Header Section -->
    <section class="section-padding gradient-bg">
      <div class="container-custom">
        <div class="text-center">
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-display font-bold text-gradient mb-4">
            {{ selectedCategoryName }}
          </h1>
          <p class="text-lg text-text-secondary max-w-2xl mx-auto">
            Выберите уникальное украшение из бисера ручной работы
          </p>
        </div>
      </div>
    </section>

    <!-- Category Filter -->
    <section class="py-8 bg-white border-b border-surface-200">
      <div class="container-custom">
        <div class="flex flex-wrap justify-center gap-3">
          <RouterLink
            to="/catalog"
            class="px-6 py-2.5 rounded-full transition-all duration-300 font-medium"
            :class="!selectedCategory ? 'bg-gradient-to-r from-primary-500 to-accent-500 text-white shadow-glow' : 'bg-surface-100 text-text-secondary hover:bg-primary-50 hover:text-primary-500'"
          >
            Все
          </RouterLink>
          <RouterLink
            v-for="category in categories"
            :key="category.id"
            :to="`/catalog?category=${category.id}`"
            class="px-6 py-2.5 rounded-full transition-all duration-300 font-medium"
            :class="selectedCategory === category.id ? 'bg-gradient-to-r from-primary-500 to-accent-500 text-white shadow-glow' : 'bg-surface-100 text-text-secondary hover:bg-primary-50 hover:text-primary-500'"
          >
            {{ category.name }}
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Products Grid -->
    <section class="section-padding bg-white">
      <div class="container-custom">
        <div v-if="filteredProducts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <RouterLink
            v-for="product in filteredProducts"
            :key="product.id"
            :to="`/product/${product.id}`"
            class="group block"
          >
            <AppCard class="card-modern overflow-hidden product-card">
              <div class="aspect-square relative overflow-hidden">
                <img
                  :src="product.images[0]"
                  :alt="product.name"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                />
                <div v-if="product.featured" class="absolute top-4 right-4 bg-gradient-to-r from-primary-500 to-accent-500 text-white px-4 py-1.5 rounded-full text-xs font-bold shadow-glow">
                  Хит
                </div>
                <div v-if="!product.inStock" class="absolute top-4 left-4 bg-surface-500 text-white px-4 py-1.5 rounded-full text-xs font-bold">
                  Нет в наличии
                </div>
              </div>
              <AppCardContent class="p-6">
                <h3 class="font-display text-xl font-bold text-text-primary mb-2 group-hover:text-primary-500 transition-colors">
                  {{ product.name }}
                </h3>
                <p class="text-text-secondary text-sm mb-4 line-clamp-2">
                  {{ product.description }}
                </p>
                <div class="flex items-center justify-between">
                  <span class="text-lg font-bold text-text-primary">
                    {{ formatPrice(product.price) }}
                  </span>
                  <div class="flex items-center space-x-1">
                    <Heart class="w-4 h-4 text-primary-500" />
                    <span class="text-sm text-text-secondary">В избранное</span>
                  </div>
                </div>
              </AppCardContent>
            </AppCard>
          </RouterLink>
        </div>
        <div v-else class="text-center py-12">
          <p class="text-text-secondary text-lg">В этой категории пока нет изделий</p>
          <RouterLink to="/catalog" class="inline-block mt-4">
            <AppButton variant="outline" class="border-2 border-surface-200 hover:border-primary-500 hover:text-primary-500">
              Смотреть все изделия
            </AppButton>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="section-padding bg-gradient-to-r from-primary-500 to-accent-500 text-white relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-primary-600 to-accent-600 opacity-50"></div>
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
            variant="secondary"
            class="bg-white text-primary-500 hover:bg-surface-50 shadow-glow hover:shadow-glow-lg"
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
