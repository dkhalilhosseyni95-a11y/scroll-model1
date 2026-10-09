'use client';

import { useRef, useCallback, useState, useEffect } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { ArrowLeft, ArrowRight, Bed, Bath, Maximize } from 'lucide-react';
import ScrollReveal from '@/components/ui/ScrollReveal';
import { formatPrice } from '@/lib/utils';
import type { PropertyCardData } from '@/lib/types';

export default function FeaturedProperties({ properties }: { properties: PropertyCardData[] }) {
  const scrollRef = useRef<HTMLDivElement>(null);
  const [canPrev, setCanPrev] = useState(false);
  const [canNext, setCanNext] = useState(true);

  const updateArrows = useCallback(() => {
    const el = scrollRef.current;
    if (!el) return;
    setCanPrev(el.scrollLeft > 8);
    setCanNext(el.scrollLeft + el.clientWidth < el.scrollWidth - 8);
  }, []);

  useEffect(() => {
    updateArrows();
    const el = scrollRef.current;
    if (!el) return;
    el.addEventListener('scroll', updateArrows, { passive: true });
    window.addEventListener('resize', updateArrows);
    return () => {
      el.removeEventListener('scroll', updateArrows);
      window.removeEventListener('resize', updateArrows);
    };
  }, [updateArrows, properties]);

  const scrollByCards = (dir: number) => {
    const el = scrollRef.current;
    if (!el) return;
    const card = el.querySelector('[data-card]') as HTMLElement | null;
    const step = card ? card.offsetWidth + 24 : el.clientWidth * 0.8;
    el.scrollBy({ left: dir * step, behavior: 'smooth' });
  };

  // Mouse drag to scroll
  const dragState = useRef({ down: false, startX: 0, startLeft: 0, moved: false });

  const onPointerDown = (e: React.PointerEvent) => {
    const el = scrollRef.current;
    if (!el) return;
    dragState.current = { down: true, startX: e.clientX, startLeft: el.scrollLeft, moved: false };
    el.setPointerCapture(e.pointerId);
  };
  const onPointerMove = (e: React.PointerEvent) => {
    const el = scrollRef.current;
    if (!el || !dragState.current.down) return;
    const dx = e.clientX - dragState.current.startX;
    if (Math.abs(dx) > 4) dragState.current.moved = true;
    el.scrollLeft = dragState.current.startLeft - dx;
  };
  const onPointerUp = () => {
    dragState.current.down = false;
  };

  if (properties.length === 0) return null;

  return (
    <section className="py-section bg-white">
      <div className="container-wide">
        <ScrollReveal className="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-10 md:mb-14">
          <div>
            <p className="section-label mb-4">Featured</p>
            <h2 className="text-4xl md:text-5xl lg:text-6xl leading-none">
              <span className="heading-serif">Featured Properties</span>
            </h2>
          </div>
          <div className="flex items-center gap-5">
            <Link
              href="/properties"
              className="group inline-flex items-center gap-2 text-sm font-medium uppercase tracking-[0.15em] text-champagne-dark hover:text-navy transition-colors"
            >
              View All
              <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
            </Link>
            <div className="hidden md:flex gap-2">
              <button
                onClick={() => scrollByCards(-1)}
                disabled={!canPrev}
                aria-label="Previous properties"
                className="flex h-11 w-11 items-center justify-center rounded-full border border-line text-ink hover:border-champagne hover:text-champagne-dark transition-all disabled:opacity-30 disabled:cursor-not-allowed"
              >
                <ArrowLeft className="h-4 w-4" />
              </button>
              <button
                onClick={() => scrollByCards(1)}
                disabled={!canNext}
                aria-label="Next properties"
                className="flex h-11 w-11 items-center justify-center rounded-full border border-line text-ink hover:border-champagne hover:text-champagne-dark transition-all disabled:opacity-30 disabled:cursor-not-allowed"
              >
                <ArrowRight className="h-4 w-4" />
              </button>
            </div>
          </div>
        </ScrollReveal>
      </div>

      {/* Carousel — edge bleed so cards peek at the viewport edge */}
      <div
        ref={scrollRef}
        onPointerDown={onPointerDown}
        onPointerMove={onPointerMove}
        onPointerUp={onPointerUp}
        onPointerLeave={onPointerUp}
        className="flex gap-6 overflow-x-auto no-scrollbar snap-x snap-mandatory px-5 sm:px-8 lg:px-12 pb-4 select-none"
        style={{ cursor: dragState.current.down ? 'grabbing' : 'grab' }}
      >
        {properties.map((property) => (
          <Link
            key={property.id}
            href={`/properties/${property.slug}`}
            data-card
            className="snap-start shrink-0 w-[85vw] sm:w-[420px] lg:w-[440px] group block"
          >
            <article className="overflow-hidden rounded-card border border-line bg-white transition-all duration-500 hover:border-champagne/50 hover:shadow-[0_24px_60px_-30px_rgba(11,31,51,0.35)]">
              <div className="relative aspect-[4/3] overflow-hidden">
                <Image
                  src={property.images[0]}
                  alt={property.title}
                  fill
                  sizes="(max-width: 640px) 85vw, 440px"
                  className="object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-navy/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
                <div className="absolute top-4 left-4">
                  <span className="rounded-full bg-navy/85 backdrop-blur-sm px-4 py-1.5 text-sm font-semibold text-champagne-light">
                    {formatPrice(property.price, property.transactionType)}
                  </span>
                </div>
                <div className="absolute top-4 right-4">
                  <span className="rounded-full bg-white/90 backdrop-blur-sm px-3 py-1.5 text-[11px] font-medium uppercase tracking-wide text-navy">
                    {property.propertyType}
                  </span>
                </div>
              </div>
              <div className="p-5">
                <h3 className="text-lg font-semibold text-ink mb-1.5 group-hover:text-champagne-dark transition-colors">
                  {property.title}
                </h3>
                <p className="text-sm text-slate mb-4">{property.location}</p>
                <div className="flex items-center gap-4 text-sm text-slate border-t border-line pt-4">
                  <span className="flex items-center gap-1.5">
                    <Bed className="h-4 w-4 text-champagne" />
                    {property.bedrooms} Bed
                  </span>
                  <span className="flex items-center gap-1.5">
                    <Bath className="h-4 w-4 text-champagne" />
                    {property.bathrooms} Bath
                  </span>
                  <span className="flex items-center gap-1.5">
                    <Maximize className="h-4 w-4 text-champagne" />
                    {property.area.toLocaleString()} {property.areaUnit}
                  </span>
                </div>
              </div>
            </article>
          </Link>
        ))}
        {/* trailing spacer so last card snaps cleanly */}
        <div className="shrink-0 w-1" aria-hidden />
      </div>
    </section>
  );
}
