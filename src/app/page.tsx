import { prisma } from '@/lib/prisma';
import Hero from '@/components/home/Hero';
import WhoWeAre from '@/components/home/WhoWeAre';
import FeaturedProperties from '@/components/home/FeaturedProperties';
import Services from '@/components/home/Services';
import WhyChooseHorizon from '@/components/home/WhyChooseHorizon';
import Team from '@/components/home/Team';
import CtaSection from '@/components/home/CtaSection';
import type { PropertyCardData, AgentCardData } from '@/lib/types';

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

async function getAgents(): Promise<AgentCardData[]> {
  const agents = await prisma.agent.findMany({
    orderBy: { createdAt: 'asc' },
    select: {
      id: true,
      name: true,
      slug: true,
      biography: true,
      profileImage: true,
      email: true,
      phone: true,
      specialties: true,
      activeStatus: true,
    },
  });
  return agents as AgentCardData[];
}

export default async function HomePage() {
  const [properties, agents] = await Promise.all([getProperties(), getAgents()]);

  const featured = properties.filter((p) => p.featured);
  const featuredList = (featured.length >= 4 ? featured : properties).slice(0, 8);

  return (
    <>
      <Hero />
      <WhoWeAre />
      <FeaturedProperties properties={featuredList} />
      <Services />
      <WhyChooseHorizon />
      <Team agents={agents} />
      <CtaSection />
    </>
  );
}
