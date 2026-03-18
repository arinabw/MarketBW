import Link from 'next/link'
import { ArrowRight, Star, Heart } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { getFeaturedProducts, categories, reviews } from '@/lib/data'
import { formatPrice } from '@/lib/utils'

export default function HomePage() {
  const featuredProducts = getFeaturedProducts()
  const featuredReviews = reviews.slice(0, 3)

  return (
    <div className="min-h-screen">
      {/* Hero Section */}
      <section className="relative overflow-hidden bg-gradient-to-br from-rose-light via-white to-lavender/20">
        <div className="container-custom section-padding">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div className="space-y-6 animate-fade-in">
              <h1 className="text-4xl md:text-5xl lg:text-6xl font-playfair font-bold text-gradient leading-tight">
                Уникальные украшения из бисера ручной работы
              </h1>
              <p className="text-lg text-text-medium leading-relaxed">
                Каждое изделие создано с любовью и вниманием к деталям. 
                Откройте для себя мир нежной красоты и элегантности.
              </p>
              <div className="flex flex-col sm:flex-row gap-4">
                <Button 
                  size="lg" 
                  className="btn-romantic"
                  asChild
                >
                  <Link href="/catalog">
                    Смотреть каталог
                    <ArrowRight className="ml-2 w-5 h-5" />
                  </Link>
                </Button>
                <Button 
                  variant="outline" 
                  size="lg"
                  className="border-dusty-rose text-dusty-rose hover:bg-dusty-rose hover:text-white"
                  asChild
                >
                  <Link href="/contact">
                    Связаться с мастером
                  </Link>
                </Button>
              </div>
            </div>
            <div className="relative">
              <div className="relative z-10">
                <img
                  src="/images/hero-beadwork.jpg"
                  alt="Украшения из бисера"
                  className="rounded-2xl shadow-soft-lg w-full h-auto"
                />
              </div>
              <div className="absolute -top-4 -right-4 w-24 h-24 bg-dusty-rose/20 rounded-full blur-2xl"></div>
              <div className="absolute -bottom-4 -left-4 w-32 h-32 bg-pastel-purple/20 rounded-full blur-2xl"></div>
            </div>
          </div>
        </div>
      </section>

      {/* Categories Section */}
      <section className="section-padding bg-white">
        <div className="container-custom">
          <div className="text-center mb-12">
            <h2 className="section-title">Категории изделий</h2>
            <p className="text-text-medium max-w-2xl mx-auto">
              Выберите категорию, чтобы ознакомиться с коллекцией уникальных украшений
            </p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {categories.map((category, index) => (
              <Link
                key={category.id}
                href={`/catalog?category=${category.id}`}
                className="group block"
              >
                <Card className="card-romantic overflow-hidden hover:scale-105 transition-transform duration-300">
                  <div className="aspect-square relative overflow-hidden">
                    <img
                      src={category.image}
                      alt={category.name}
                      className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                  </div>
                  <CardContent className="p-4 text-center">
                    <h3 className="font-playfair text-lg text-text-rose mb-2">
                      {category.name}
                    </h3>
                    <p className="text-text-medium text-sm">
                      {category.description}
                    </p>
                  </CardContent>
                </Card>
              </Link>
            ))}
          </div>
        </div>
      </section>

      {/* Featured Products */}
      <section className="section-padding bg-rose-light">
        <div className="container-custom">
          <div className="text-center mb-12">
            <h2 className="section-title">Популярные изделия</h2>
            <p className="text-text-medium max-w-2xl mx-auto">
              Самые любимые работы наших клиентов
            </p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {featuredProducts.map((product, index) => (
              <Link
                key={product.id}
                href={`/product/${product.id}`}
                className="group block"
              >
                <Card className="card-romantic overflow-hidden product-card">
                  <div className="aspect-square relative overflow-hidden">
                    <img
                      src={product.images[0]}
                      alt={product.name}
                      className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    />
                    {product.featured && (
                      <div className="absolute top-4 right-4 bg-dusty-rose text-white px-3 py-1 rounded-full text-xs font-medium">
                        Хит
                      </div>
                    )}
                  </div>
                  <CardContent className="p-6">
                    <h3 className="font-playfair text-xl text-text-rose mb-2 group-hover:text-dusty-rose transition-colors">
                      {product.name}
                    </h3>
                    <p className="text-text-medium text-sm mb-4 line-clamp-2">
                      {product.description}
                    </p>
                    <div className="flex items-center justify-between">
                      <span className="text-lg font-semibold text-text-dark">
                        {formatPrice(product.price)}
                      </span>
                      <div className="flex items-center space-x-1">
                        <Heart className="w-4 h-4 text-dusty-rose" />
                        <span className="text-sm text-text-medium">В избранное</span>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </Link>
            ))}
          </div>
          <div className="text-center mt-12">
            <Button 
              variant="outline" 
              size="lg"
              className="border-dusty-rose text-dusty-rose hover:bg-dusty-rose hover:text-white"
              asChild
            >
              <Link href="/catalog">
                Смотреть все изделия
                <ArrowRight className="ml-2 w-5 h-5" />
              </Link>
            </Button>
          </div>
        </div>
      </section>

      {/* Reviews Section */}
      <section className="section-padding bg-white">
        <div className="container-custom">
          <div className="text-center mb-12">
            <h2 className="section-title">Отзывы клиентов</h2>
            <p className="text-text-medium max-w-2xl mx-auto">
              Что говорят о наших изделиях
            </p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            {featuredReviews.map((review, index) => (
              <Card key={review.id} className="card-romantic p-6">
                <div className="flex items-center mb-4">
                  <div className="w-12 h-12 bg-gradient-to-r from-dusty-rose to-pastel-purple rounded-full flex items-center justify-center text-white font-bold">
                    {review.author.charAt(0)}
                  </div>
                  <div className="ml-4">
                    <h4 className="font-playfair text-text-rose">{review.author}</h4>
                    <div className="flex items-center">
                      {[...Array(5)].map((_, i) => (
                        <Star
                          key={i}
                          className={`w-4 h-4 ${
                            i < review.rating ? 'text-yellow-400 fill-current' : 'text-gray-300'
                          }`}
                        />
                      ))}
                    </div>
                  </div>
                </div>
                <p className="text-text-medium italic">
                  "{review.text}"
                </p>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="section-padding bg-gradient-to-r from-dusty-rose to-pastel-purple text-white">
        <div className="container-custom text-center">
          <h2 className="text-3xl md:text-4xl font-playfair font-bold mb-6">
            Не нашли то, что искали?
          </h2>
          <p className="text-lg mb-8 max-w-2xl mx-auto">
            Я создаю украшения на заказ по вашим индивидуальным пожеланиям. 
            Свяжитесь со мной, и мы вместе создадим уникальное изделие!
          </p>
          <Button 
            size="lg" 
            variant="secondary"
            className="bg-white text-dusty-rose hover:bg-ghost-white"
            asChild
          >
            <Link href="/contact">
              Заказать индивидуальное изделие
              <ArrowRight className="ml-2 w-5 h-5" />
            </Link>
          </Button>
        </div>
      </section>
    </div>
  )
}