'use client'

import Link from 'next/link'
import { Phone, Mail, MapPin, Instagram, Telegram, Vk } from 'lucide-react'

export function Footer() {
  const currentYear = new Date().getFullYear()
  const version = "v1.0.0"

  const navigation = [
    { name: 'Главная', href: '/' },
    { name: 'Каталог', href: '/catalog' },
    { name: 'Контакты', href: '/contact' },
    { name: 'FAQ', href: '/faq' },
  ]

  const socialLinks = [
    {
      name: 'Instagram',
      href: process.env.NEXT_PUBLIC_INSTAGRAM || '#',
      icon: Instagram,
    },
    {
      name: 'Telegram',
      href: process.env.NEXT_PUBLIC_TELEGRAM || '#',
      icon: Telegram,
    },
    {
      name: 'VK',
      href: process.env.NEXT_PUBLIC_VK || '#',
      icon: Vk,
    },
  ]

  return (
    <footer className="bg-gradient-to-br from-rose-light to-lavender/30 border-t border-lavender/20">
      <div className="container-custom">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 py-12">
          {/* О компании */}
          <div className="space-y-4">
            <div className="flex items-center space-x-2">
              <div className="w-8 h-8 bg-gradient-to-r from-dusty-rose to-pastel-purple rounded-full flex items-center justify-center">
                <span className="text-white font-bold text-sm">BW</span>
              </div>
              <span className="font-playfair text-xl text-text-rose">MarketBW</span>
            </div>
            <p className="text-text-medium text-sm leading-relaxed">
              Создаю уникальные украшения из бисера ручной работы с любовью и вниманием к каждой детали.
            </p>
          </div>

          {/* Навигация */}
          <div className="space-y-4">
            <h3 className="font-playfair text-lg text-text-rose">Навигация</h3>
            <ul className="space-y-2">
              {navigation.map((item) => (
                <li key={item.name}>
                  <Link
                    href={item.href}
                    className="text-text-medium hover:text-text-rose transition-colors duration-300 text-sm"
                  >
                    {item.name}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Контакты */}
          <div className="space-y-4">
            <h3 className="font-playfair text-lg text-text-rose">Контакты</h3>
            <div className="space-y-3">
              <a
                href={`tel:${process.env.NEXT_PUBLIC_CONTACT_PHONE}`}
                className="flex items-center space-x-2 text-text-medium hover:text-text-rose transition-colors text-sm"
              >
                <Phone className="w-4 h-4" />
                <span>{process.env.NEXT_PUBLIC_CONTACT_PHONE}</span>
              </a>
              <a
                href={`mailto:${process.env.NEXT_PUBLIC_CONTACT_EMAIL}`}
                className="flex items-center space-x-2 text-text-medium hover:text-text-rose transition-colors text-sm"
              >
                <Mail className="w-4 h-4" />
                <span>{process.env.NEXT_PUBLIC_CONTACT_EMAIL}</span>
              </a>
            </div>
          </div>

          {/* Социальные сети */}
          <div className="space-y-4">
            <h3 className="font-playfair text-lg text-text-rose">Социальные сети</h3>
            <div className="flex space-x-3">
              {socialLinks.map((social) => {
                const IconComponent = social.icon
                return (
                  <a
                    key={social.name}
                    href={social.href}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="w-10 h-10 bg-white/80 rounded-full flex items-center justify-center hover:bg-dusty-rose hover:text-white transition-all duration-300 shadow-soft hover:shadow-soft-lg"
                    aria-label={social.name}
                  >
                    <IconComponent className="w-5 h-5" />
                  </a>
                )
              })}
            </div>
            <p className="text-text-medium text-sm">
              Подписывайтесь, чтобы первыми узнавать о новинках и акциях!
            </p>
          </div>
        </div>

        {/* Нижняя часть футера */}
        <div className="border-t border-lavender/20 py-6">
          <div className="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <p className="text-text-medium text-sm">
              © {currentYear} MarketBW. Все права защищены.
            </p>
            <div className="flex flex-col md:flex-row items-center space-y-2 md:space-y-0 md:space-x-4">
              <p className="text-text-medium text-sm">
                Создано с ❤️ для ценителей бисерного искусства
              </p>
              <p className="text-text-medium text-xs opacity-75">
                Версия: {version}
              </p>
            </div>
          </div>
        </div>
      </div>
    </footer>
  )
}