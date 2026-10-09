import Link from 'next/link';
import Image from 'next/image';
import { Bed, Bath, Maximize } from 'lucide-react';
import { formatPrice } from '@/lib/utils';
import type { PropertyCardData } from '@/lib/types';

export default function PropertyCard({ property }: { property: PropertyCardData }) {
  return (
    <Link
      href={`/properties/${property.slug}`}
      className="group block"
    >
      <article className="overflow-hidden rounded-card border border-charcoal/30 bg-void transition-all duration-500 hover:border-amber/40">
        {/* Image */}
        <div className="relative aspect-[4/3] overflow-hidden">
          <Image
            src={property.images[0]}
            alt={property.title}
            fill
            sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
            className="object-cover transition-transform duration-700 ease-out group-hover:scale-105"
          />
          {/* Overlay gradient */}
          <div className="absolute inset-0 bg-gradient-to-t from-void/80 via-void/10 to-transparent" />

          {/* Price badge */}
          <div className="absolute top-4 left-4">
            <span className="rounded-full bg-void/70 backdrop-blur-md px-4 py-1.5 text-sm font-semibold text-amber">
              {formatPrice(property.price, property.transactionType)}
            </span>
          </div>

          {/* Status badge */}
          {property.status !== 'AVAILABLE' && (
            <div className="absolute top-4 right-4">
              <span className="rounded-full bg-charcoal/80 backdrop-blur-md px-3 py-1.5 text-xs font-medium uppercase tracking-wide text-stone">
                {property.status}
              </span>
            </div>
          )}

          {/* Bottom metadata on image */}
          <div className="absolute bottom-4 left-4 right-4">
            <h3 className="text-lg font-semibold text-parchment mb-1">
              {property.title}
            </h3>
            <p className="text-sm text-stone">{property.location}</p>
          </div>
        </div>

        {/* Specs */}
        <div className="flex items-center gap-5 px-5 py-4 text-sm text-stone">
          <span className="flex items-center gap-1.5">
            <Bed className="h-4 w-4 text-amber" />
            {property.bedrooms} Bed
          </span>
          <span className="flex items-center gap-1.5">
            <Bath className="h-4 w-4 text-amber" />
            {property.bathrooms} Bath
          </span>
          <span className="flex items-center gap-1.5">
            <Maximize className="h-4 w-4 text-amber" />
            {property.area.toLocaleString()} {property.areaUnit}
          </span>
          <span className="ml-auto text-xs uppercase tracking-wide text-stone-dark">
            {property.propertyType}
          </span>
        </div>
      </article>
    </Link>
  );
}
