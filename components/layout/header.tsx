'use client'

import Link from 'next/link'
import { useState } from 'react'
import { Menu, X, ShoppingBag, Phone, Mail } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { env } from '@/lib/env'

export function Header() {
  const [isMenuOpen, setIsMenuOpen] = useState(false)

  const navigation = [
    { name: 'Главная', href: '/' },
    { name: 'Каталог', href: '/catalog' },
    { name: 'Контакты', href: '/contact' },
    { name: 'FAQ', href: '/faq' },
  ]

  return (
    <header className="bg-white/80 backdrop-blur-md shadow-soft sticky top-0 z-50">
      <div className="container-custom">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <Link href="/" className="flex items-center space-x-2">
            <div className="w-8 h-8 bg-gradient-to-r from-dusty-rose to-pastel-purple rounded-full flex items-center justify-center">
              <span className="text-white font-bold text-sm">BW</span>
            </div>
            <span className="font-playfair text-xl text-text-rose">{env.siteName}</span>
          </Link>

          {/* Desktop Navigation */}
          <nav className="hidden md:flex items-center space-x-8">
            {navigation.map((item) => (
              <Link
                key={item.name}
                href={item.href}
                className="nav-link text-text-dark hover:text-text-rose transition-colors duration-300"
              >
                {item.name}
              </Link>
            ))}
          </nav>

          {/* Contact Info */}
          <div className="hidden lg:flex items-center space-x-4">
            <a
              href={`tel:${env.contactPhone}`}
              className="flex items-center space-x-1 text-text-medium hover:text-text-rose transition-colors"
            >
              <Phone className="w-4 h-4" />
              <span className="text-sm">{env.contactPhone}</span>
            </a>
            <a
              href={`mailto:${env.contactEmail}`}
              className="flex items-center space-x-1 text-text-medium hover:text-text-rose transition-colors"
            >
              <Mail className="w-4 h-4" />
              <span className="text-sm hidden xl:inline">{env.contactEmail}</span>
            </a>
          </div>

          {/* Mobile menu button */}
          <Button
            variant="ghost"
            size="icon"
            className="md:hidden"
            onClick={() => setIsMenuOpen(!isMenuOpen)}
          >
            {isMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
          </Button>
        </div>

        {/* Mobile Navigation */}
        {isMenuOpen && (
          <div className="md:hidden">
            <div className="px-2 pt-2 pb-3 space-y-1 bg-white/95 backdrop-blur-md rounded-lg mt-2">
              {navigation.map((item) => (
                <Link
                  key={item.name}
                  href={item.href}
                  className="block px-3 py-2 text-text-dark hover:text-text-rose hover:bg-rose-light rounded-md transition-colors duration-300"
                  onClick={() => setIsMenuOpen(false)}
                >
                  {item.name}
                </Link>
              ))}
              <div className="pt-4 pb-2 border-t border-lavender/20">
                <a
                  href={`tel:${env.contactPhone}`}
                  className="flex items-center space-x-2 px-3 py-2 text-text-medium hover:text-text-rose transition-colors"
                >
                  <Phone className="w-4 h-4" />
                  <span>{env.contactPhone}</span>
                </a>
                <a
                  href={`mailto:${env.contactEmail}`}
                  className="flex items-center space-x-2 px-3 py-2 text-text-medium hover:text-text-rose transition-colors"
                >
                  <Mail className="w-4 h-4" />
                  <span>{env.contactEmail}</span>
                </a>
              </div>
            </div>
          </div>
        )}
      </div>
    </header>
  )
}