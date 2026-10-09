import { prisma } from '@/lib/prisma';
import Hero from '@/components/home/Hero';
import FeaturedProperties from '@/components/home/FeaturedProperties';
import PropertyDiscovery from '@/components/home/PropertyDiscovery';
import BrandStory from '@/components/home/BrandStory';
import PropertyCategories from '@/components/home/PropertyCategories';
import Testimonials from '@/components/home/Testimonials';
import CtaSection from '@/components/home/CtaSection';
import type { PropertyCardData } from '@/lib/types';

async function getProperties(): Promise<PropertyCardData[]> {
  const properties = await prisma.property.findMany({
    orderBy: { createdAt: 'desc' },
    select: {
      id: true,
      title: true,
      slug: true,
      price: true,
      transactionType: true,
      propertyType: true,
      location: true,
      city: true,
      bedrooms: true,
      bathrooms: true,
      area: true,
      areaUnit: true,
      images: true,
      status: true,
      featured: true,
    },
  });
  return properties as PropertyCardData[];
}

export default async function HomePage() {
  const properties = await getProperties();
  const featured = properties.filter((p) => p.featured).slice(0, 3);
  const featuredList = featured.length >= 3 ? featured : properties.slice(0, 3);

  return (
    <>
      <Hero />
      <FeaturedProperties properties={featuredList} />
      <PropertyDiscovery properties={properties} />
      <BrandStory />
      <PropertyCategories />
      <Testimonials />
      <CtaSection />
    </>
  );
}
