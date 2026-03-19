<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { ArrowRight, Heart } from 'lucide-vue-next'
import AppButton from '@/components/ui/AppButton.vue'
import { getProducts, getCategories } from '@/api/public'
import { products as staticProducts, categories as staticCategories } from '@/lib/data'
import { formatPrice } from '@/lib/utils'

const route = useRoute()

const products = ref<any[]>([])
const categories = ref<any[]>([])
const isLoading = ref(true)

const loadData = async () => {
  try {
    const [prods, cats] = await Promise.all([
      getProducts(),
      getCategories()
    ])
    products.value = prods
    categories.value = cats
  } catch (error) {
    console.error('Error loading data:', error)
    // Fallback to static data if API fails
    products.value = staticProducts
    categories.value = staticCategories
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadData()
})

const selectedCategory = computed(() => route.query.category as string | undefined)

const filteredProducts = computed(() => {
  if (!selectedCategory.value) return products.value
  return products.value.filter(product => product.category === selectedCategory.value)
})

const selectedCategoryName = computed(() => {
  if (!selectedCategory.value) return 'Все изделия'
  const category = categories.value.find(cat => cat.id === selectedCategory.value)
  return category ? category.name : 'Все изделия'
})
</script>

<template>
  <div>
    <!-- Page Header -->
    <section class="py-14 md:py-20 gradient-bg">
      <div class="container-custom text-center">
        <h1 class="text-4xl md:text-5xl font-display font-bold text-gradient mb-3">
          {{ selectedCategoryName }}
        </h1>
        <p class="text-text-secondary max-w-lg mx-auto">
          Выберите уникальное украшение из бисера ручной работы
        </p>
      </div>
    </section>

    <!-- Filter -->
    <section class="py-6 bg-white border-b border-surface-200">
      <div class="container-custom">
        <div class="flex flex-wrap justify-center gap-2">
          <RouterLink
            to="/catalog"
            class="px-5 py-2 rounded-full text-sm font-medium transition-all duration-300"
            :class="!selectedCategory
              ? 'bg-gradient-to-r from-primary-500 to-accent-500 text-white shadow-glow'
              : 'bg-surface-100 text-text-secondary hover:bg-primary-50 hover:text-primary-600'"
          >
            Все
          </RouterLink>
          <RouterLink
            v-for="category in categories"
            :key="category.id"
            :to="`/catalog?category=${category.id}`"
            class="px-5 py-2 rounded-full text-sm font-medium transition-all duration-300"
            :class="selectedCategory === category.id
              ? 'bg-gradient-to-r from-primary-500 to-accent-500 text-white shadow-glow'
              : 'bg-surface-100 text-text-secondary hover:bg-primary-50 hover:text-primary-600'"
          >
            {{ category.name }}
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Products Grid -->
    <section class="section-padding bg-white">
      <div class="container-custom">
        <div v-if="filteredProducts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <RouterLink
            v-for="product in filteredProducts"
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
                <div v-if="!product.inStock" class="absolute top-3 left-3 bg-surface-600 text-white px-3 py-1 rounded-full text-xs font-semibold">
                  Нет в наличии
                </div>
              </div>
              <div class="p-5">
                <h3 class="font-display text-base font-bold text-text-primary mb-1.5 group-hover:text-primary-600 transition-colors line-clamp-1">
                  {{ product.name }}
                </h3>
                <p class="text-text-muted text-sm mb-3 line-clamp-2">
                  {{ product.description }}
                </p>
                <div class="flex items-center justify-between">
                  <span class="text-lg font-bold text-primary-600">
                    {{ formatPrice(product.price) }}
                  </span>
                  <Heart class="w-4 h-4 text-surface-400 group-hover:text-primary-400 transition-colors" />
                </div>
              </div>
            </div>
          </RouterLink>
        </div>
        <div v-else class="text-center py-16">
          <p class="text-text-secondary text-lg mb-4">В этой категории пока нет изделий</p>
          <RouterLink to="/catalog" custom v-slot="{ navigate }">
            <AppButton variant="outline" @click="navigate">
              Смотреть все изделия
            </AppButton>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="section-padding bg-gradient-to-r from-primary-600 via-primary-500 to-accent-500 text-white relative overflow-hidden">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(255,255,255,0.08),transparent_60%)]"></div>
      <div class="container-custom text-center relative z-10">
        <h2 class="text-3xl md:text-4xl font-display font-bold mb-4 text-white">
          Создадим ваше собственное чудо?
        </h2>
        <p class="text-lg mb-8 max-w-xl mx-auto text-white/85">
          Каждое изделие Bead Wonder — уникальная история.
          Свяжитесь со мной, и мы вместе создадим ваше украшение!
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
