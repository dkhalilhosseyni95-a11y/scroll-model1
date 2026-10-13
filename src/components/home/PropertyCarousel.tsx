'use client';

import { useEffect, useState } from 'react';
import { ArrowLeft, ArrowRight } from 'lucide-react';
import PropertyCard from '@/components/ui/PropertyCard';
import ScrollReveal from '@/components/ui/ScrollReveal';
import type { PropertyCardData } from '@/lib/types';

export default function PropertyCarousel({ properties }: { properties: PropertyCardData[] }) {
  const slides = properties.slice(0, 10);
  const [index, setIndex] = useState(0);
  const [perView, setPerView] = useState(2);

  useEffect(() => {
    const mq = window.matchMedia('(min-width: 768px)');
    const apply = () => setPerView(mq.matches ? 2 : 1);
    apply();
    mq.addEventListener('change', apply);
    return () => mq.removeEventListener('change', apply);
  }, []);

  if (slides.length === 0) return null;

  const maxIndex = Math.max(0, slides.length - perView);
  const clamped = Math.min(index, maxIndex);
  const next = () => setIndex((prev) => (prev >= maxIndex ? 0 : prev + 1));
  const prevSlide = () => setIndex((prev) => (prev <= 0 ? maxIndex : prev - 1));

  return (
    <section className="bg-parchment py-section text-void">
      <div className="container-wide">
        {/* Header: heading left, counter + arrows right */}
        <ScrollReveal className="mb-10 flex items-end justify-between gap-6">
          <h2 className="text-4xl md:text-5xl lg:text-6xl leading-none text-void">
            <span className="font-sans font-bold uppercase tracking-tight">Discover</span>{' '}
            <span className="font-serif italic">More Homes</span>
          </h2>
          <div className="flex items-center gap-3">
            <span className="mr-1 text-sm text-stone-dark">
              {clamped + 1}/{slides.length}
            </span>
            <button
              onClick={prevSlide}
              className="flex h-11 w-11 items-center justify-center rounded-full border border-charcoal/40 text-charcoal transition-all hover:border-void hover:bg-void hover:text-parchment"
              aria-label="Previous properties"
            >
              <ArrowLeft className="h-5 w-5" />
            </button>
            <button
              onClick={next}
              className="flex h-11 w-11 items-center justify-center rounded-full bg-void text-parchment transition-all hover:bg-amber hover:text-void"
              aria-label="Next properties"
            >
              <ArrowRight className="h-5 w-5" />
            </button>
          </div>
        </ScrollReveal>

        {/* Sliding track */}
        <ScrollReveal delay={150}>
          <div className="-mx-3 overflow-hidden">
            <div
              className="flex transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]"
              style={{ transform: `translateX(-${clamped * (100 / perView)}%)` }}
            >
              {slides.map((property) => (
                <div
                  key={property.id}
                  className="flex-shrink-0 px-3"
                  style={{ width: `${100 / perView}%` }}
                >
                  <PropertyCard property={property} tone="light" />
                </div>
              ))}
            </div>
          </div>
        </ScrollReveal>
      </div>
    </section>
  );
}
