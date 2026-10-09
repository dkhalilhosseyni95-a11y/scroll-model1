import Link from 'next/link';
import Image from 'next/image';
import { Bed, Bath, Maximize } from 'lucide-react';
import { formatPrice } from '@/lib/utils';
import type { PropertyCardData } from '@/lib/types';

export default function PropertyCard({ property }: { property: PropertyCardData }) {
  return (
    <Link href={`/properties/${property.slug}`} className="group block h-full">
      <article className="h-full overflow-hidden rounded-card border border-line bg-white transition-all duration-500 hover:border-champagne/50 hover:shadow-[0_24px_60px_-30px_rgba(11,31,51,0.35)]">
        {/* Image */}
        <div className="relative aspect-[4/3] overflow-hidden">
          <Image
            src={property.images[0]}
            alt={property.title}
            fill
            sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
            className="object-cover transition-transform duration-700 ease-out group-hover:scale-105"
          />
          <div className="absolute inset-0 bg-gradient-to-t from-navy/30 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />

          {/* Price badge */}
          <div className="absolute top-4 left-4">
            <span className="rounded-full bg-navy/85 backdrop-blur-sm px-4 py-1.5 text-sm font-semibold text-champagne-light">
              {formatPrice(property.price, property.transactionType)}
            </span>
          </div>

          {/* Type badge */}
          <div className="absolute top-4 right-4">
            <span className="rounded-full bg-white/90 backdrop-blur-sm px-3 py-1.5 text-[11px] font-medium uppercase tracking-wide text-navy">
              {property.propertyType}
            </span>
          </div>

          {property.status !== 'AVAILABLE' && (
            <div className="absolute bottom-4 right-4">
              <span className="rounded-full bg-champagne px-3 py-1.5 text-[11px] font-medium uppercase tracking-wide text-navy">
                {property.status}
              </span>
            </div>
          )}
        </div>

        {/* Body */}
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
  );
}
