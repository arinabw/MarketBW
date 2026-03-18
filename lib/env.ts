/**
 * Безопасный доступ к переменным окружения с значениями по умолчанию
 */

export const env = {
  // Публичные переменные (доступны на клиенте)
  siteName: process.env.NEXT_PUBLIC_SITE_NAME || 'MarketBW',
  siteUrl: process.env.NEXT_PUBLIC_SITE_URL || 'https://your-domain.com',
  contactEmail: process.env.NEXT_PUBLIC_CONTACT_EMAIL || 'your-email@example.com',
  contactPhone: process.env.NEXT_PUBLIC_CONTACT_PHONE || '+7 (999) 123-45-67',
  instagram: process.env.NEXT_PUBLIC_INSTAGRAM || '#',
  telegram: process.env.NEXT_PUBLIC_TELEGRAM || '#',
  vk: process.env.NEXT_PUBLIC_VK || '#',
  
  // Серверные переменные (только на сервере)
  nodeEnv: process.env.NODE_ENV || 'development',
}
