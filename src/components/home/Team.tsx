import Image from 'next/image';
import Link from 'next/link';
import { Linkedin, Mail } from 'lucide-react';
import ScrollReveal from '@/components/ui/ScrollReveal';
import type { AgentCardData } from '@/lib/types';

export default function Team({ agents }: { agents: AgentCardData[] }) {
  const team = agents.slice(0, 4);
  if (team.length === 0) return null;

  return (
    <section className="py-section bg-white">
      <div className="container-wide">
        <ScrollReveal className="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-12 md:mb-16">
          <div>
            <p className="section-label mb-4">Meet The Team</p>
            <h2 className="text-4xl md:text-5xl lg:text-6xl leading-[1.05]">
              <span className="heading-sans">Our</span>{' '}
              <span className="heading-serif">Experts</span>
            </h2>
          </div>
          <p className="md:max-w-sm md:text-right text-sm leading-relaxed text-slate">
            A dedicated team of advisors bringing decades of combined experience
            in luxury real estate and investment.
          </p>
        </ScrollReveal>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
          {team.map((agent, i) => (
            <ScrollReveal key={agent.id} delay={Math.min(i * 90, 360)}>
              <Link href={`/agents/${agent.slug}`} className="group block">
                <article className="overflow-hidden rounded-card border border-line bg-white transition-all duration-500 hover:border-champagne/50 hover:shadow-[0_24px_60px_-30px_rgba(11,31,51,0.35)]">
                  <div className="relative aspect-[3/4] overflow-hidden bg-mist">
                    <Image
                      src={agent.profileImage}
                      alt={agent.name}
                      fill
                      sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw"
                      className="object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-navy/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
                    <div className="absolute bottom-4 left-4 flex gap-2 opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-500">
                      <span className="flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-navy">
                        <Linkedin className="h-4 w-4" />
                      </span>
                      <span className="flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-navy">
                        <Mail className="h-4 w-4" />
                      </span>
                    </div>
                  </div>
                  <div className="p-5 text-center">
                    <h3 className="text-lg font-semibold text-ink group-hover:text-champagne-dark transition-colors">
                      {agent.name}
                    </h3>
                    <p className="text-sm text-slate mt-1">
                      {agent.specialties[0] || 'Property Advisor'}
                    </p>
                  </div>
                </article>
              </Link>
            </ScrollReveal>
          ))}
        </div>
      </div>
    </section>
  );
}
