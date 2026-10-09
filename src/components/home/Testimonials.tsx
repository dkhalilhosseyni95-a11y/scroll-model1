'use client';

import { useState, useEffect } from 'react';
import Image from 'next/image';
import { ChevronLeft, ChevronRight, Quote } from 'lucide-react';
import ScrollReveal from '@/components/ui/ScrollReveal';

const testimonials = [
  {
    quote:
      'HOUSEN didn\'t just find us a house — they found us a piece of architecture that has transformed how we live. Their attention to every detail, from the structural integrity to the way light moves through the space, was extraordinary.',
    name: 'Adrian Voss',
    role: 'Property Owner, Malibu',
    image: 'https://images.unsplash.com/photo-1505843513577-22bb7d21e455?auto=format&fit=crop&w=800&q=80',
    portrait: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80',
  },
  {
    quote:
      'Working with HOUSEN felt like collaborating with a gallery curator. Every property they showed us had a story, a reason for being. We ended up with a home that feels like it was designed specifically for us.',
    name: 'Camille Laurent',
    role: 'Interior Designer, New York',
    image: 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=800&q=80',
    portrait: 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=400&q=80',
  },
  {
    quote:
      'The level of service was unmatched. HOUSEN understood that buying a home of this calibre is not just a transaction — it is a life decision. They guided us with patience, expertise, and genuine care.',
    name: 'Marcus Holloway',
    role: 'Investor, Miami',
    image: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80',
    portrait: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80',
  },
];

export default function Testimonials() {
  const [current, setCurrent] = useState(0);

  useEffect(() => {
    const timer = setInterval(() => {
      setCurrent((prev) => (prev + 1) % testimonials.length);
    }, 8000);
    return () => clearInterval(timer);
  }, []);

  const next = () => setCurrent((prev) => (prev + 1) % testimonials.length);
  const prev = () => setCurrent((prev) => (prev - 1 + testimonials.length) % testimonials.length);
  const active = testimonials[current];

  return (
    <section className="py-section">
      <div className="container-wide">
        <ScrollReveal className="mb-12 text-center">
          <p className="section-label mb-4">Testimonials</p>
          <h2 className="text-4xl md:text-5xl lg:text-6xl leading-none">
            <span className="heading-sans">Our Clients</span>{' '}
            <span className="heading-serif">Speak Boldly.</span>
          </h2>
        </ScrollReveal>

        <ScrollReveal delay={150}>
          <div className="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-12 items-center">
            {/* Image */}
            <div className="lg:col-span-2 relative aspect-[4/5] rounded-card overflow-hidden">
              <Image
                src={active.image}
                alt={`Architectural setting — ${active.name}`}
                fill
                sizes="(max-width: 1024px) 100vw, 40vw"
                className="object-cover transition-opacity duration-700"
                key={active.image}
              />
              <div className="absolute inset-0 bg-gradient-to-t from-void/50 to-transparent" />
            </div>

            {/* Quote */}
            <div className="lg:col-span-3">
              <Quote className="h-10 w-10 text-amber mb-6" />
              <blockquote className="font-serif italic text-2xl md:text-3xl lg:text-4xl leading-relaxed text-parchment mb-8">
                {active.quote}
              </blockquote>
              <div className="flex items-center gap-4">
                <div className="relative h-14 w-14 rounded-full overflow-hidden border-2 border-amber/30">
                  <Image
                    src={active.portrait}
                    alt={active.name}
                    fill
                    sizes="56px"
                    className="object-cover"
                  />
                </div>
                <div>
                  <p className="font-semibold text-parchment">{active.name}</p>
                  <p className="text-sm text-stone-dark">{active.role}</p>
                </div>
                <p className="text-xs text-stone-dark/60 ml-4 italic">
                  — Demonstration testimonial
                </p>
              </div>

              {/* Controls */}
              <div className="flex items-center gap-4 mt-10">
                <button
                  onClick={prev}
                  className="flex h-11 w-11 items-center justify-center rounded-full border border-charcoal text-stone hover:border-amber hover:text-amber transition-all"
                  aria-label="Previous testimonial"
                >
                  <ChevronLeft className="h-5 w-5" />
                </button>
                <div className="flex gap-2">
                  {testimonials.map((_, i) => (
                    <button
                      key={i}
                      onClick={() => setCurrent(i)}
                      className={`h-1.5 rounded-full transition-all ${
                        i === current ? 'w-8 bg-amber' : 'w-4 bg-charcoal'
                      }`}
                      aria-label={`Testimonial ${i + 1}`}
                    />
                  ))}
                </div>
                <button
                  onClick={next}
                  className="flex h-11 w-11 items-center justify-center rounded-full border border-charcoal text-stone hover:border-amber hover:text-amber transition-all"
                  aria-label="Next testimonial"
                >
                  <ChevronRight className="h-5 w-5" />
                </button>
              </div>
            </div>
          </div>
        </ScrollReveal>
      </div>
    </section>
  );
}
