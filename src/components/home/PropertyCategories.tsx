import Link from 'next/link';
import Image from 'next/image';
import ScrollReveal from '@/components/ui/ScrollReveal';
import { ArrowUpRight } from 'lucide-react';

const categories = [
  {
    title: 'Contemporary Villas',
    description: 'Sculptural homes with organic geometry and seamless indoor-outdoor living.',
    image: 'https://images.unsplash.com/photo-1505843513577-22bb7d21e455?auto=format&fit=crop&w=800&q=80',
    href: '/properties?type=Villa',
    span: 'lg:col-span-2',
  },
  {
    title: 'Luxury Apartments',
    description: 'Refined city residences with premium amenities.',
    image: 'https://images.unsplash.com/photo-1706808849780-7a04fbac83ef?auto=format&fit=crop&w=800&q=80',
    href: '/properties?type=Apartment',
    span: '',
  },
  {
    title: 'Penthouses',
    description: 'Sky-high living with panoramic views.',
    image: 'https://images.unsplash.com/photo-1706808849780-7a04fbac83ef?auto=format&fit=crop&w=800&q=80&sat=-100',
    href: '/properties?type=Penthouse',
    span: '',
  },
  {
    title: 'Waterfront Homes',
    description: 'Coastal estates where architecture meets the water.',
    image: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80',
    href: '/properties?type=Villa&location=water',
    span: 'lg:col-span-2',
  },
];

export default function PropertyCategories() {
  return (
    <section className="py-section">
      <div className="container-wide">
        <ScrollReveal className="mb-12">
          <p className="section-label mb-4">Browse by Type</p>
          <h2 className="text-4xl md:text-5xl lg:text-6xl leading-none">
            <span className="heading-sans">Property</span>{' '}
            <span className="heading-serif">Categories</span>
          </h2>
        </ScrollReveal>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {categories.map((cat, i) => (
            <ScrollReveal key={cat.title} delay={i * 100} className={cat.span}>
              <Link
                href={cat.href}
                className="group relative block aspect-[16/10] lg:aspect-[16/9] rounded-card overflow-hidden"
              >
                <Image
                  src={cat.image}
                  alt={cat.title}
                  fill
                  sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 33vw"
                  className="object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-void/90 via-void/30 to-transparent" />
                <div className="absolute bottom-0 left-0 right-0 p-6">
                  <div className="flex items-start justify-between">
                    <div>
                      <h3 className="text-xl md:text-2xl font-semibold text-parchment mb-1">
                        {cat.title}
                      </h3>
                      <p className="text-sm text-stone max-w-xs">{cat.description}</p>
                    </div>
                    <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-white/20 text-parchment group-hover:border-amber group-hover:text-amber transition-all">
                      <ArrowUpRight className="h-5 w-5" />
                    </span>
                  </div>
                </div>
              </Link>
            </ScrollReveal>
          ))}
        </div>
      </div>
    </section>
  );
}
