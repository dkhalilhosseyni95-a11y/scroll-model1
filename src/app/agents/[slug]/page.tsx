import { prisma } from '@/lib/prisma';
import { notFound } from 'next/navigation';
import Image from 'next/image';
import Link from 'next/link';
import { Mail, Phone, ArrowRight } from 'lucide-react';
import PropertyCard from '@/components/ui/PropertyCard';
import ScrollReveal from '@/components/ui/ScrollReveal';
import type { Metadata } from 'next';

export async function generateMetadata({ params }: { params: { slug: string } }): Promise<Metadata> {
  const agent = await prisma.agent.findUnique({
    where: { slug: params.slug },
    select: { name: true, biography: true },
  });
  if (!agent) return { title: 'Agent Not Found' };
  return {
    title: agent.name,
    description: agent.biography.slice(0, 160),
  };
}

export default async function AgentDetailPage({ params }: { params: { slug: string } }) {
  const agent = await prisma.agent.findUnique({
    where: { slug: params.slug },
    include: {
      properties: {
        orderBy: { createdAt: 'desc' },
        select: {
          id: true, title: true, slug: true, price: true, transactionType: true,
          propertyType: true, location: true, city: true, bedrooms: true,
          bathrooms: true, area: true, areaUnit: true, images: true, status: true,
        },
      },
    },
  });

  if (!agent) notFound();

  return (
    <div className="pt-28 pb-section">
      <div className="container-wide">
        {/* Breadcrumb */}
        <nav className="flex items-center gap-2 text-sm text-slate-light mb-8">
          <Link href="/" className="hover:text-champagne-dark transition-colors">Home</Link>
          <span className="text-line">/</span>
          <Link href="/agents" className="hover:text-champagne-dark transition-colors">Team</Link>
          <span className="text-line">/</span>
          <span className="text-slate">{agent.name}</span>
        </nav>

        {/* Agent header */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-10 mb-16">
          <ScrollReveal className="lg:col-span-1">
            <div className="relative aspect-[4/5] rounded-card overflow-hidden">
              <Image
                src={agent.profileImage}
                alt={agent.name}
                fill
                sizes="(max-width: 1024px) 100vw, 33vw"
                className="object-cover"
                priority
              />
            </div>
          </ScrollReveal>

          <ScrollReveal delay={150} className="lg:col-span-2">
            <p className="section-label mb-4">Your Agent</p>
            <h1 className="text-4xl md:text-5xl font-bold text-ink mb-3">{agent.name}</h1>
            <p className="text-lg text-champagne-dark mb-6">{agent.specialties.join(' · ')}</p>
            <p className="text-base leading-relaxed text-slate mb-8 max-w-2xl">{agent.biography}</p>

            <div className="flex flex-wrap gap-4">
              <a
                href={`mailto:${agent.email}`}
                className="flex items-center gap-2 rounded-full border border-line px-5 py-2.5 text-sm text-slate hover:border-champagne hover:text-champagne-dark transition-all"
              >
                <Mail className="h-4 w-4" />
                {agent.email}
              </a>
              <a
                href={`tel:${agent.phone}`}
                className="flex items-center gap-2 rounded-full border border-line px-5 py-2.5 text-sm text-slate hover:border-champagne hover:text-champagne-dark transition-all"
              >
                <Phone className="h-4 w-4" />
                {agent.phone}
              </a>
            </div>
          </ScrollReveal>
        </div>

        {/* Agent's properties */}
        {agent.properties.length > 0 && (
          <div>
            <ScrollReveal className="mb-8 flex items-center justify-between">
              <div>
                <p className="section-label mb-4">Portfolio</p>
                <h2 className="text-3xl md:text-4xl font-bold">
                  <span className="heading-sans">Listed</span>{' '}
                  <span className="heading-serif">Properties</span>
                </h2>
              </div>
              <Link href="/properties" className="group inline-flex items-center gap-2 text-sm font-medium uppercase tracking-[0.15em] text-champagne-dark hover:text-navy transition-colors">
                View All <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
              </Link>
            </ScrollReveal>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
              {agent.properties.map((p, i) => (
                <ScrollReveal key={p.id} delay={i * 100}>
                  <PropertyCard property={p} />
                </ScrollReveal>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
