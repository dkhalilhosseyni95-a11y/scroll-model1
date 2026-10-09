import { prisma } from '@/lib/prisma';
import PropertiesClient from '@/components/properties/PropertiesClient';
import type { PropertyCardData } from '@/lib/types';
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Properties for Sale',
  description: 'Browse luxury properties available for purchase through HORIZON PROPERTIES.',
};

export default async function BuyPage() {
  const properties = await prisma.property.findMany({
    where: { transactionType: 'BUY' },
    orderBy: { createdAt: 'desc' },
    select: {
      id: true, title: true, slug: true, price: true, transactionType: true,
      propertyType: true, location: true, city: true, bedrooms: true,
      bathrooms: true, area: true, areaUnit: true, images: true, status: true, featured: true,
    },
  });

  return (
    <div className="pt-28 pb-section">
      <div className="container-wide">
        <div className="mb-10">
          <p className="section-label mb-4">Acquire</p>
          <h1 className="text-4xl md:text-5xl lg:text-6xl leading-none">
            <span className="heading-sans">Properties</span>{' '}
            <span className="heading-serif">for Sale</span>
          </h1>
        </div>
        <PropertiesClient properties={properties as PropertyCardData[]} />
      </div>
    </div>
  );
}
