import type { MetadataRoute } from 'next';
import { prisma } from '@/lib/prisma';

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const baseUrl = process.env.NEXTAUTH_URL || 'http://localhost:3000';

  const staticPages = [
    '', '/properties', '/buy', '/rent', '/about', '/services',
    '/agents', '/contact', '/book-viewing', '/privacy', '/terms',
  ].map((path) => ({
    url: `${baseUrl}${path}`,
    lastModified: new Date(),
    changeFrequency: 'weekly' as const,
    priority: path === '' ? 1 : 0.8,
  }));

  const properties = await prisma.property.findMany({
    select: { slug: true, createdAt: true },
  });

  const propertyPages = properties.map((p) => ({
    url: `${baseUrl}/properties/${p.slug}`,
    lastModified: p.createdAt || new Date(),
    changeFrequency: 'monthly' as const,
    priority: 0.6,
  }));

  const agents = await prisma.agent.findMany({
    select: { slug: true },
  });

  const agentPages = agents.map((a) => ({
    url: `${baseUrl}/agents/${a.slug}`,
    lastModified: new Date(),
    changeFrequency: 'monthly' as const,
    priority: 0.5,
  }));

  return [...staticPages, ...propertyPages, ...agentPages];
}
