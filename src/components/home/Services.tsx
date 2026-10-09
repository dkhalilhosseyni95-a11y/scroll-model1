import Link from 'next/link';
import { ArrowRight } from 'lucide-react';
import ScrollReveal from '@/components/ui/ScrollReveal';

const services = [
  {
    no: '01',
    title: 'Luxury Home Sales',
    description:
      'Representing architecturally significant residences with bespoke marketing and a global buyer network.',
  },
  {
    no: '02',
    title: 'Property Investment',
    description:
      'Data-driven advisory for building and balancing a resilient real estate portfolio across prime markets.',
  },
  {
    no: '03',
    title: 'Property Marketing',
    description:
      'Editorial photography, cinematic film, and targeted campaigns that present each home at its finest.',
  },
  {
    no: '04',
    title: 'Real Estate Advisory',
    description:
      'Strategic guidance through every stage — acquisition, negotiation, and long-term asset planning.',
  },
  {
    no: '05',
    title: 'Property Valuation',
    description:
      'Precise, market-informed appraisals grounded in comparable sales and architectural value.',
  },
  {
    no: '06',
    title: 'Relocation Services',
    description:
      'End-to-end support for seamless moves, from neighborhood discovery to settling in.',
  },
];

export default function Services() {
  return (
    <section className="py-section bg-ivory">
      <div className="container-wide">
        <ScrollReveal className="max-w-2xl mb-12 md:mb-16">
          <p className="section-label mb-4">What We Do</p>
          <h2 className="text-4xl md:text-5xl lg:text-6xl leading-[1.05]">
            <span className="heading-sans">Our</span>{' '}
            <span className="heading-serif">Services</span>
          </h2>
          <p className="mt-6 text-base md:text-lg text-slate leading-relaxed">
            A full spectrum of premium real estate services — designed to support
            buyers, sellers, and investors with expertise at every turn.
          </p>
        </ScrollReveal>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 border-t border-l border-line">
          {services.map((s, i) => (
            <ScrollReveal key={s.no} delay={Math.min(i * 80, 320)}>
              <div className="group h-full border-r border-b border-line p-8 lg:p-10 transition-colors duration-300 hover:bg-white">
                <div className="flex items-start justify-between mb-6">
                  <span className="font-serif italic text-2xl text-champagne">
                    {s.no}
                  </span>
                  <ArrowRight className="h-5 w-5 text-slate-light -translate-x-1 opacity-0 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100 group-hover:text-champagne-dark" />
                </div>
                <h3 className="text-xl font-semibold text-ink mb-3">{s.title}</h3>
                <p className="text-sm leading-relaxed text-slate">{s.description}</p>
              </div>
            </ScrollReveal>
          ))}
        </div>

        <ScrollReveal className="mt-12 text-center">
          <Link
            href="/services"
            className="group inline-flex items-center gap-2.5 rounded-full border border-ink/15 px-7 py-3.5 text-sm font-medium text-ink hover:border-champagne hover:text-champagne-dark transition-all"
          >
            Explore All Services
            <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
          </Link>
        </ScrollReveal>
      </div>
    </section>
  );
}
