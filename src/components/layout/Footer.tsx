import Link from 'next/link';
import { Facebook, Instagram, Twitter, Linkedin } from 'lucide-react';
import Logo from '@/components/ui/Logo';
import NewsletterForm from './NewsletterForm';

const footerNav = [
  { label: 'Home', href: '/' },
  { label: 'Properties', href: '/properties' },
  { label: 'About Us', href: '/about' },
  { label: 'Contact', href: '/contact' },
];

const footerCategories = [
  { label: 'Contemporary Villas', href: '/properties?type=Villa' },
  { label: 'Luxury Apartments', href: '/properties?type=Apartment' },
  { label: 'Penthouses', href: '/properties?type=Penthouse' },
  { label: 'Waterfront Homes', href: '/properties?type=Villa&location=water' },
];

const socialLinks = [
  { Icon: Twitter, href: '#', label: 'Twitter' },
  { Icon: Linkedin, href: '#', label: 'LinkedIn' },
  { Icon: Instagram, href: '#', label: 'Instagram' },
  { Icon: Facebook, href: '#', label: 'Facebook' },
];

export default function Footer() {
  const year = new Date().getFullYear();

  return (
    <footer className="bg-void border-t border-charcoal/40">
      <div className="container-wide py-16 md:py-20">
        <div className="grid grid-cols-1 gap-12 md:grid-cols-2 lg:grid-cols-4 lg:gap-8">
          {/* Brand */}
          <div className="lg:col-span-1">
            <div className="text-parchment mb-5">
              <Logo />
            </div>
            <p className="text-sm leading-relaxed text-stone max-w-xs">
              HOUSEN curates exceptional architecture and remarkable places to call
              home — a digital gallery of living art.
            </p>
            <div className="flex gap-3 mt-6">
              {socialLinks.map(({ Icon, href, label }) => (
                <a
                  key={label}
                  href={href}
                  aria-label={label}
                  className="flex h-10 w-10 items-center justify-center rounded-full border border-charcoal text-stone hover:border-amber hover:text-amber transition-all"
                >
                  <Icon className="h-4 w-4" />
                </a>
              ))}
            </div>
          </div>

          {/* Navigation */}
          <div>
            <h3 className="text-xs font-medium uppercase tracking-[0.2em] text-amber mb-5">
              Navigation
            </h3>
            <ul className="space-y-3">
              {footerNav.map((link) => (
                <li key={link.href}>
                  <Link
                    href={link.href}
                    className="text-sm text-stone hover:text-parchment transition-colors"
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
              <li>
                <Link href="/services" className="text-sm text-stone hover:text-parchment transition-colors">
                  Services
                </Link>
              </li>
              <li>
                <Link href="/agents" className="text-sm text-stone hover:text-parchment transition-colors">
                  Agents
                </Link>
              </li>
            </ul>
          </div>

          {/* Categories */}
          <div>
            <h3 className="text-xs font-medium uppercase tracking-[0.2em] text-amber mb-5">
              Categories
            </h3>
            <ul className="space-y-3">
              {footerCategories.map((cat) => (
                <li key={cat.label}>
                  <Link
                    href={cat.href}
                    className="text-sm text-stone hover:text-parchment transition-colors"
                  >
                    {cat.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Newsletter */}
          <div>
            <h3 className="text-xs font-medium uppercase tracking-[0.2em] text-amber mb-5">
              Newsletter
            </h3>
            <p className="text-sm text-stone mb-4">
              Subscribe for new listings and architectural insights.
            </p>
            <NewsletterForm />
            <div className="mt-6 space-y-2">
              <p className="text-sm text-stone">
                <span className="text-stone-dark">Email:</span>{' '}
                <a href="mailto:contact@housen.com" className="hover:text-amber transition-colors">
                  contact@housen.com
                </a>
              </p>
              <p className="text-sm text-stone">
                <span className="text-stone-dark">Phone:</span>{' '}
                <span className="text-stone-dark">Available upon request</span>
              </p>
            </div>
          </div>
        </div>

        {/* Bottom bar */}
        <div className="mt-16 pt-8 border-t border-charcoal/40 flex flex-col md:flex-row items-center justify-between gap-4">
          <p className="text-xs text-stone-dark">
            © {year} HOUSEN. All rights reserved. — Demonstration website.
          </p>
          <div className="flex gap-6">
            <Link href="/privacy" className="text-xs text-stone-dark hover:text-amber transition-colors">
              Privacy Policy
            </Link>
            <Link href="/terms" className="text-xs text-stone-dark hover:text-amber transition-colors">
              Terms of Service
            </Link>
          </div>
        </div>
      </div>

      {/* Oversized wordmark */}
      <div className="overflow-hidden border-t border-charcoal/20">
        <p className="font-sans font-bold uppercase tracking-tight text-center text-[clamp(4rem,18vw,18rem)] leading-none text-charcoal/30 select-none pb-4">
          HOUSEN
        </p>
      </div>
    </footer>
  );
}
