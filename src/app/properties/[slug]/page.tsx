import { prisma } from '@/lib/prisma';
import { notFound } from 'next/navigation';
import Link from 'next/link';
import Image from 'next/image';
import { Bed, Bath, Maximize, MapPin, Check, ArrowRight } from 'lucide-react';
import PropertyGallery from '@/components/properties/PropertyGallery';
import FavoriteButton from '@/components/properties/FavoriteButton';
import ShareButton from '@/components/properties/ShareButton';
import InquiryForm from '@/components/properties/InquiryForm';
import PropertyCard from '@/components/ui/PropertyCard';
import ScrollReveal from '@/components/ui/ScrollReveal';
import { formatPrice } from '@/lib/utils';
import type { Metadata } from 'next';

export async function generateMetadata({ params }: { params: { slug: string } }): Promise<Metadata> {
  const property = await prisma.property.findUnique({
    where: { slug: params.slug },
    select: { title: true, description: true, location: true },
  });
  if (!property) return { title: 'Property Not Found' };
  return {
    title: property.title,
    description: property.description.slice(0, 160),
    openGraph: {
      title: `${property.title} — HOUSEN`,
      description: property.description.slice(0, 160),
    },
  };
}

export default async function PropertyDetailPage({ params }: { params: { slug: string } }) {
  const property = await prisma.property.findUnique({
    where: { slug: params.slug },
    include: { agent: true },
  });

  if (!property) notFound();

  const similar = await prisma.property.findMany({
    where: {
      propertyType: property.propertyType,
      slug: { not: property.slug },
    },
    take: 3,
    orderBy: { createdAt: 'desc' },
    select: {
      id: true, title: true, slug: true, price: true, transactionType: true,
      propertyType: true, location: true, city: true, bedrooms: true,
      bathrooms: true, area: true, areaUnit: true, images: true, status: true,
    },
  });

  return (
    <div className="pt-24 pb-section">
      <div className="container-wide">
        {/* Breadcrumb */}
        <nav className="flex items-center gap-2 text-sm text-stone-dark mb-6">
          <Link href="/" className="hover:text-amber transition-colors">Home</Link>
          <span>/</span>
          <Link href="/properties" className="hover:text-amber transition-colors">Properties</Link>
          <span>/</span>
          <span className="text-stone">{property.title}</span>
        </nav>

        {/* Gallery */}
        <PropertyGallery images={property.images} alt={property.title} />

        {/* Main layout */}
        <div className="mt-10 grid grid-cols-1 lg:grid-cols-3 gap-10">
          {/* Left: property info */}
          <div className="lg:col-span-2">
            <div className="flex items-start justify-between gap-4 mb-6">
              <div>
                <div className="flex items-center gap-2 mb-2">
                  <span className="rounded-full bg-amber/10 px-3 py-1 text-xs font-medium uppercase tracking-wide text-amber">
                    {property.propertyType}
                  </span>
                  <span className="rounded-full bg-charcoal px-3 py-1 text-xs font-medium uppercase tracking-wide text-stone">
                    For {property.transactionType}
                  </span>
                  {property.status !== 'AVAILABLE' && (
                    <span className="rounded-full bg-charcoal px-3 py-1 text-xs font-medium uppercase tracking-wide text-stone-dark">
                      {property.status}
                    </span>
                  )}
                </div>
                <h1 className="text-3xl md:text-4xl font-bold text-parchment mb-2">{property.title}</h1>
                <p className="flex items-center gap-1.5 text-stone">
                  <MapPin className="h-4 w-4 text-amber" />
                  {property.location}
                </p>
              </div>
              <FavoriteButton propertyId={property.id} />
            </div>

            {/* Price */}
            <div className="flex items-center gap-4 py-6 border-y border-charcoal/40 mb-8">
              <p className="text-3xl md:text-4xl font-bold text-amber font-sans">
                {formatPrice(property.price, property.transactionType)}
              </p>
              <ShareButton />
            </div>

            {/* Specs */}
            <div className="grid grid-cols-3 gap-4 mb-8">
              <div className="rounded-card border border-charcoal/40 p-5 text-center">
                <Bed className="h-6 w-6 text-amber mx-auto mb-2" />
                <p className="text-2xl font-bold text-parchment">{property.bedrooms}</p>
                <p className="text-xs uppercase tracking-wide text-stone-dark">Bedrooms</p>
              </div>
              <div className="rounded-card border border-charcoal/40 p-5 text-center">
                <Bath className="h-6 w-6 text-amber mx-auto mb-2" />
                <p className="text-2xl font-bold text-parchment">{property.bathrooms}</p>
                <p className="text-xs uppercase tracking-wide text-stone-dark">Bathrooms</p>
              </div>
              <div className="rounded-card border border-charcoal/40 p-5 text-center">
                <Maximize className="h-6 w-6 text-amber mx-auto mb-2" />
                <p className="text-2xl font-bold text-parchment">{property.area.toLocaleString()}</p>
                <p className="text-xs uppercase tracking-wide text-stone-dark">{property.areaUnit}</p>
              </div>
            </div>

            {/* Description */}
            <div className="mb-8">
              <h2 className="text-xl font-semibold text-parchment mb-4">About This Property</h2>
              <p className="text-base leading-relaxed text-stone whitespace-pre-line">{property.description}</p>
            </div>

            {/* Amenities */}
            {property.amenities.length > 0 && (
              <div className="mb-8">
                <h2 className="text-xl font-semibold text-parchment mb-4">Features & Amenities</h2>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                  {property.amenities.map((amenity) => (
                    <div key={amenity} className="flex items-center gap-3 text-sm text-stone">
                      <span className="flex h-8 w-8 items-center justify-center rounded-full bg-amber/10">
                        <Check className="h-4 w-4 text-amber" />
                      </span>
                      {amenity}
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Agent */}
            {property.agent && (
              <div className="rounded-card border border-charcoal/40 p-6">
                <h2 className="text-xl font-semibold text-parchment mb-4">Your Agent</h2>
                <Link href={`/agents/${property.agent.slug}`} className="flex items-center gap-4 group">
                  <div className="relative h-16 w-16 rounded-full overflow-hidden border-2 border-amber/30">
                    <Image src={property.agent.profileImage} alt={property.agent.name} fill sizes="64px" className="object-cover" />
                  </div>
                  <div>
                    <p className="font-semibold text-parchment group-hover:text-amber transition-colors">{property.agent.name}</p>
                    <p className="text-sm text-stone-dark">{property.agent.specialties.join(', ')}</p>
                    <p className="text-xs text-stone-dark mt-1">{property.agent.email}</p>
                  </div>
                </Link>
              </div>
            )}
          </div>

          {/* Right: sticky inquiry panel */}
          <div className="lg:col-span-1">
            <div className="lg:sticky lg:top-24 rounded-card border border-charcoal/40 bg-void/60 backdrop-blur-sm p-6">
              <p className="text-2xl font-bold text-amber mb-1">{formatPrice(property.price, property.transactionType)}</p>
              <p className="text-sm text-stone-dark mb-6">Inquire about this property</p>
              <InquiryForm propertyId={property.id} />
              <div className="mt-4 pt-4 border-t border-charcoal/40">
                <Link href={`/book-viewing?property=${property.slug}`} className="btn-outline w-full text-center">
                  Book a Viewing
                </Link>
              </div>
            </div>
          </div>
        </div>

        {/* Similar properties */}
        {similar.length > 0 && (
          <div className="mt-20">
            <ScrollReveal className="mb-8">
              <p className="section-label mb-4">You May Also Like</p>
              <h2 className="text-3xl md:text-4xl font-bold">
                <span className="heading-sans">Similar</span>{' '}
                <span className="heading-serif">Properties</span>
              </h2>
            </ScrollReveal>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
              {similar.map((p, i) => (
                <ScrollReveal key={p.id} delay={i * 100}>
                  <PropertyCard property={p} />
                </ScrollReveal>
              ))}
            </div>
          </div>
        )}
      </div>

      {/* Mobile sticky CTA */}
      <div className="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-void/95 backdrop-blur-lg border-t border-charcoal/40 p-4 flex items-center gap-3">
        <div>
          <p className="text-lg font-bold text-amber">{formatPrice(property.price, property.transactionType)}</p>
          <p className="text-xs text-stone-dark">{property.location}</p>
        </div>
        <Link href={`/book-viewing?property=${property.slug}`} className="btn-amber ml-auto">
          Book Viewing <ArrowRight className="h-4 w-4" />
        </Link>
      </div>
    </div>
  );
}
