import Link from 'next/link';
import ScrollReveal from '@/components/ui/ScrollReveal';
import { ArrowRight } from 'lucide-react';
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Services',
  description: 'Horizon Properties offers luxury home sales, property investment, marketing, advisory, valuation, and relocation services.',
};

const services = [
  { title: 'Luxury Home Sales', description: 'We represent architecturally significant residences with bespoke marketing and a global buyer network.', icon: '01' },
  { title: 'Property Investment', description: 'Data-driven advisory for building and balancing a resilient real estate portfolio across prime markets.', icon: '02' },
  { title: 'Property Marketing', description: 'Editorial photography, cinematic film, and targeted campaigns that present each home at its finest.', icon: '03' },
  { title: 'Real Estate Advisory', description: 'Strategic guidance through every stage — acquisition, negotiation, and long-term asset planning.', icon: '04' },
  { title: 'Property Valuation', description: 'Precise, market-informed appraisals grounded in comparable sales and architectural value.', icon: '05' },
  { title: 'Relocation Services', description: 'End-to-end support for seamless moves, from neighborhood discovery to settling in.', icon: '06' },
];

const steps = [
  { title: 'Consultation', text: 'We begin with a private consultation to understand your needs, preferences, and aspirations.' },
  { title: 'Curation', text: 'We curate a selection of properties that match your criteria, each vetted for architectural quality.' },
  { title: 'Viewing', text: 'Private viewings arranged at your convenience, with architectural context provided by our team.' },
  { title: 'Acquisition', text: 'We guide you through negotiation, inspection, and closing with complete discretion.' },
];

export default function ServicesPage() {
  return (
    <div className="pt-28 pb-section">
      <section className="container-wide mb-16 md:mb-20">
        <ScrollReveal>
          <p className="section-label mb-4">What We Offer</p>
          <h1 className="text-4xl md:text-6xl lg:text-7xl leading-[1.05]">
            <span className="heading-sans">Our</span>{' '}
            <span className="heading-serif">Services</span>
          </h1>
          <p className="text-lg text-slate max-w-2xl mt-6 leading-relaxed">
            From acquisition to portfolio management, Horizon Properties provides
            a full spectrum of services for the luxury property market — each
            delivered with care and expertise.
          </p>
        </ScrollReveal>
      </section>

      {/* Services grid */}
      <section className="container-wide mb-16 md:mb-20">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {services.map((service, i) => (
            <ScrollReveal key={service.title} delay={i * 100}>
              <div className="rounded-card border border-line bg-white p-8 h-full hover:border-champagne/50 hover:shadow-[0_20px_50px_-30px_rgba(11,31,51,0.35)] transition-all">
                <p className="text-3xl font-bold text-champagne/50 font-sans mb-6">{service.icon}</p>
                <h3 className="text-lg font-semibold text-ink mb-3">{service.title}</h3>
                <p className="text-sm leading-relaxed text-slate">{service.description}</p>
              </div>
            </ScrollReveal>
          ))}
        </div>
      </section>

      {/* Process */}
      <section className="container-wide mb-16 md:mb-20">
        <ScrollReveal className="mb-10">
          <p className="section-label mb-4">How We Work</p>
          <h2 className="text-3xl md:text-4xl font-bold text-ink">Our Process</h2>
        </ScrollReveal>
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          {steps.map((step, i) => (
            <ScrollReveal key={step.title} delay={i * 120}>
              <div className="relative">
                <div className="flex items-center gap-3 mb-4">
                  <span className="flex h-10 w-10 items-center justify-center rounded-full bg-navy text-champagne-light font-bold text-sm">
                    {i + 1}
                  </span>
                  {i < steps.length - 1 && <div className="flex-1 h-px bg-line" />}
                </div>
                <h3 className="text-lg font-semibold text-ink mb-2">{step.title}</h3>
                <p className="text-sm text-slate leading-relaxed">{step.text}</p>
              </div>
            </ScrollReveal>
          ))}
        </div>
      </section>

      {/* CTA */}
      <section className="container-wide">
        <ScrollReveal className="text-center">
          <h2 className="text-3xl md:text-4xl font-bold mb-6">
            <span className="heading-sans">Let&apos;s Work</span>{' '}
            <span className="heading-serif">Together</span>
          </h2>
          <Link href="/contact" className="btn-amber group">
            Get in Touch <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
          </Link>
        </ScrollReveal>
      </section>
    </div>
  );
}
