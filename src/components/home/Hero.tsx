'use client';

import { useState, useEffect, useRef } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { motion, useScroll, useTransform, AnimatePresence } from 'framer-motion';
import { ArrowRight, ArrowUp, Facebook, Instagram, Twitter, Linkedin } from 'lucide-react';

const heroImages = [
  {
    src: 'https://images.unsplash.com/photo-1505843513577-22bb7d21e455?auto=format&fit=crop&w=2000&q=80',
    alt: 'Futuristic luxury villa with organic concrete forms and warm interior lighting at dusk',
  },
  {
    src: 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=2000&q=80',
    alt: 'Modern architectural villa with cantilevered roof and expansive glass walls',
  },
  {
    src: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=2000&q=80',
    alt: 'Luxury home with infinity pool reflecting the evening sky',
  },
];

const annotations = [
  {
    text: 'Triple-glazed solar glass',
    position: 'top-[22%] right-[8%]',
    dotPosition: 'left-0 top-1/2',
    labelPosition: 'ml-8',
    delay: 1.2,
  },
  {
    text: 'Cantilevered concrete forms',
    position: 'top-[48%] left-[6%]',
    dotPosition: 'right-0 top-1/2',
    labelPosition: 'mr-8',
    delay: 1.5,
  },
  {
    text: 'Natural stone & warm oak interiors',
    position: 'bottom-[28%] right-[12%]',
    dotPosition: 'left-0 top-1/2',
    labelPosition: 'ml-8',
    delay: 1.8,
  },
];

const socials = [
  { Icon: Facebook, label: 'Facebook' },
  { Icon: Instagram, label: 'Instagram' },
  { Icon: Twitter, label: 'Twitter' },
  { Icon: Linkedin, label: 'LinkedIn' },
];

export default function Hero() {
  const [current, setCurrent] = useState(0);
  const containerRef = useRef<HTMLElement>(null);
  const { scrollY } = useScroll();
  const y = useTransform(scrollY, [0, 800], [0, 150]);
  const opacity = useTransform(scrollY, [0, 500], [1, 0]);

  useEffect(() => {
    const timer = setInterval(() => {
      setCurrent((prev) => (prev + 1) % heroImages.length);
    }, 6000);
    return () => clearInterval(timer);
  }, []);

  return (
    <section
      ref={containerRef}
      className="relative h-screen min-h-[700px] w-full overflow-hidden"
    >
      {/* Background images with crossfade */}
      <motion.div style={{ y }} className="absolute inset-0">
        <AnimatePresence mode="sync">
          <motion.div
            key={current}
            data-ssr-failsafe
            initial={{ opacity: 0, scale: 1.05 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: 1.5, ease: [0.16, 1, 0.3, 1] }}
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
        {/* Dark overlay for text contrast */}
        <div className="absolute inset-0 bg-gradient-to-b from-void/50 via-void/20 to-void/70" />
        <div className="absolute inset-0 bg-gradient-to-r from-void/40 via-transparent to-void/30" />
      </motion.div>

      {/* HOUSEN wordmark */}
      <motion.div
        style={{ opacity }}
        className="absolute inset-0 flex flex-col items-center justify-center px-6"
      >
        <motion.h1
          data-ssr-failsafe
          initial={{ opacity: 0, y: 60, letterSpacing: '0.1em' }}
          animate={{ opacity: 1, y: 0, letterSpacing: '-0.05em' }}
          transition={{ duration: 1.4, ease: [0.16, 1, 0.3, 1], delay: 0.2 }}
          className="font-sans font-bold uppercase text-hero text-parchment text-center leading-none"
        >
          Housen
        </motion.h1>

        {/* Explore Properties link */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 1 }}
          data-ssr-failsafe
          className="mt-8"
        >
          <Link
            href="/properties"
            className="group inline-flex items-center gap-2 text-sm font-medium uppercase tracking-[0.2em] text-parchment/80 hover:text-amber transition-colors"
          >
            Explore Properties
            <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
          </Link>
        </motion.div>
      </motion.div>

      {/* Editorial annotations */}
      {annotations.map((ann, i) => (
        <motion.div
          key={i}
          data-ssr-failsafe
          initial={{ opacity: 0, scale: 0.9 }}
          animate={{ opacity: 1, scale: 1 }}
          transition={{ duration: 0.8, delay: ann.delay, ease: [0.16, 1, 0.3, 1] }}
          className={`absolute ${ann.position} hidden md:flex items-center`}
        >
          {/* Dot */}
          <div className={`relative ${ann.dotPosition}`}>
            <div className="h-2 w-2 rounded-full bg-amber" />
            <div className="absolute inset-0 h-2 w-2 rounded-full bg-amber animate-ping opacity-60" />
          </div>
          {/* Label */}
          <div className={`${ann.labelPosition}`}>
            <div className="h-px w-12 bg-amber/40" />
            <div className="mt-2 rounded-lg border border-white/10 bg-void/40 backdrop-blur-md px-4 py-2">
              <p className="text-xs font-medium text-parchment whitespace-nowrap">
                {ann.text}
              </p>
            </div>
          </div>
        </motion.div>
      ))}

      {/* Bottom bar */}
      <div className="absolute bottom-0 left-0 right-0 z-10">
        <div className="container-wide pb-8 flex items-end justify-between">
          {/* Brand statement */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 2 }}
            data-ssr-failsafe
            className="max-w-xs hidden md:block"
          >
            <p className="text-xs leading-relaxed text-stone">
              Using stone, sand-finished concrete and natural wood, the design
              echoes the shoreline&apos;s natural textures.
            </p>
          </motion.div>

          {/* Carousel controls */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 2 }}
            data-ssr-failsafe
            className="flex items-center gap-3"
          >
            <button
              onClick={() => setCurrent((prev) => (prev - 1 + heroImages.length) % heroImages.length)}
              className="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 text-parchment hover:border-amber hover:text-amber transition-all"
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
                    i === current ? 'w-8 bg-amber' : 'w-4 bg-white/30'
                  }`}
                  aria-label={`Go to image ${i + 1}`}
                />
              ))}
            </div>
            <button
              onClick={() => setCurrent((prev) => (prev + 1) % heroImages.length)}
              className="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 text-parchment hover:border-amber hover:text-amber transition-all"
              aria-label="Next image"
            >
              <ArrowUp className="h-4 w-4 rotate-90" />
            </button>
          </motion.div>

          {/* Social icons */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 2 }}
            data-ssr-failsafe
            className="hidden md:flex gap-3"
          >
            {socials.map(({ Icon, label }) => (
              <a
                key={label}
                href="#"
                aria-label={label}
                className="flex h-9 w-9 items-center justify-center rounded-full border border-white/15 text-parchment/70 hover:border-amber hover:text-amber transition-all"
              >
                <Icon className="h-4 w-4" />
              </a>
            ))}
          </motion.div>
        </div>
      </div>

      {/* Scroll indicator */}
      <motion.div
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ delay: 2.5, duration: 1 }}
        style={{ opacity }}
        className="absolute bottom-4 left-1/2 -translate-x-1/2 hidden lg:block"
      >
        <div className="flex flex-col items-center gap-2">
          <span className="text-[10px] uppercase tracking-[0.3em] text-stone-dark">Scroll</span>
          <div className="h-12 w-px bg-gradient-to-b from-amber/50 to-transparent" />
        </div>
      </motion.div>
    </section>
  );
}
