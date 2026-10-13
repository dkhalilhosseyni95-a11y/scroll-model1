import Link from 'next/link';
import { ArrowUpRight } from 'lucide-react';
import PropertyCard from '@/components/ui/PropertyCard';
import ScrollReveal from '@/components/ui/ScrollReveal';
import type { PropertyCardData } from '@/lib/types';

export default function FeaturedProperties({ properties }: { properties: PropertyCardData[] }) {
  return (
    <section className="py-section">
      <div className="container-wide">
        {/* Header: "Featured / Properties" left, copy + View All right */}
        <ScrollReveal className="mb-12 flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
          <h2 className="text-4xl md:text-5xl lg:text-6xl leading-none">
            <span className="heading-sans block">Featured</span>
            <span className="heading-serif block">Properties</span>
          </h2>
          <div className="md:max-w-sm md:text-right">
            <p className="mb-4 text-sm leading-relaxed text-stone">
              We blend design, technology, and trust to connect people with
              spaces they&apos;ll love.
            </p>
            <Link href="/properties" className="btn-amber text-sm">
              View All
              <ArrowUpRight className="h-4 w-4" />
            </Link>
          </div>
        </ScrollReveal>

        {/* Cards */}
        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 lg:gap-8">
          {properties.map((property, i) => (
            <ScrollReveal key={property.id} delay={i * 120}>
              <PropertyCard property={property} />
            </ScrollReveal>
          ))}
        </div>
      </div>
    </section>
  );
}
