import Link from 'next/link';
import { Facebook, Instagram, Twitter, Linkedin, MapPin, Phone, Mail } from 'lucide-react';
import Logo from '@/components/ui/Logo';
import NewsletterForm from './NewsletterForm';

const footerNav = [
  { label: 'Home', href: '/' },
  { label: 'Properties', href: '/properties' },
  { label: 'About Us', href: '/about' },
  { label: 'Services', href: '/services' },
  { label: 'Team', href: '/agents' },
  { label: 'Contact', href: '/contact' },
];

const footerCategories = [
  { label: 'Luxury Villas', href: '/properties?type=Villa' },
  { label: 'Penthouses', href: '/properties?type=Penthouse' },
  { label: 'Apartments', href: '/properties?type=Apartment' },
  { label: 'For Sale', href: '/buy' },
  { label: 'For Rent', href: '/rent' },
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
    <footer className="bg-navy text-ivory">
      <div className="container-wide pt-16 md:pt-20 pb-12">
        <div className="grid grid-cols-1 gap-12 md:grid-cols-2 lg:grid-cols-12 lg:gap-8">
          {/* Brand */}
          <div className="lg:col-span-4">
            <Logo variant="light" />
            <p className="mt-6 text-sm leading-relaxed text-ivory/60 max-w-xs">
              Horizon Properties connects discerning buyers with extraordinary
              homes and smart investments. Integrity, transparency, and client
              satisfaction are at the heart of everything we do.
            </p>
            <div className="flex gap-3 mt-7">
              {socialLinks.map(({ Icon, href, label }) => (
                <a
                  key={label}
                  href={href}
                  aria-label={label}
                  className="flex h-10 w-10 items-center justify-center rounded-full border border-white/15 text-ivory/70 hover:border-champagne hover:text-champagne-light transition-all"
                >
                  <Icon className="h-4 w-4" />
                </a>
              ))}
            </div>
          </div>

          {/* Navigation */}
          <div className="lg:col-span-2">
            <h3 className="text-xs font-medium uppercase tracking-[0.2em] text-champagne-light mb-5">
              Navigation
            </h3>
            <ul className="space-y-3">
              {footerNav.map((link) => (
                <li key={link.href}>
                  <Link
                    href={link.href}
                    className="text-sm text-ivory/60 hover:text-ivory transition-colors"
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Categories */}
          <div className="lg:col-span-2">
            <h3 className="text-xs font-medium uppercase tracking-[0.2em] text-champagne-light mb-5">
              Explore
            </h3>
            <ul className="space-y-3">
              {footerCategories.map((cat) => (
                <li key={cat.label}>
                  <Link
                    href={cat.href}
                    className="text-sm text-ivory/60 hover:text-ivory transition-colors"
                  >
                    {cat.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Contact + Newsletter */}
          <div className="lg:col-span-4">
            <h3 className="text-xs font-medium uppercase tracking-[0.2em] text-champagne-light mb-5">
              Get in Touch
            </h3>
            <ul className="space-y-3 mb-6">
              <li className="flex items-start gap-3 text-sm text-ivory/60">
                <MapPin className="h-4 w-4 mt-0.5 text-champagne shrink-0" />
                1280 Coast Avenue, Suite 400, Los Angeles, CA 90024
              </li>
              <li className="flex items-center gap-3 text-sm text-ivory/60">
                <Phone className="h-4 w-4 text-champagne shrink-0" />
                <a href="tel:+15552467890" className="hover:text-ivory transition-colors">
                  (555) 246-7890
                </a>
              </li>
              <li className="flex items-center gap-3 text-sm text-ivory/60">
                <Mail className="h-4 w-4 text-champagne shrink-0" />
                <a href="mailto:contact@horizonproperties.com" className="hover:text-ivory transition-colors">
                  contact@horizonproperties.com
                </a>
              </li>
            </ul>
            <p className="text-sm text-ivory/60 mb-3">Subscribe for new listings & insights.</p>
            <NewsletterForm />
          </div>
        </div>

        {/* Bottom bar */}
        <div className="mt-16 pt-8 border-t border-white/10 flex flex-col md:flex-row items-center justify-between gap-4">
          <p className="text-xs text-ivory/40">
            © {year} Horizon Properties. All rights reserved. — Demonstration website.
          </p>
          <div className="flex gap-6">
            <Link href="/privacy" className="text-xs text-ivory/40 hover:text-champagne-light transition-colors">
              Privacy Policy
            </Link>
            <Link href="/terms" className="text-xs text-ivory/40 hover:text-champagne-light transition-colors">
              Terms of Service
            </Link>
          </div>
        </div>
      </div>

      {/* Oversized wordmark */}
      <div className="overflow-hidden border-t border-white/10">
        <p className="font-sans font-bold uppercase tracking-tight text-center text-[clamp(3rem,16vw,16rem)] leading-[0.85] text-white/[0.04] select-none pb-3 pt-2">
          HORIZON
        </p>
      </div>
    </footer>
  );
}
