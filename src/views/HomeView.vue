<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { ArrowRight, Star, Heart } from 'lucide-vue-next'
import AppButton from '@/components/ui/AppButton.vue'
import AppCard, { AppCardContent } from '@/components/ui/AppCard.vue'
import { getFeaturedProducts, categories, reviews } from '@/lib/data'
import { formatPrice } from '@/lib/utils'

const featuredProducts = computed(() => getFeaturedProducts())
const featuredReviews = computed(() => reviews.slice(0, 3))
</script>

<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-rose-light via-white to-lavender/20">
      <div class="container-custom section-padding">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div class="space-y-6 animate-fade-in">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-playfair font-bold text-gradient leading-tight">
              Уникальные украшения из бисера ручной работы
            </h1>
            <p class="text-lg text-text-medium leading-relaxed">
              Каждое изделие создано с любовью и вниманием к деталям. 
              Откройте для себя мир нежной красоты и элегантности.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
              <RouterLink to="/catalog" custom v-slot="{ navigate }">
                <AppButton 
                  size="lg" 
                  class="btn-romantic"
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
                  class="border-dusty-rose text-dusty-rose hover:bg-dusty-rose hover:text-white"
                  @click="navigate"
                >
                  Связаться с мастером
                </AppButton>
              </RouterLink>
            </div>
          </div>
          <div class="relative">
            <div class="relative z-10">
              <img
                src="/images/hero-beadwork.jpg"
                alt="Украшения из бисера"
                class="rounded-2xl shadow-soft-lg w-full h-auto"
              />
            </div>
            <div class="absolute -top-4 -right-4 w-24 h-24 bg-dusty-rose/20 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-pastel-purple/20 rounded-full blur-2xl"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section class="section-padding bg-white">
      <div class="container-custom">
        <div class="text-center mb-12">
          <h2 class="section-title">Категории изделий</h2>
          <p class="text-text-medium max-w-2xl mx-auto">
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
            <AppCard class="card-romantic overflow-hidden hover:scale-105 transition-transform duration-300">
              <div class="aspect-square relative overflow-hidden">
                <img
                  :src="category.image"
                  :alt="category.name"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </div>
              <AppCardContent class="p-4 text-center">
                <h3 class="font-playfair text-lg text-text-rose mb-2">
                  {{ category.name }}
                </h3>
                <p class="text-text-medium text-sm">
                  {{ category.description }}
                </p>
              </AppCardContent>
            </AppCard>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="section-padding bg-rose-light">
      <div class="container-custom">
        <div class="text-center mb-12">
          <h2 class="section-title">Популярные изделия</h2>
          <p class="text-text-medium max-w-2xl mx-auto">
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
        <div class="text-center mt-12">
          <RouterLink to="/catalog" custom v-slot="{ navigate }">
            <AppButton 
              variant="outline" 
              size="lg"
              class="border-dusty-rose text-dusty-rose hover:bg-dusty-rose hover:text-white"
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
        <div class="text-center mb-12">
          <h2 class="section-title">Отзывы клиентов</h2>
          <p class="text-text-medium max-w-2xl mx-auto">
            Что говорят о наших изделиях
          </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <AppCard v-for="review in featuredReviews" :key="review.id" class="card-romantic p-6">
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
