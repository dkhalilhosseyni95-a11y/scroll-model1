import Link from 'next/link';
import Image from 'next/image';
import { ArrowUpRight } from 'lucide-react';
import ScrollReveal from '@/components/ui/ScrollReveal';

export default function CtaSection() {
  return (
    <section className="relative overflow-hidden">
      <div className="relative h-[70vh] min-h-[480px]">
        <Image
          src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=2000&q=80"
          alt="Luxury villa at dusk with warm interior lighting"
          fill
          sizes="100vw"
          className="object-cover"
        />
        <div className="absolute inset-0 bg-void/35" />
        <div className="absolute inset-0 bg-gradient-to-b from-void/60 via-transparent to-void/80" />

        {/* Centered content */}
        <ScrollReveal className="absolute inset-0 flex flex-col items-center justify-center px-6 text-center">
          <h2 className="text-3xl font-bold leading-[1.15] text-parchment md:text-5xl lg:text-6xl">
            <span className="font-sans tracking-tight">Start Your Journey to</span>
            <br />
            <span className="font-sans tracking-tight">Smarter </span>
            <em className="font-serif italic">Living</em>
          </h2>
          <p className="mt-4 max-w-md text-sm leading-relaxed text-parchment/80">
            From modern apartments to luxury villas — find your perfect home today.
          </p>
          <div className="mt-8">
            <Link href="/properties" className="btn-amber text-sm">
              Explore More
              <ArrowUpRight className="h-4 w-4" />
            </Link>
          </div>
        </ScrollReveal>
      </div>
    </section>
  );
}
