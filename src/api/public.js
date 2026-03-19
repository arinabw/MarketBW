import { apiGet } from '@/api/http'

export const getCategories = async () => {
  return apiGet('/categories')
}

export const getProducts = async () => {
  return apiGet('/products')
}

export const getProductById = async (id) => {
  const products = await getProducts()
  return products.find(p => p.id === id)
}

export const getFeaturedProducts = async () => {
  const products = await getProducts()
  return products.filter(p => p.featured)
}

export const getProductsByCategory = async (category) => {
  const products = await getProducts()
  return products.filter(p => p.category === category)
}

export const getReviews = async (productId) => {
  return apiGet('/reviews', productId ? { product_id: productId } : undefined)
}

export const getFaqs = async () => {
  return apiGet('/faqs')
}
