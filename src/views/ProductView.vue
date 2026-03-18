<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { ArrowLeft, Heart, Star } from 'lucide-vue-next'
import AppButton from '@/components/ui/AppButton.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppCardHeader from '@/components/ui/AppCardHeader.vue'
import AppCardContent from '@/components/ui/AppCardContent.vue'
import AppCardTitle from '@/components/ui/AppCardTitle.vue'
import { getProductById, getReviewsByProductId, categories } from '@/lib/data'
import { formatPrice } from '@/lib/utils'

const route = useRoute()
const productId = computed(() => route.params.id as string)

const product = computed(() => getProductById(productId.value))
const productReviews = computed(() => getReviewsByProductId(productId.value))

const currentImageIndex = ref(0)

const nextImage = () => {
  if (!product.value) return
  currentImageIndex.value = (currentImageIndex.value + 1) % product.value.images.length
}

const prevImage = () => {
  if (!product.value) return
  currentImageIndex.value = currentImageIndex.value === 0
    ? product.value.images.length - 1
    : currentImageIndex.value - 1
}
</script>

<template>
  <div v-if="product">
    <!-- Back -->
    <section class="py-4 bg-white border-b border-surface-200">
      <div class="container-custom">
        <RouterLink to="/catalog" class="inline-flex items-center text-sm text-primary-600 hover:text-primary-700 transition-colors font-medium">
          <ArrowLeft class="w-4 h-4 mr-1.5" />
          Вернуться в каталог
        </RouterLink>
      </div>
    </section>

    <!-- Product -->
    <section class="section-padding bg-white">
      <div class="container-custom">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14">
          <!-- Images -->
          <div class="space-y-4">
            <div class="relative aspect-square overflow-hidden rounded-2xl shadow-soft-lg bg-surface-50">
              <img
                :src="product.images[currentImageIndex]"
                :alt="product.name"
                class="w-full h-full object-cover"
              />
              <button
                v-if="product.images.length > 1"
                @click="prevImage"
                class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-xl flex items-center justify-center hover:bg-white transition-colors shadow-card"
              >
                <ArrowLeft class="w-4 h-4" />
              </button>
              <button
                v-if="product.images.length > 1"
                @click="nextImage"
                class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-xl flex items-center justify-center hover:bg-white transition-colors shadow-card"
              >
                <ArrowLeft class="w-4 h-4 rotate-180" />
              </button>
            </div>
            <div v-if="product.images.length > 1" class="flex gap-3">
              <button
                v-for="(image, index) in product.images"
                :key="index"
                @click="currentImageIndex = index"
                class="w-20 h-20 rounded-xl overflow-hidden border-2 transition-all"
                :class="currentImageIndex === index ? 'border-primary-500 shadow-glow' : 'border-surface-200 hover:border-surface-400'"
              >
                <img :src="image" :alt="`${product.name} ${index + 1}`" class="w-full h-full object-cover" />
              </button>
            </div>
          </div>

          <!-- Info -->
          <div class="space-y-6">
            <div>
              <h1 class="text-3xl md:text-4xl font-display font-bold text-text-primary mb-3">
                {{ product.name }}
              </h1>
              <p class="text-2xl md:text-3xl font-bold text-primary-600 mb-4">
                {{ formatPrice(product.price) }}
              </p>
              <div class="flex items-center gap-2">
                <span v-if="product.inStock" class="px-3.5 py-1.5 bg-green-50 text-green-700 rounded-full text-xs font-semibold">
                  В наличии
                </span>
                <span v-else class="px-3.5 py-1.5 bg-surface-100 text-surface-600 rounded-full text-xs font-semibold">
                  Нет в наличии
                </span>
                <span v-if="product.featured" class="px-3.5 py-1.5 bg-primary-50 text-primary-700 rounded-full text-xs font-semibold">
                  Хит продаж
                </span>
              </div>
            </div>

            <p class="text-text-secondary leading-relaxed">
              {{ product.description }}
            </p>

            <AppCard class="shadow-card">
              <AppCardHeader>
                <AppCardTitle>Характеристики</AppCardTitle>
              </AppCardHeader>
              <AppCardContent>
                <div class="space-y-3">
                  <div class="flex justify-between py-2 border-b border-surface-100">
                    <span class="text-text-secondary text-sm">Категория</span>
                    <span class="font-semibold text-text-primary text-sm">{{ categories.find(c => c.id === product.category)?.name }}</span>
                  </div>
                  <div v-if="product.size" class="flex justify-between py-2 border-b border-surface-100">
                    <span class="text-text-secondary text-sm">Размер</span>
                    <span class="font-semibold text-text-primary text-sm">{{ product.size }}</span>
                  </div>
                  <div class="flex justify-between py-2 border-b border-surface-100">
                    <span class="text-text-secondary text-sm">Техника</span>
                    <span class="font-semibold text-text-primary text-sm">{{ product.technique }}</span>
                  </div>
                  <div class="flex justify-between py-2">
                    <span class="text-text-secondary text-sm">Материалы</span>
                    <span class="font-semibold text-text-primary text-sm text-right max-w-[60%]">{{ product.materials.join(', ') }}</span>
                  </div>
                </div>
              </AppCardContent>
            </AppCard>

            <div class="flex gap-3">
              <RouterLink to="/contact" custom v-slot="{ navigate }">
                <AppButton size="lg" variant="modern" class="flex-1" @click="navigate">
                  Заказать
                </AppButton>
              </RouterLink>
              <AppButton variant="outline" size="lg">
                <Heart class="w-5 h-5" />
              </AppButton>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Reviews -->
    <section v-if="productReviews.length > 0" class="section-padding bg-surface-50">
      <div class="container-custom">
        <h2 class="section-title">Отзывы</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-10">
          <div v-for="review in productReviews" :key="review.id" class="card-modern p-6">
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
      </div>
    </section>
  </div>

  <div v-else class="min-h-[60vh] flex items-center justify-center">
    <div class="text-center">
      <h1 class="text-2xl font-display font-bold text-text-primary mb-3">
        Изделие не найдено
      </h1>
      <p class="text-text-secondary mb-6">
        К сожалению, изделие с таким ID не существует
      </p>
      <RouterLink to="/catalog" custom v-slot="{ navigate }">
        <AppButton variant="outline" @click="navigate">
          Вернуться в каталог
        </AppButton>
      </RouterLink>
    </div>
  </div>
</template>
