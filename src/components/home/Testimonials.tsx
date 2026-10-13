'use client';

import { useState, useEffect } from 'react';
import Image from 'next/image';
import { ArrowLeft, ArrowRight } from 'lucide-react';
import ScrollReveal from '@/components/ui/ScrollReveal';

const testimonials = [
  {
    quote:
      'HOUSEN didn\'t just find us a house — they found us a piece of architecture that has transformed how we live. Their attention to every detail, from the structural integrity to the way light moves through the space, was extraordinary.',
    name: 'Adrian Voss',
    role: 'Property Owner',
    image: 'https://images.unsplash.com/photo-1505843513577-22bb7d21e455?auto=format&fit=crop&w=800&q=80',
    portrait: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80',
  },
  {
    quote:
      'Working with HOUSEN felt like collaborating with a gallery curator. Every property they showed us had a story, a reason for being. We ended up with a home that feels like it was designed specifically for us.',
    name: 'Camille Laurent',
    role: 'Property Analyst',
    image: 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=800&q=80',
    portrait: 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=400&q=80',
  },
  {
    quote:
      'The level of service was unmatched. HOUSEN understood that buying a home of this calibre is not just a transaction — it is a life decision. They guided us with patience, expertise, and genuine care.',
    name: 'Marcus Holloway',
    role: 'Property Analyst',
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
  const peek = testimonials[(current + 1) % testimonials.length];

  return (
    <section className="bg-parchment pb-section pt-4 text-void">
      <div className="container-wide">
        {/* Header: heading left, arrows right */}
        <ScrollReveal className="mb-12 flex items-end justify-between gap-6">
          <h2 className="leading-[1.05]">
            <span className="block font-sans text-3xl font-bold tracking-tight text-void md:text-5xl lg:text-6xl">
              Our Clients
            </span>
            <span className="block font-sans text-3xl font-bold tracking-tight text-void md:text-5xl lg:text-6xl">
              Speak{' '}
              <em className="font-serif italic">Boldly.</em>
            </span>
          </h2>
          <div className="flex items-center gap-3">
            <button
              onClick={prev}
              className="flex h-11 w-11 items-center justify-center rounded-full border border-charcoal/40 text-charcoal transition-all hover:border-void hover:bg-void hover:text-parchment"
              aria-label="Previous testimonial"
            >
              <ArrowLeft className="h-5 w-5" />
            </button>
            <button
              onClick={next}
              className="flex h-11 w-11 items-center justify-center rounded-full bg-void text-parchment transition-all hover:bg-amber hover:text-void"
              aria-label="Next testimonial"
            >
              <ArrowRight className="h-5 w-5" />
            </button>
          </div>
        </ScrollReveal>

        <ScrollReveal delay={150}>
          <div className="grid grid-cols-1 items-center gap-8 lg:grid-cols-12 lg:gap-6">
            {/* Active image */}
            <div className="relative aspect-square overflow-hidden rounded-card lg:col-span-3">
              <Image
                key={active.image}
                src={active.image}
                alt={`Architectural setting — ${active.name}`}
                fill
                sizes="(max-width: 1024px) 100vw, 25vw"
                className="object-cover transition-opacity duration-700"
              />
              <div className="absolute inset-0 bg-gradient-to-t from-void/40 to-transparent" />
            </div>

            {/* Quote */}
            <div className="lg:col-span-6 lg:px-6">
              <span className="select-none font-serif text-6xl leading-none text-charcoal/30 md:text-7xl">
                &ldquo;
              </span>
              <blockquote className="mb-8 mt-2 font-serif text-lg italic leading-relaxed text-charcoal md:text-xl">
                {active.quote}
              </blockquote>
              <div className="flex items-center gap-3">
                <div className="relative h-11 w-11 overflow-hidden rounded-full">
                  <Image
                    src={active.portrait}
                    alt={active.name}
                    fill
                    sizes="44px"
                    className="object-cover"
                  />
                </div>
                <div>
                  <p className="text-sm font-semibold text-void">{active.name}</p>
                  <p className="text-xs text-stone-dark">{active.role}</p>
                </div>
              </div>
            </div>

            {/* Next-slide peek */}
            <div className="relative hidden aspect-square overflow-hidden rounded-card lg:col-span-3 lg:block">
              <Image
                src={peek.image}
                alt="Next client story"
                fill
                sizes="25vw"
                className="object-cover"
              />
              <div className="absolute inset-0 bg-gradient-to-t from-void/40 to-transparent" />
            </div>
          </div>
        </ScrollReveal>
      </div>
    </section>
  );
}
