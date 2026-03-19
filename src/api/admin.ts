import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:3000/api'

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
})

// Аутентификация
export const login = async (username: string, password: string) => {
  const response = await api.post('/login', { username, password })
  return response.data
}

export const logout = async () => {
  const response = await api.post('/logout')
  return response.data
}

// Категории
export const getCategories = async () => {
  const response = await api.get('/categories')
  return response.data
}

export const createCategory = async (category: { name: string; description: string; image: string }) => {
  const response = await api.post('/categories', category)
  return response.data
}

export const updateCategory = async (id: string, category: { name: string; description: string; image: string }) => {
  const response = await api.put(`/categories/${id}`, category)
  return response.data
}

export const deleteCategory = async (id: string) => {
  const response = await api.delete(`/categories/${id}`)
  return response.data
}

// Товары
export const getProducts = async () => {
  const response = await api.get('/products')
  return response.data
}

export const createProduct = async (product: {
  name: string
  description: string
  price: number
  category: string
  images: string[]
  materials: string[]
  size?: string
  technique: string
  inStock: boolean
  featured?: boolean
}) => {
  const response = await api.post('/products', product)
  return response.data
}

export const updateProduct = async (id: string, product: {
  name: string
  description: string
  price: number
  category: string
  images: string[]
  materials: string[]
  size?: string
  technique: string
  inStock: boolean
  featured?: boolean
}) => {
  const response = await api.put(`/products/${id}`, product)
  return response.data
}

export const deleteProduct = async (id: string) => {
  const response = await api.delete(`/products/${id}`)
  return response.data
}

export default api
