export interface Product {
  id: string
  name: string
  description: string
  price: number
  category: 'bracelet' | 'necklace' | 'earrings' | 'brooch'
  images: string[]
  materials: string[]
  size?: string
  technique: string
  inStock: boolean
  featured?: boolean
  createdAt: Date
}

export interface Category {
  id: string
  name: string
  description: string
  image: string
}

export interface Review {
  id: string
  author: string
  rating: number
  text: string
  date: Date
  productId?: string
}

export interface FAQ {
  id: string
  question: string
  answer: string
  category: 'order' | 'care' | 'materials' | 'shipping'
}

// Моковые данные для демонстрации
export const categories: Category[] = [
  {
    id: 'bracelets',
    name: 'Браслеты',
    description: 'Элегантные браслеты из бисера ручной работы',
    image: '/images/categories/bracelets.jpg'
  },
  {
    id: 'necklaces',
    name: 'Колье',
    description: 'Уникальные колье и ожерелья из бисера',
    image: '/images/categories/necklaces.jpg'
  },
  {
    id: 'earrings',
    name: 'Серьги',
    description: 'Нежные серьги из бисера для любого образа',
    image: '/images/categories/earrings.jpg'
  },
  {
    id: 'brooches',
    name: 'Броши',
    description: 'Декоративные броши из бисера ручной работы',
    image: '/images/categories/brooches.jpg'
  }
]

export const products: Product[] = [
  {
    id: '1',
    name: 'Розовый браслет "Весенняя нежность"',
    description: 'Изящный браслет из бисера нежно-розовых оттенков, созданный с любовью и вниманием к деталям. Идеально дополнит ваш романтичный образ.',
    price: 2500,
    category: 'bracelet',
    images: [
      '/images/products/bracelet-1-1.svg',
      '/images/products/bracelet-1-2.svg',
      '/images/products/bracelet-1-3.svg'
    ],
    materials: ['Чешский бисер', 'Нитки мулине', 'Застежка из ювелирного сплава'],
    size: 'Обхват 16-18 см',
    technique: 'Монастырское плетение',
    inStock: true,
    featured: true,
    createdAt: new Date('2024-01-15')
  },
  {
    id: '2',
    name: 'Колье "Лавандовые мечты"',
    description: 'Элегантное колье в лавандовых тонах, созданное техникой мозаичного плетения. Станет украшением вашего вечернего образа.',
    price: 4500,
    category: 'necklace',
    images: [
      '/images/products/necklace-1-1.svg',
      '/images/products/necklace-1-2.svg'
    ],
    materials: ['Японский бисер TOHO', 'Серебряная фурнитура'],
    size: 'Длина 45 см',
    technique: 'Мозаичное плетение',
    inStock: true,
    featured: true,
    createdAt: new Date('2024-01-20')
  },
  {
    id: '3',
    name: 'Серьги "Нежные капли"',
    description: 'Лёгкие серьги из бисера в форме капель. Идеальны для повседневного ношения и особых случаев.',
    price: 1800,
    category: 'earrings',
    images: [
      '/images/products/earrings-1-1.svg',
      '/images/products/earrings-1-2.svg'
    ],
    materials: ['Бисер PRECIOSA', 'Серебряные крючки'],
    size: 'Длина 4 см',
    technique: 'Кирпичное плетение',
    inStock: true,
    featured: false,
    createdAt: new Date('2024-02-01')
  },
  {
    id: '4',
    name: 'Брошь "Ромашковое поле"',
    description: 'Милая брошь в виде ромашки, созданная с использованием различных техник бисероплетения. Отличный подарок для близких.',
    price: 2200,
    category: 'brooch',
    images: [
      '/images/products/brooch-1-1.svg',
      '/images/products/brooch-1-2.svg'
    ],
    materials: ['Бисер разных размеров', 'Фетровая основа', 'Застежка-булавка'],
    size: 'Диаметр 5 см',
    technique: 'Комбинированная техника',
    inStock: true,
    featured: false,
    createdAt: new Date('2024-02-10')
  },
  {
    id: '5',
    name: 'Браслет "Морская волна"',
    description: 'Свежий браслет в синих и бирюзовых тонах, напоминающий о морских просторах. Создан техникой сеточного плетения.',
    price: 2800,
    category: 'bracelet',
    images: [
      '/images/products/bracelet-2-1.svg',
      '/images/products/bracelet-2-2.svg'
    ],
    materials: ['Бисер разных оттенков синего', 'Эластичная нить'],
    size: 'Обхват 17-19 см',
    technique: 'Сеточное плетение',
    inStock: true,
    featured: true,
    createdAt: new Date('2024-02-15')
  },
  {
    id: '6',
    name: 'Колье "Винтажная роза"',
    description: 'Роскошное колье в винтажном стиле с центральным элементом в виде розы. Создано для особенных случаев.',
    price: 6500,
    category: 'necklace',
    images: [
      '/images/products/necklace-2-1.svg',
      '/images/products/necklace-2-2.svg',
      '/images/products/necklace-2-3.svg'
    ],
    materials: ['Бисер PRECIOSA', 'Россыпи кристаллов', 'Бронзовая фурнитура'],
    size: 'Длина 50 см',
    technique: 'Объемное плетение',
    inStock: false,
    featured: true,
    createdAt: new Date('2024-02-20')
  }
]

export const reviews: Review[] = [
  {
    id: '1',
    author: 'Анна Петрова',
    rating: 5,
    text: 'Заказала браслет "Весенняя нежность" - просто восторг! Очень качественная работа, бисер ровный, застежка надежная. Спасибо!',
    date: new Date('2024-01-25'),
    productId: '1'
  },
  {
    id: '2',
    author: 'Мария Соколова',
    rating: 5,
    text: 'Колье "Лавандовые мечты" превзошло все мои ожидания! Цвета очень нежные, работа ювелирная. Получила много комплиментов.',
    date: new Date('2024-02-05'),
    productId: '2'
  },
  {
    id: '3',
    author: 'Елена Иванова',
    rating: 5,
    text: 'Серьги "Нежные капли" - мои любимые! Лёгкие, красивые, удобные. Ношу почти каждый день. Спасибо за прекрасную работу!',
    date: new Date('2024-02-12'),
    productId: '3'
  }
]

export const faqs: FAQ[] = [
  {
    id: '1',
    question: 'Как сделать заказ?',
    answer: 'Для заказа выберите понравившееся изделие, нажмите кнопку "Заказать" и заполните форму. Я свяжусь с вами в течение 24 часов для подтверждения заказа.',
    category: 'order'
  },
  {
    id: '2',
    question: 'Какие способы оплаты доступны?',
    answer: 'Доступны следующие способы оплаты: банковская карта, перевод на карту, электронные кошельки. Оплата возможна как предоплата, так и при получении.',
    category: 'order'
  },
  {
    id: '3',
    question: 'Как долго выполняется заказ?',
    answer: 'Если изделие есть в наличии, отправка происходит в течение 1-2 дней. Если изделие нужно изготовить, срок выполнения - 5-7 дней.',
    category: 'shipping'
  },
  {
    id: '4',
    question: 'Как осуществляется доставка?',
    answer: 'Доставка осуществляется Почтой России, СДЭК или другими транспортными компаниями по вашему выбору. Стоимость доставки рассчитывается индивидуально.',
    category: 'shipping'
  },
  {
    id: '5',
    question: 'Как ухаживать за изделиями из бисера?',
    answer: 'Изделия из бисера нужно беречь от влаги, прямых солнечных лучей и механических повреждений. Храните в сухом месте, вдали от косметики и парфюмерии.',
    category: 'care'
  },
  {
    id: '6',
    question: 'Можно ли мыть изделия из бисера?',
    answer: 'Мыть изделия из бисера не рекомендуется. При необходимости можно аккуратно протереть влажной салфеткой и сразу высушить.',
    category: 'care'
  },
  {
    id: '7',
    question: 'Какой бисер используется в работах?',
    answer: 'В работах используется только качественный бисер: чешский PRECIOSA, японский TOHO, Miyuki. Это гарантирует долговечность и яркость изделий.',
    category: 'materials'
  },
  {
    id: '8',
    question: 'Можно ли заказать изделие по индивидуальному заказу?',
    answer: 'Да, я принимаю индивидуальные заказы. Свяжитесь со мной, и мы обсудим ваши пожелания по цвету, размеру и дизайну.',
    category: 'order'
  }
]

// Вспомогательные функции
export function getProductsByCategory(category: string): Product[] {
  return products.filter(product => product.category === category)
}

export function getFeaturedProducts(): Product[] {
  return products.filter(product => product.featured)
}

export function getProductById(id: string): Product | undefined {
  return products.find(product => product.id === id)
}

export function getReviewsByProductId(productId: string): Review[] {
  return reviews.filter(review => review.productId === productId)
}

export function getFAQsByCategory(category: string): FAQ[] {
  return faqs.filter(faq => faq.category === category)
}