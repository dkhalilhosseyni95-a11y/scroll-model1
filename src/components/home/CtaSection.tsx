import Link from 'next/link';
import Image from 'next/image';
import { ArrowRight } from 'lucide-react';
import ScrollReveal from '@/components/ui/ScrollReveal';

export default function CtaSection() {
  return (
    <section className="relative py-section overflow-hidden">
      <div className="container-wide">
        <ScrollReveal className="relative rounded-card overflow-hidden">
          <div className="relative aspect-[16/9] md:aspect-[21/9]">
            <Image
              src="https://images.unsplash.com/photo-1600596542815-ff1a41622a3a?auto=format&fit=crop&w=2000&q=80"
              alt="Luxury villa at dusk with warm interior lighting"
              fill
              sizes="100vw"
              className="object-cover"
              priority={false}
            />
            <div className="absolute inset-0 bg-gradient-to-r from-void/90 via-void/50 to-void/70" />
            <div className="absolute inset-0 bg-gradient-to-t from-void/80 to-transparent" />

            {/* Content */}
            <div className="absolute inset-0 flex flex-col justify-center px-8 md:px-16 lg:px-24 max-w-3xl">
              <p className="section-label mb-4">Begin Your Search</p>
              <h2 className="text-3xl md:text-5xl lg:text-6xl leading-[1.05] mb-6">
                <span className="heading-sans">Find Your Place</span>{' '}
                <span className="heading-serif">in the Extraordinary.</span>
              </h2>
              <p className="text-base md:text-lg text-stone max-w-md mb-8 leading-relaxed">
                Discover remarkable homes chosen for the way you want to live.
              </p>
              <div>
                <Link href="/properties" className="btn-amber group">
                  Explore Homes
                  <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                </Link>
              </div>
            </div>
          </div>
        </ScrollReveal>
      </div>
    </section>
  );
}
