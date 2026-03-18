<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { ArrowLeft, Heart, Star } from 'lucide-vue-next'
import AppButton from '@/components/ui/AppButton.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppCardContent from '@/components/ui/AppCardContent.vue'
import AppCardHeader from '@/components/ui/AppCardHeader.vue'
import AppCardTitle from '@/components/ui/AppCardTitle.vue'
import { getProductById, getReviewsByProductId } from '@/lib/data'
import { formatPrice } from '@/lib/utils'

const route = useRoute()
const productId = computed(() => route.params.id as string)

const product = computed(() => getProductById(productId.value))
const reviews = computed(() => getReviewsByProductId(productId.value))

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
  <div v-if="product" class="min-h-screen">
    <!-- Back Button -->
    <section class="py-4 bg-white border-b border-lavender/20">
      <div class="container-custom">
        <RouterLink to="/catalog" class="inline-flex items-center text-text-rose hover:text-dusty-rose transition-colors">
          <ArrowLeft class="w-5 h-5 mr-2" />
          Вернуться в каталог
        </RouterLink>
      </div>
    </section>

    <!-- Product Details -->
    <section class="section-padding bg-white">
      <div class="container-custom">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
          <!-- Images -->
          <div class="space-y-4">
            <div class="relative aspect-square overflow-hidden rounded-2xl shadow-soft-lg">
              <img
                :src="product.images[currentImageIndex]"
                :alt="product.name"
                class="w-full h-full object-cover"
              />
              <button
                v-if="product.images.length > 1"
                @click="prevImage"
                class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/80 rounded-full flex items-center justify-center hover:bg-white transition-colors shadow-soft"
              >
                <ArrowLeft class="w-5 h-5" />
              </button>
              <button
                v-if="product.images.length > 1"
                @click="nextImage"
                class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/80 rounded-full flex items-center justify-center hover:bg-white transition-colors shadow-soft"
              >
                <ArrowLeft class="w-5 h-5 rotate-180" />
              </button>
            </div>
            <div v-if="product.images.length > 1" class="flex gap-2">
              <button
                v-for="(image, index) in product.images"
                :key="index"
                @click="currentImageIndex = index"
                class="w-20 h-20 rounded-lg overflow-hidden border-2 transition-all"
                :class="currentImageIndex === index ? 'border-dusty-rose' : 'border-transparent hover:border-lavender'"
              >
                <img :src="image" :alt="`${product.name} ${index + 1}`" class="w-full h-full object-cover" />
              </button>
            </div>
          </div>

          <!-- Info -->
          <div class="space-y-6">
            <div>
              <h1 class="text-3xl md:text-4xl font-playfair font-bold text-text-rose mb-4">
                {{ product.name }}
              </h1>
              <p class="text-2xl font-semibold text-text-dark mb-4">
                {{ formatPrice(product.price) }}
              </p>
              <div class="flex items-center gap-2 mb-4">
                <span v-if="product.inStock" class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">
                  В наличии
                </span>
                <span v-else class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">
                  Нет в наличии
                </span>
                <span v-if="product.featured" class="px-3 py-1 bg-dusty-rose text-white rounded-full text-sm">
                  Хит продаж
                </span>
              </div>
            </div>

            <p class="text-text-medium leading-relaxed">
              {{ product.description }}
            </p>

            <AppCard class="card-romantic">
              <AppCardHeader>
                <AppCardTitle>Характеристики</AppCardTitle>
              </AppCardHeader>
              <AppCardContent>
                <div class="space-y-3">
                  <div class="flex justify-between">
                    <span class="text-text-medium">Категория:</span>
                    <span class="font-medium">{{ categories.find(c => c.id === product.category)?.name }}</span>
                  </div>
                  <div v-if="product.size" class="flex justify-between">
                    <span class="text-text-medium">Размер:</span>
                    <span class="font-medium">{{ product.size }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-text-medium">Техника:</span>
                    <span class="font-medium">{{ product.technique }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-text-medium">Материалы:</span>
                    <span class="font-medium">{{ product.materials.join(', ') }}</span>
                  </div>
                </div>
              </AppCardContent>
            </AppCard>

            <div class="flex gap-4">
              <RouterLink to="/contact" custom v-slot="{ navigate }">
                <AppButton 
                  size="lg" 
                  class="btn-romantic flex-1"
                  @click="navigate"
                >
                  Заказать
                </AppButton>
              </RouterLink>
              <AppButton 
                variant="outline" 
                size="lg"
                class="border-dusty-rose text-dusty-rose hover:bg-dusty-rose hover:text-white"
              >
                <Heart class="w-5 h-5" />
              </AppButton>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Reviews -->
    <section v-if="reviews.length > 0" class="section-padding bg-rose-light">
      <div class="container-custom">
        <h2 class="section-title">Отзывы</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <AppCard v-for="review in reviews" :key="review.id" class="card-romantic p-6">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-gradient-to-r from-dusty-rose to-pastel-purple rounded-full flex items-center justify-center text-white font-bold">
                {{ review.author.charAt(0) }}
              </div>
              <div class="ml-4">
                <h4 class="font-playfair text-text-rose">{{ review.author }}</h4>
                <div class="flex items-center">
                  <Star
                    v-for="i in 5"
                    :key="i"
                    class="w-4 h-4"
                    :class="i <= review.rating ? 'text-yellow-400 fill-current' : 'text-gray-300'"
                  />
                </div>
              </div>
            </div>
            <p class="text-text-medium italic">
              "{{ review.text }}"
            </p>
          </AppCard>
        </div>
      </div>
    </section>
  </div>
  <div v-else class="min-h-screen flex items-center justify-center">
    <div class="text-center">
      <h1 class="text-3xl font-playfair font-bold text-text-rose mb-4">
        Изделие не найдено
      </h1>
      <p class="text-text-medium mb-6">
        К сожалению, изделие с таким ID не существует
      </p>
      <RouterLink to="/catalog" custom v-slot="{ navigate }">
        <AppButton 
          variant="outline" 
          class="border-dusty-rose text-dusty-rose hover:bg-dusty-rose hover:text-white"
          @click="navigate"
        >
          Вернуться в каталог
        </AppButton>
      </RouterLink>
    </div>
  </div>
</template>
