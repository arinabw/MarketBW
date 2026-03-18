import type { Metadata } from 'next'
import { Inter, Playfair_Display, Lato } from 'next/font/google'
import './globals.css'
import { Header } from '@/components/layout/header'
import { Footer } from '@/components/layout/footer'

const inter = Inter({ subsets: ['latin'] })
const playfair = Playfair_Display({
  subsets: ['latin'],
  variable: '--font-playfair'
})
const lato = Lato({
  weight: ['300', '400', '700'],
  subsets: ['latin'],
  variable: '--font-lato'
})

export const metadata: Metadata = {
  title: 'MarketBW - Украшения из бисера ручной работы',
  description: 'Уникальные украшения из бисера ручной работы. Браслеты, колье, серьги и броши, созданные с любовью и вниманием к деталям.',
  keywords: 'бисер, украшения, браслеты, колье, серьги, броши, ручная работа, бижутерия',
  authors: [{ name: 'MarketBW' }],
  openGraph: {
    title: 'MarketBW - Украшения из бисера ручной работы',
    description: 'Уникальные украшения из бисера ручной работы',
    type: 'website',
    locale: 'ru_RU',
  },
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="ru" className={`${playfair.variable} ${lato.variable}`}>
      <body className={inter.className}>
        <Header />
        <main>{children}</main>
        <Footer />
      </body>
    </html>
  )
}