'use client';

import Link from 'next/link';
import Image from 'next/image';
import { motion } from 'framer-motion';
import { ArrowUpRight, Facebook, Instagram, Twitter, Linkedin } from 'lucide-react';

const heroImage = {
  src: 'https://images.unsplash.com/photo-1505843513577-22bb7d21e455?auto=format&fit=crop&w=2000&q=80',
  alt: 'Futuristic luxury villa with organic concrete forms and warm interior lighting at dusk',
};

const annotations = [
  { text: 'Using stone, sand-finished concrete and natural wood, the design echoes the shoreline\u2019s natural textures.', position: 'top-[30%] right-[4%] max-w-[230px]', dot: 'bottom-0 left-6', delay: 1.6 },
  { text: 'Using stone, sand-finished concrete and natural wood, the design echoes the shoreline\u2019s natural textures.', position: 'top-[48%] left-1/2 -translate-x-1/2 max-w-[220px]', dot: 'top-0 right-6', delay: 1.9 },
  { text: 'Using stone, sand-finished concrete and natural wood, the design echoes the shoreline\u2019s natural textures.', position: 'top-[62%] left-[3%] max-w-[230px]', dot: 'bottom-0 left-6', delay: 2.2 },
];

const socials = [
  { Icon: Facebook, label: 'Facebook' },
  { Icon: Instagram, label: 'Instagram' },
  { Icon: Twitter, label: 'Twitter' },
  { Icon: Linkedin, label: 'LinkedIn' },
];

export default function Hero() {
  return (
    <section className="relative h-screen min-h-[720px] w-full overflow-hidden bg-void">
      {/* Giant wordmark — sits behind the building image */}
      <div className="absolute inset-x-0 top-[9%] z-0 px-4 text-center">
        <motion.h1
          data-ssr-failsafe
          initial={{ opacity: 0, y: 40 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 1.4, ease: [0.16, 1, 0.3, 1], delay: 0.2 }}
          className="font-sans font-bold uppercase text-hero text-parchment leading-none select-none"
        >
          Housen
        </motion.h1>
      </div>

      {/* Building image — overlaps the bottom of the wordmark */}
      <motion.div
        data-ssr-failsafe
        initial={{ opacity: 0, scale: 1.03 }}
        animate={{ opacity: 1, scale: 1 }}
        transition={{ duration: 1.6, ease: [0.16, 1, 0.3, 1], delay: 0.4 }}
        className="absolute inset-x-0 top-[16%] bottom-0 z-10"
      >
        <Image
          src={heroImage.src}
          alt={heroImage.alt}
          fill
          priority
          sizes="100vw"
          className="object-cover object-center"
        />
        {/* Blend the image top into the void background */}
        <div className="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-void to-transparent" />
      </motion.div>

      {/* Explore Properties link */}
      <motion.div
        data-ssr-failsafe
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.8, delay: 1.2 }}
        className="absolute left-6 md:left-10 lg:left-16 top-[38%] z-20"
      >
        <Link
          href="/properties"
          className="group inline-flex items-center gap-2 border-b border-parchment/40 pb-1 text-sm font-medium text-parchment hover:text-amber hover:border-amber transition-colors"
        >
          Explore Properties
          <ArrowUpRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
        </Link>
      </motion.div>

      {/* Editorial annotation callouts */}
      {annotations.map((ann, i) => (
        <motion.div
          key={i}
          data-ssr-failsafe
          initial={{ opacity: 0, scale: 0.92 }}
          animate={{ opacity: 1, scale: 1 }}
          transition={{ duration: 0.8, delay: ann.delay, ease: [0.16, 1, 0.3, 1] }}
          className={`absolute z-20 hidden lg:block ${ann.position}`}
        >
          <div className="glass-pill px-4 py-2.5">
            <p className="text-[11px] leading-relaxed text-parchment">{ann.text}</p>
          </div>
          {/* Connector line + dot */}
          <div className={`absolute h-px w-16 bg-gradient-to-r from-white/40 to-amber/60 ${i === 1 ? 'top-0 right-6 rotate-[35deg] origin-top-right' : 'bottom-0 left-6 -rotate-[25deg] origin-bottom-left'}`} />
          <div className={`absolute h-2 w-2 rounded-full bg-amber ${ann.dot}`}>
            <div className="absolute inset-0 rounded-full bg-amber animate-ping opacity-60" />
          </div>
        </motion.div>
      ))}

      {/* Bottom bar */}
      <div className="absolute bottom-0 left-0 right-0 z-20">
        <div className="container-wide pb-8 flex items-end justify-between gap-6">
          {/* Brand statement */}
          <motion.p
            data-ssr-failsafe
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 2.4 }}
            className="text-xs md:text-sm font-medium uppercase leading-relaxed tracking-wide text-parchment"
          >
            We build<br />modern<br />houses<br />in USA
          </motion.p>

          {/* Social icons in a glass pill */}
          <motion.div
            data-ssr-failsafe
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 2.4 }}
            className="glass-pill flex items-center gap-2 px-2.5 py-2"
          >
            {socials.map(({ Icon, label }) => (
              <a
                key={label}
                href="#"
                aria-label={label}
                className="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-parchment/80 hover:bg-amber hover:text-void transition-all"
              >
                <Icon className="h-4 w-4" />
              </a>
            ))}
          </motion.div>
        </div>
      </div>
    </section>
  );
}
