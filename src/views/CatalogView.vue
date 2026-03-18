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
    <section class="section-padding bg-gradient-to-br from-rose-light via-white to-lavender/20">
      <div class="container-custom">
        <div class="text-center">
          <h1 class="text-4xl md:text-5xl font-playfair font-bold text-gradient mb-4">
            {{ selectedCategoryName }}
          </h1>
          <p class="text-lg text-text-medium max-w-2xl mx-auto">
            Выберите уникальное украшение из бисера ручной работы
          </p>
        </div>
      </div>
    </section>

    <!-- Category Filter -->
    <section class="py-8 bg-white border-b border-lavender/20">
      <div class="container-custom">
        <div class="flex flex-wrap justify-center gap-4">
          <RouterLink
            to="/catalog"
            class="px-6 py-2 rounded-full transition-all duration-300"
            :class="!selectedCategory ? 'bg-dusty-rose text-white shadow-soft' : 'bg-rose-light text-text-medium hover:bg-dusty-rose hover:text-white'"
          >
            Все
          </RouterLink>
          <RouterLink
            v-for="category in categories"
            :key="category.id"
            :to="`/catalog?category=${category.id}`"
            class="px-6 py-2 rounded-full transition-all duration-300"
            :class="selectedCategory === category.id ? 'bg-dusty-rose text-white shadow-soft' : 'bg-rose-light text-text-medium hover:bg-dusty-rose hover:text-white'"
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
            <AppCard class="card-romantic overflow-hidden product-card">
              <div class="aspect-square relative overflow-hidden">
                <img
                  :src="product.images[0]"
                  :alt="product.name"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                />
                <div v-if="product.featured" class="absolute top-4 right-4 bg-dusty-rose text-white px-3 py-1 rounded-full text-xs font-medium">
                  Хит
                </div>
                <div v-if="!product.inStock" class="absolute top-4 left-4 bg-gray-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                  Нет в наличии
                </div>
              </div>
              <AppCardContent class="p-6">
                <h3 class="font-playfair text-xl text-text-rose mb-2 group-hover:text-dusty-rose transition-colors">
                  {{ product.name }}
                </h3>
                <p class="text-text-medium text-sm mb-4 line-clamp-2">
                  {{ product.description }}
                </p>
                <div class="flex items-center justify-between">
                  <span class="text-lg font-semibold text-text-dark">
                    {{ formatPrice(product.price) }}
                  </span>
                  <div class="flex items-center space-x-1">
                    <Heart class="w-4 h-4 text-dusty-rose" />
                    <span class="text-sm text-text-medium">В избранное</span>
                  </div>
                </div>
              </AppCardContent>
            </AppCard>
          </RouterLink>
        </div>
        <div v-else class="text-center py-12">
          <p class="text-text-medium text-lg">В этой категории пока нет изделий</p>
          <RouterLink to="/catalog" class="inline-block mt-4">
            <AppButton variant="outline" class="border-dusty-rose text-dusty-rose hover:bg-dusty-rose hover:text-white">
              Смотреть все изделия
            </AppButton>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="section-padding bg-gradient-to-r from-dusty-rose to-pastel-purple text-white">
      <div class="container-custom text-center">
        <h2 class="text-3xl md:text-4xl font-playfair font-bold mb-6">
          Не нашли то, что искали?
        </h2>
        <p class="text-lg mb-8 max-w-2xl mx-auto">
          Я создаю украшения на заказ по вашим индивидуальным пожеланиям. 
          Свяжитесь со мной, и мы вместе создадим уникальное изделие!
        </p>
        <RouterLink to="/contact" custom v-slot="{ navigate }">
          <AppButton 
            size="lg" 
            variant="secondary"
            class="bg-white text-dusty-rose hover:bg-ghost-white"
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
