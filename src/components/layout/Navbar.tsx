'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { motion, AnimatePresence } from 'framer-motion';
import { Menu, X } from 'lucide-react';
import Logo from '@/components/ui/Logo';

const navLinks = [
  { label: 'Home', href: '/' },
  { label: 'Properties', href: '/properties' },
  { label: 'About', href: '/about' },
  { label: 'Services', href: '/services' },
  { label: 'Agents', href: '/agents' },
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
    return () => { document.body.style.overflow = ''; };
  }, [mobileOpen]);

  const isHome = pathname === '/';

  return (
    <>
      <header
        className={`fixed top-0 left-0 right-0 z-50 transition-all duration-500 ${
          scrolled || !isHome
            ? 'bg-void/90 backdrop-blur-lg border-b border-charcoal/40'
            : 'bg-transparent'
        }`}
      >
        <nav className="container-wide flex items-center justify-between py-4 md:py-5">
          {/* Logo */}
          <Link href="/" className="text-parchment hover:text-amber transition-colors" aria-label="HOUSEN home">
            <Logo />
          </Link>

          {/* Desktop nav pill */}
          <div className="hidden lg:flex items-center gap-1 glass-pill px-2 py-1.5">
            {navLinks.map((link) => {
              const active = pathname === link.href || (link.href !== '/' && pathname.startsWith(link.href));
              return (
                <Link
                  key={link.href}
                  href={link.href}
                  className={`rounded-full px-4 py-2 text-sm font-medium tracking-wide transition-all duration-300 ${
                    active
                      ? 'bg-white/10 text-amber'
                      : 'text-stone hover:text-parchment'
                  }`}
                >
                  {link.label}
                </Link>
              );
            })}
          </div>

          {/* Desktop CTA */}
          <div className="hidden lg:block">
            <Link href="/book-viewing" className="btn-amber text-sm">
              Book a Viewing
            </Link>
          </div>

          {/* Mobile toggle */}
          <button
            className="lg:hidden text-parchment p-2 -mr-2"
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
            className="fixed inset-0 z-[60] bg-void lg:hidden"
          >
            <div className="container-wide flex items-center justify-between py-4 md:py-5">
              <Link href="/" onClick={() => setMobileOpen(false)} className="text-parchment">
                <Logo />
              </Link>
              <button
                className="text-parchment p-2 -mr-2"
                onClick={() => setMobileOpen(false)}
                aria-label="Close menu"
              >
                <X className="h-6 w-6" />
              </button>
            </div>
            <nav className="flex flex-col items-center justify-center gap-2 px-6 mt-12">
              {navLinks.map((link, i) => (
                <motion.div
                  key={link.href}
                  initial={{ opacity: 0, y: 30 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.1 + i * 0.08, duration: 0.5, ease: [0.16, 1, 0.3, 1] }}
                >
                  <Link
                    href={link.href}
                    className="text-3xl font-sans font-semibold uppercase tracking-tight text-parchment hover:text-amber transition-colors py-3"
                  >
                    {link.label}
                  </Link>
                </motion.div>
              ))}
              <motion.div
                initial={{ opacity: 0, y: 30 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.1 + navLinks.length * 0.08, duration: 0.5 }}
                className="mt-8"
              >
                <Link href="/book-viewing" className="btn-amber">
                  Book a Viewing
                </Link>
              </motion.div>
            </nav>
          </motion.div>
        )}
      </AnimatePresence>
    </>
  );
}
