import Link from 'next/link';
import Image from 'next/image';
import { MapPin } from 'lucide-react';
import { formatPrice } from '@/lib/utils';
import type { PropertyCardData } from '@/lib/types';

interface PropertyCardProps {
  property: PropertyCardData;
  tone?: 'dark' | 'light';
}

export default function PropertyCard({ property, tone = 'dark' }: PropertyCardProps) {
  const isLight = tone === 'light';

  return (
    <Link href={`/properties/${property.slug}`} className="group block">
      <article>
        {/* Image */}
        <div className="relative aspect-[4/4.3] overflow-hidden rounded-card">
          <Image
            src={property.images[0]}
            alt={property.title}
            fill
            sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
            className="object-cover transition-transform duration-700 ease-out group-hover:scale-105"
          />
          {/* Bottom gradient for legibility */}
          <div className="absolute inset-0 bg-gradient-to-t from-void/60 via-transparent to-transparent" />
        </div>

        {/* Title + price */}
        <div className="flex items-baseline justify-between gap-4 pt-4">
          <h3
            className={`text-base md:text-lg font-semibold ${
              isLight ? 'text-void' : 'text-parchment'
            }`}
          >
            {property.title}
          </h3>
          <span
            className={`shrink-0 text-base md:text-lg font-bold ${
              isLight ? 'text-void' : 'text-parchment'
            }`}
          >
            {formatPrice(property.price, property.transactionType)}
          </span>
        </div>

        {/* Specs */}
        <div
          className={`mt-2 flex items-center gap-3 text-xs md:text-sm ${
            isLight ? 'text-charcoal' : 'text-stone'
          }`}
        >
          <span>{property.bedrooms} Bed</span>
          <span className={isLight ? 'text-charcoal/40' : 'text-charcoal'}>|</span>
          <span>{property.bathrooms} Bath</span>
          <span className={isLight ? 'text-charcoal/40' : 'text-charcoal'}>|</span>
          <span>
            {property.area.toLocaleString()} {property.areaUnit}
          </span>
        </div>

        {/* Location */}
        <p className="mt-1.5 flex items-center gap-1.5 text-xs md:text-sm text-stone-dark">
          <MapPin className="h-3.5 w-3.5 text-amber" />
          {property.location}
        </p>
      </article>
    </Link>
  );
}
