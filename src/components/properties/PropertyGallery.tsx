'use client';

import { useState, useEffect, useCallback } from 'react';
import Image from 'next/image';
import { ChevronLeft, ChevronRight, X, ZoomIn } from 'lucide-react';

export default function PropertyGallery({ images, alt }: { images: string[]; alt: string }) {
  const [current, setCurrent] = useState(0);
  const [lightbox, setLightbox] = useState(false);

  const next = useCallback(() => setCurrent((p) => (p + 1) % images.length), [images.length]);
  const prev = useCallback(() => setCurrent((p) => (p - 1 + images.length) % images.length), [images.length]);

  useEffect(() => {
    if (!lightbox) return;
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape') setLightbox(false);
      if (e.key === 'ArrowRight') next();
      if (e.key === 'ArrowLeft') prev();
    };
    window.addEventListener('keydown', onKey);
    document.body.style.overflow = 'hidden';
    return () => {
      window.removeEventListener('keydown', onKey);
      document.body.style.overflow = '';
    };
  }, [lightbox, next, prev]);

  return (
    <>
      {/* Main gallery */}
      <div className="relative aspect-[16/10] md:aspect-[16/9] rounded-card overflow-hidden bg-charcoal">
        <Image
          src={images[current]}
          alt={`${alt} — image ${current + 1}`}
          fill
          sizes="(max-width: 768px) 100vw, 66vw"
          className="object-cover"
          priority
        />

        {/* Controls */}
        {images.length > 1 && (
          <>
            <button
              onClick={prev}
              className="absolute left-4 top-1/2 -translate-y-1/2 flex h-11 w-11 items-center justify-center rounded-full bg-void/60 backdrop-blur-md text-parchment hover:bg-amber hover:text-void transition-all"
              aria-label="Previous image"
            >
              <ChevronLeft className="h-5 w-5" />
            </button>
            <button
              onClick={next}
              className="absolute right-4 top-1/2 -translate-y-1/2 flex h-11 w-11 items-center justify-center rounded-full bg-void/60 backdrop-blur-md text-parchment hover:bg-amber hover:text-void transition-all"
              aria-label="Next image"
            >
              <ChevronRight className="h-5 w-5" />
            </button>
          </>
        )}

        {/* Zoom button */}
        <button
          onClick={() => setLightbox(true)}
          className="absolute bottom-4 right-4 flex h-11 w-11 items-center justify-center rounded-full bg-void/60 backdrop-blur-md text-parchment hover:bg-amber hover:text-void transition-all"
          aria-label="Open full-screen viewer"
        >
          <ZoomIn className="h-5 w-5" />
        </button>

        {/* Counter */}
        <div className="absolute bottom-4 left-4 rounded-full bg-void/60 backdrop-blur-md px-4 py-1.5 text-sm text-parchment">
          {current + 1} / {images.length}
        </div>
      </div>

      {/* Thumbnails */}
      {images.length > 1 && (
        <div className="mt-4 grid grid-cols-4 md:grid-cols-6 gap-3">
          {images.map((img, i) => (
            <button
              key={i}
              onClick={() => setCurrent(i)}
              className={`relative aspect-square rounded-xl overflow-hidden transition-all ${
                i === current ? 'ring-2 ring-amber' : 'opacity-60 hover:opacity-100'
              }`}
              aria-label={`View image ${i + 1}`}
            >
              <Image src={img} alt={`${alt} thumbnail ${i + 1}`} fill sizes="120px" className="object-cover" />
            </button>
          ))}
        </div>
      )}

      {/* Lightbox */}
      {lightbox && (
        <div className="fixed inset-0 z-[200] bg-void/95 backdrop-blur-sm flex items-center justify-center p-4">
          <button
            onClick={() => setLightbox(false)}
            className="absolute top-6 right-6 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-parchment hover:bg-amber hover:text-void transition-all"
            aria-label="Close viewer"
          >
            <X className="h-6 w-6" />
          </button>
          <button
            onClick={prev}
            className="absolute left-4 md:left-8 flex h-12 w-12 items-center justify-center rounded-full bg-white/10 text-parchment hover:bg-amber hover:text-void transition-all"
            aria-label="Previous image"
          >
            <ChevronLeft className="h-6 w-6" />
          </button>
          <div className="relative w-full max-w-5xl aspect-[16/10]">
            <Image src={images[current]} alt={`${alt} — full view ${current + 1}`} fill sizes="100vw" className="object-contain" />
          </div>
          <button
            onClick={next}
            className="absolute right-4 md:right-8 flex h-12 w-12 items-center justify-center rounded-full bg-white/10 text-parchment hover:bg-amber hover:text-void transition-all"
            aria-label="Next image"
          >
            <ChevronRight className="h-6 w-6" />
          </button>
        </div>
      )}
    </>
  );
}
