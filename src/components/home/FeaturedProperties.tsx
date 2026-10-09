import Link from 'next/link';
import { ArrowRight } from 'lucide-react';
import PropertyCard from '@/components/ui/PropertyCard';
import ScrollReveal from '@/components/ui/ScrollReveal';
import type { PropertyCardData } from '@/lib/types';

export default function FeaturedProperties({ properties }: { properties: PropertyCardData[] }) {
  return (
    <section className="py-section">
      <div className="container-wide">
        {/* Header */}
        <ScrollReveal className="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-12">
          <div>
            <p className="section-label mb-4">Curated Selection</p>
            <h2 className="text-4xl md:text-5xl lg:text-6xl leading-none">
              <span className="heading-sans">Featured</span>{' '}
              <span className="heading-serif">Properties</span>
            </h2>
          </div>
          <div className="md:max-w-sm md:text-right">
            <p className="text-sm leading-relaxed text-stone mb-4">
              We bring together exceptional architecture, considered design, and
              remarkable places to call home.
            </p>
            <Link
              href="/properties"
              className="group inline-flex items-center gap-2 text-sm font-medium uppercase tracking-[0.15em] text-amber hover:text-amber-light transition-colors"
            >
              View All
              <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
            </Link>
          </div>
        </ScrollReveal>

        {/* Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
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
