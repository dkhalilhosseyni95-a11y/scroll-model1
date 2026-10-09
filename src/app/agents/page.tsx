import { prisma } from '@/lib/prisma';
import Link from 'next/link';
import Image from 'next/image';
import ScrollReveal from '@/components/ui/ScrollReveal';
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Our Team',
  description: 'Meet the Horizon Properties team of luxury real estate specialists with backgrounds in architecture, finance, and property marketing.',
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
            <span className="heading-serif">Experts</span>
          </h1>
          <p className="text-lg text-slate max-w-2xl mt-6 leading-relaxed">
            Our advisors bring together backgrounds in architecture, finance, and
            property marketing — a combination that allows us to see properties as
            expressions of design and craft.
          </p>
        </ScrollReveal>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
          {agents.map((agent, i) => (
            <ScrollReveal key={agent.id} delay={i * 100}>
              <Link href={`/agents/${agent.slug}`} className="group block">
                <div className="overflow-hidden rounded-card border border-line bg-white transition-all duration-500 hover:border-champagne/50 hover:shadow-[0_24px_60px_-30px_rgba(11,31,51,0.35)]">
                  <div className="relative aspect-[3/4] overflow-hidden bg-mist">
                    <Image
                      src={agent.profileImage}
                      alt={agent.name}
                      fill
                      sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw"
                      className="object-cover transition-transform duration-700 group-hover:scale-105"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-navy/80 via-navy/10 to-transparent" />
                    <div className="absolute bottom-0 left-0 right-0 p-5">
                      <h2 className="text-lg font-semibold text-ivory mb-1">{agent.name}</h2>
                      <p className="text-sm text-champagne-light">{agent.specialties[0]}</p>
                      <p className="text-xs text-ivory/60 mt-2">
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
