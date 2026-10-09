'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { motion, AnimatePresence } from 'framer-motion';
import { Menu, X, Phone } from 'lucide-react';
import Logo from '@/components/ui/Logo';

const navLinks = [
  { label: 'Home', href: '/' },
  { label: 'Properties', href: '/properties' },
  { label: 'About Us', href: '/about' },
  { label: 'Services', href: '/services' },
  { label: 'Team', href: '/agents' },
  { label: 'Contact', href: '/contact' },
];

export default function Navbar() {
  const [scrolled, setScrolled] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);
  const pathname = usePathname();

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 40);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  useEffect(() => {
    setMobileOpen(false);
  }, [pathname]);

  useEffect(() => {
    document.body.style.overflow = mobileOpen ? 'hidden' : '';
    return () => {
      document.body.style.overflow = '';
    };
  }, [mobileOpen]);

  const isHome = pathname === '/';
  const solid = scrolled || !isHome;

  return (
    <>
      <header
        className={`fixed top-0 left-0 right-0 z-50 transition-all duration-500 ${
          solid
            ? 'bg-ivory/90 backdrop-blur-lg border-b border-line shadow-[0_4px_30px_-12px_rgba(11,31,51,0.15)]'
            : 'bg-transparent'
        }`}
      >
        <nav className="container-wide flex items-center justify-between py-3.5 md:py-4">
          {/* Logo */}
          <Link href="/" aria-label="Horizon Properties home" className="shrink-0">
            <Logo variant={solid ? 'dark' : 'light'} />
          </Link>

          {/* Desktop nav */}
          <div className="hidden lg:flex items-center gap-1">
            {navLinks.map((link) => {
              const active =
                pathname === link.href || (link.href !== '/' && pathname.startsWith(link.href));
              return (
                <Link
                  key={link.href}
                  href={link.href}
                  className={`rounded-full px-4 py-2 text-sm font-medium tracking-wide transition-colors duration-300 ${
                    solid
                      ? active
                        ? 'text-champagne-dark'
                        : 'text-ink/70 hover:text-ink'
                      : active
                        ? 'text-champagne-light'
                        : 'text-ivory/80 hover:text-ivory'
                  }`}
                >
                  {link.label}
                </Link>
              );
            })}
          </div>

          {/* Desktop CTA — phone */}
          <div className="hidden lg:flex items-center gap-3">
            <a
              href="tel:+15552467890"
              className={`group inline-flex items-center gap-2.5 rounded-full border px-5 py-2.5 text-sm font-medium tracking-wide transition-all duration-300 ${
                solid
                  ? 'border-ink/15 text-ink hover:border-champagne hover:text-champagne-dark'
                  : 'border-white/40 text-ivory hover:border-champagne-light hover:text-champagne-light'
              }`}
            >
              <Phone className="h-4 w-4" />
              (555) 246-7890
            </a>
          </div>

          {/* Mobile toggle */}
          <button
            className={`lg:hidden p-2 -mr-2 ${solid ? 'text-ink' : 'text-ivory'}`}
            onClick={() => setMobileOpen(true)}
            aria-label="Open menu"
          >
            <Menu className="h-6 w-6" />
          </button>
        </nav>
      </header>

      {/* Mobile menu overlay */}
      <AnimatePresence>
        {mobileOpen && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: 0.3 }}
            className="fixed inset-0 z-[60] bg-navy lg:hidden flex flex-col"
          >
            <div className="container-wide flex items-center justify-between py-3.5">
              <Link href="/" onClick={() => setMobileOpen(false)} aria-label="Horizon Properties home">
                <Logo variant="light" />
              </Link>
              <button
                className="text-ivory p-2 -mr-2"
                onClick={() => setMobileOpen(false)}
                aria-label="Close menu"
              >
                <X className="h-6 w-6" />
              </button>
            </div>
            <nav className="flex flex-col px-6 mt-10 gap-1">
              {navLinks.map((link, i) => (
                <motion.div
                  key={link.href}
                  initial={{ opacity: 0, y: 24 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.08 + i * 0.06, duration: 0.45, ease: [0.16, 1, 0.3, 1] }}
                >
                  <Link
                    href={link.href}
                    className="block py-3 text-3xl font-sans font-semibold tracking-tight text-ivory hover:text-champagne-light transition-colors border-b border-white/10"
                  >
                    {link.label}
                  </Link>
                </motion.div>
              ))}
            </nav>
            <motion.div
              initial={{ opacity: 0, y: 24 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.08 + navLinks.length * 0.06, duration: 0.45 }}
              className="mt-auto px-6 pb-10"
            >
              <a
                href="tel:+15552467890"
                className="flex items-center gap-3 text-ivory/80 text-lg"
              >
                <span className="flex h-11 w-11 items-center justify-center rounded-full border border-white/25">
                  <Phone className="h-5 w-5" />
                </span>
                (555) 246-7890
              </a>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    </>
  );
}
