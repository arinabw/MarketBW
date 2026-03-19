/**
 * Типы каталога для фронта и для компиляции server-build/db.ts.
 * Не импортировать сюда Node-модули — файл безопасен для Vite.
 */

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

export interface User {
  id: string
  username: string
  password_hash: string
  created_at: string
}

/** Данные формы товара в админке (camelCase, как в AdminProducts.vue) */
export interface AdminProductDraft {
  id: string
  name: string
  description: string
  price: number
  category: string
  images: string[]
  materials: string[]
  size?: string
  technique: string
  inStock: boolean
  featured: boolean
}
