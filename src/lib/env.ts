/**
 * Безопасный доступ к переменным окружения с значениями по умолчанию
 */

export const env = {
  // Публичные переменные (доступны на клиенте)
  siteName: import.meta.env.VITE_SITE_NAME || 'MarketBW',
  siteUrl: import.meta.env.VITE_SITE_URL || 'https://your-domain.com',
  contactEmail: import.meta.env.VITE_CONTACT_EMAIL || 'your-email@example.com',
  contactPhone: import.meta.env.VITE_CONTACT_PHONE || '+7 (999) 123-45-67',
  instagram: import.meta.env.VITE_INSTAGRAM || '#',
  telegram: import.meta.env.VITE_TELEGRAM || '#',
  vk: import.meta.env.VITE_VK || '#',
  
  // Серверные переменные (только на сервере)
  nodeEnv: import.meta.env.MODE || 'development',
}
