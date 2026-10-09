import { prisma } from '@/lib/prisma';
import PropertiesClient from '@/components/properties/PropertiesClient';
import type { PropertyCardData } from '@/lib/types';
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Properties',
  description: 'Browse HORIZON PROPERTIES\'s curated collection of luxury villas, penthouses, apartments, and waterfront homes.',
};

export default async function PropertiesPage({
  searchParams,
}: {
  searchParams: { type?: string; transaction?: string; location?: string };
}) {
  const where: Record<string, any> = {};
  if (searchParams.type) where.propertyType = searchParams.type;
  if (searchParams.transaction) where.transactionType = searchParams.transaction.toUpperCase();

  const properties = await prisma.property.findMany({
    where,
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

  return <PropertiesClient properties={properties as PropertyCardData[]} initialType={searchParams.type || ''} />;
}
