import { prisma } from '@/lib/prisma';
import Link from 'next/link';
import Image from 'next/image';
import ScrollReveal from '@/components/ui/ScrollReveal';
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Our Agents',
  description: 'Meet the HOUSEN team of luxury real estate specialists with backgrounds in architecture, fine art, and property finance.',
};

export default async function AgentsPage() {
  const agents = await prisma.agent.findMany({
    where: { activeStatus: true },
    orderBy: { createdAt: 'asc' },
    include: { _count: { select: { properties: true } } },
  });

  return (
    <div className="pt-28 pb-section">
      <div className="container-wide">
        <ScrollReveal className="mb-12">
          <p className="section-label mb-4">Our Team</p>
          <h1 className="text-4xl md:text-6xl lg:text-7xl leading-[1.05]">
            <span className="heading-sans">Meet Our</span>{' '}
            <span className="heading-serif">Agents</span>
          </h1>
          <p className="text-lg text-stone max-w-2xl mt-6 leading-relaxed">
            Our agents bring together backgrounds in architecture, fine art, and
            real estate finance — a combination that allows us to see properties
            as expressions of design and craft.
          </p>
        </ScrollReveal>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
          {agents.map((agent, i) => (
            <ScrollReveal key={agent.id} delay={i * 120}>
              <Link href={`/agents/${agent.slug}`} className="group block">
                <div className="overflow-hidden rounded-card border border-charcoal/30 bg-void transition-all duration-500 hover:border-amber/40">
                  <div className="relative aspect-[4/5] overflow-hidden">
                    <Image
                      src={agent.profileImage}
                      alt={agent.name}
                      fill
                      sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
                      className="object-cover transition-transform duration-700 group-hover:scale-105"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-void/90 via-void/20 to-transparent" />
                    <div className="absolute bottom-0 left-0 right-0 p-6">
                      <h2 className="text-xl font-semibold text-parchment mb-1">{agent.name}</h2>
                      <p className="text-sm text-amber">{agent.specialties.join(' · ')}</p>
                      <p className="text-xs text-stone-dark mt-2">
                        {agent._count.properties} {agent._count.properties === 1 ? 'property' : 'properties'}
                      </p>
                    </div>
                  </div>
                </div>
              </Link>
            </ScrollReveal>
          ))}
        </div>
      </div>
    </div>
  );
}
