import { prisma } from '@/lib/prisma';
import PropertiesManager from '@/components/admin/PropertiesManager';

export default async function AdminPropertiesPage() {
  const properties = await prisma.property.findMany({
    orderBy: { createdAt: 'desc' },
    select: {
      id: true, title: true, slug: true, price: true, transactionType: true,
      propertyType: true, location: true, city: true, bedrooms: true,
      bathrooms: true, area: true, areaUnit: true, images: true, status: true,
      featured: true, amenities: true, agentId: true, description: true,
    },
  });

  const agents = await prisma.agent.findMany({
    select: { id: true, name: true },
    orderBy: { name: 'asc' },
  });

  return <PropertiesManager properties={properties as any} agents={agents} />;
}
