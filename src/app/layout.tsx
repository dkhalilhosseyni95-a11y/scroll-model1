import type { Metadata } from 'next';
import './globals.css';
import ConditionalLayout from '@/components/layout/ConditionalLayout';
import Toaster from '@/components/ui/Toaster';
import Providers from '@/components/layout/Providers';

export const metadata: Metadata = {
  title: {
    default: 'HOUSEN — Luxury Real Estate',
    template: '%s — HOUSEN',
  },
  description:
    'HOUSEN curates exceptional architecture, considered design, and remarkable places to call home. Discover luxury villas, penthouses, and waterfront estates.',
  keywords: ['luxury real estate', 'villas', 'penthouse', 'waterfront homes', 'architecture'],
  openGraph: {
    title: 'HOUSEN — Luxury Real Estate',
    description: 'Exceptional architecture, considered design, and remarkable places to call home.',
    type: 'website',
    siteName: 'HOUSEN',
  },
  robots: { index: true, follow: true },
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
        <link
          href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&display=swap"
          rel="stylesheet"
        />
      </head>
      <body className="min-h-screen flex flex-col">
        <Providers>
          <ConditionalLayout>{children}</ConditionalLayout>
          <Toaster />
        </Providers>
      </body>
    </html>
  );
}
