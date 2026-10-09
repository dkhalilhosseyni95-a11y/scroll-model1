import { ShieldCheck, LineChart, Handshake, Award } from 'lucide-react';
import ScrollReveal from '@/components/ui/ScrollReveal';

const reasons = [
  {
    Icon: Award,
    title: 'Curated Portfolio',
    description:
      'Every property is hand-selected for architectural integrity, location, and lasting value.',
  },
  {
    Icon: LineChart,
    title: 'Market Expertise',
    description:
      'Deep, data-backed insight into luxury markets helps you buy and sell with confidence.',
  },
  {
    Icon: Handshake,
    title: 'Personal Service',
    description:
      'A dedicated advisor for every client, from first viewing to closing and beyond.',
  },
  {
    Icon: ShieldCheck,
    title: 'Trusted Integrity',
    description:
      'Transparent advice and honest valuations — our reputation is built on long-term trust.',
  },
];

export default function WhyChooseHorizon() {
  return (
    <section className="py-section bg-mist">
      <div className="container-wide">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 items-start">
          <ScrollReveal className="lg:col-span-5 lg:sticky lg:top-28">
            <p className="section-label mb-4">Why Choose Us</p>
            <h2 className="text-4xl md:text-5xl lg:text-[3.5rem] leading-[1.05] mb-6">
              <span className="heading-sans">Why Choose</span>{' '}
              <span className="heading-serif">Horizon</span>
            </h2>
            <p className="text-base md:text-lg text-slate leading-relaxed max-w-md">
              We combine the reach of a global network with the care of a boutique
              agency — so every client receives expert guidance and a genuinely
              personal experience.
            </p>
          </ScrollReveal>

          <div className="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-6">
            {reasons.map((r, i) => (
              <ScrollReveal key={r.title} delay={Math.min(i * 90, 360)}>
                <div className="h-full rounded-card border border-line bg-white p-7 lg:p-8 transition-all duration-300 hover:border-champagne/50 hover:shadow-[0_20px_50px_-30px_rgba(11,31,51,0.4)]">
                  <span className="flex h-12 w-12 items-center justify-center rounded-full bg-champagne/12 text-champagne-dark mb-5">
                    <r.Icon className="h-6 w-6" strokeWidth={1.5} />
                  </span>
                  <h3 className="text-lg font-semibold text-ink mb-2.5">{r.title}</h3>
                  <p className="text-sm leading-relaxed text-slate">{r.description}</p>
                </div>
              </ScrollReveal>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
