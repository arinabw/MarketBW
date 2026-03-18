/**
 * Конфигурация сайта
 */

export const env = {
  // Публичные переменные
  siteName: 'MarketBW',
  siteUrl: 'http://marketbw.ru',
  contactEmail: 'your-email@example.com',
  contactPhone: '+7 (999) 123-45-67',
  instagram: '#',
  telegram: '#',
  vk: '#',
  
  // Окружение
  nodeEnv: import.meta.env.MODE || 'development',
}
