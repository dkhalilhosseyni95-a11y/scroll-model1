'use client';

import { useState, useEffect, useRef } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { motion, useScroll, useTransform, AnimatePresence } from 'framer-motion';
import { ArrowRight, ArrowUp } from 'lucide-react';

const heroImages = [
  {
    src: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=2000&q=80',
    alt: 'Luxury modern villa with infinity pool and glass walls at blue hour',
  },
  {
    src: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=2000&q=80',
    alt: 'Modern luxury home with warm interior lighting at dusk',
  },
  {
    src: 'https://images.unsplash.com/photo-1512917774083-611c87c178ce?auto=format&fit=crop&w=2000&q=80',
    alt: 'Contemporary architectural villa with large glass facades at sunset',
  },
];

export default function Hero() {
  const [current, setCurrent] = useState(0);
  const containerRef = useRef<HTMLElement>(null);
  const { scrollY } = useScroll();
  const y = useTransform(scrollY, [0, 800], [0, 120]);
  const overlayOpacity = useTransform(scrollY, [0, 600], [1, 0]);

  useEffect(() => {
    const timer = setInterval(() => {
      setCurrent((prev) => (prev + 1) % heroImages.length);
    }, 7000);
    return () => clearInterval(timer);
  }, []);

  return (
    <section
      ref={containerRef}
      className="relative h-[100svh] min-h-[640px] w-full overflow-hidden"
    >
      {/* Background images with crossfade */}
      <motion.div style={{ y }} className="absolute inset-0">
        <AnimatePresence mode="sync">
          <motion.div
            key={current}
            initial={{ opacity: 0, scale: 1.03 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: 1.6, ease: [0.16, 1, 0.3, 1] }}
            className="absolute inset-0"
          >
            <Image
              src={heroImages[current].src}
              alt={heroImages[current].alt}
              fill
              priority
              sizes="100vw"
              className="object-cover"
            />
          </motion.div>
        </AnimatePresence>
        {/* Subtle navy/blue overlay for readability */}
        <div className="absolute inset-0 bg-gradient-to-b from-navy/55 via-navy/25 to-navy/65" />
        <div className="absolute inset-0 bg-gradient-to-r from-navy/40 via-transparent to-navy/30" />
      </motion.div>

      {/* Centered content */}
      <div className="relative z-10 h-full flex flex-col items-center justify-center px-6 text-center">
        <motion.p
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.2, ease: [0.16, 1, 0.3, 1] }}
          className="section-label text-champagne-light mb-6"
        >
          Luxury Real Estate & Investments
        </motion.p>

        <motion.h1
          initial={{ opacity: 0, y: 50 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 1.1, delay: 0.35, ease: [0.16, 1, 0.3, 1] }}
          className="font-sans font-bold text-ivory text-4xl sm:text-5xl md:text-6xl lg:text-7xl leading-[1.05] max-w-4xl text-balance"
        >
          Discover Exceptional
          <br />
          Homes & Investments
        </motion.h1>

        <motion.p
          initial={{ opacity: 0, y: 30 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.9, delay: 0.6, ease: [0.16, 1, 0.3, 1] }}
          className="mt-7 max-w-xl text-base md:text-lg text-ivory/80 leading-relaxed"
        >
          Premium properties in prime locations. Find your dream home or the
          perfect investment with confidence.
        </motion.p>

        <motion.div
          initial={{ opacity: 0, y: 24 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.85, ease: [0.16, 1, 0.3, 1] }}
          className="mt-10 flex flex-col sm:flex-row items-center gap-4"
        >
          <Link href="/properties" className="btn-amber group">
            Explore Properties
            <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
          </Link>
          <Link
            href="/contact"
            className="btn-outline-light group"
          >
            Get in Touch
            <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
          </Link>
        </motion.div>
      </div>

      {/* Bottom bar — carousel controls */}
      <motion.div
        style={{ opacity: overlayOpacity }}
        className="absolute bottom-0 left-0 right-0 z-10"
      >
        <div className="container-wide pb-8 flex items-end justify-between gap-4">
          <div className="hidden md:block max-w-xs">
            <p className="text-xs leading-relaxed text-ivory/60">
              Curated architecture, considered design, and remarkable places to
              call home.
            </p>
          </div>

          <div className="flex items-center gap-3 mx-auto md:mx-0">
            <button
              onClick={() =>
                setCurrent((prev) => (prev - 1 + heroImages.length) % heroImages.length)
              }
              className="flex h-10 w-10 items-center justify-center rounded-full border border-white/25 text-ivory hover:border-champagne hover:text-champagne-light transition-all"
              aria-label="Previous image"
            >
              <ArrowUp className="h-4 w-4 rotate-[-90deg]" />
            </button>
            <div className="flex gap-1.5">
              {heroImages.map((_, i) => (
                <button
                  key={i}
                  onClick={() => setCurrent(i)}
                  className={`h-1 rounded-full transition-all ${
                    i === current ? 'w-8 bg-champagne' : 'w-4 bg-white/30'
                  }`}
                  aria-label={`Go to image ${i + 1}`}
                />
              ))}
            </div>
            <button
              onClick={() => setCurrent((prev) => (prev + 1) % heroImages.length)}
              className="flex h-10 w-10 items-center justify-center rounded-full border border-white/25 text-ivory hover:border-champagne hover:text-champagne-light transition-all"
              aria-label="Next image"
            >
              <ArrowUp className="h-4 w-4 rotate-90" />
            </button>
          </div>

          <div className="hidden md:block w-[15rem]" />
        </div>
      </motion.div>
    </section>
  );
}
