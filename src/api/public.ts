import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_URL || '/api'

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
})

// Типы данных (совпадают с catalog-types.ts)
export interface Category {
  id: string
  name: string
  description: string
  image: string
  created_at: string
}

export interface Product {
  id: string
  name: string
  description: string
  price: number
  category: string
  images: string[]
  materials: string[]
  size?: string
  technique: string
  in_stock: boolean
  featured: boolean
  created_at: string
}

// Категории
export const getCategories = async (): Promise<Category[]> => {
  const response = await api.get('/categories')
  return response.data
}

// Товары
export const getProducts = async (): Promise<Product[]> => {
  const response = await api.get('/products')
  return response.data
}

export const getProductById = async (id: string): Promise<Product | undefined> => {
  const products = await getProducts()
  return products.find(p => p.id === id)
}

export const getFeaturedProducts = async (): Promise<Product[]> => {
  const products = await getProducts()
  return products.filter(p => p.featured)
}

export const getProductsByCategory = async (category: string): Promise<Product[]> => {
  const products = await getProducts()
  return products.filter(p => p.category === category)
}

export default api
