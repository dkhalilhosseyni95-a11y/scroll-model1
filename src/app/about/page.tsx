import Image from 'next/image';
import Link from 'next/link';
import ScrollReveal from '@/components/ui/ScrollReveal';
import { ArrowRight } from 'lucide-react';
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'About Us',
  description: 'Horizon Properties connects discerning buyers with extraordinary homes and smart investments. Learn about our philosophy and approach.',
};

export default function AboutPage() {
  return (
    <div className="pt-28 pb-section">
      {/* Hero */}
      <section className="container-wide mb-16 md:mb-20">
        <ScrollReveal>
          <p className="section-label mb-4">About Horizon Properties</p>
          <h1 className="text-4xl md:text-6xl lg:text-7xl leading-[1.05] max-w-4xl">
            <span className="heading-sans">Connecting People With</span>{' '}
            <span className="heading-serif">Extraordinary Homes.</span>
          </h1>
        </ScrollReveal>
      </section>

      {/* Image */}
      <ScrollReveal className="container-wide mb-16 md:mb-20">
        <div className="relative aspect-[21/9] rounded-card overflow-hidden">
          <Image
            src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=2000&q=80"
            alt="Modern architectural villa with warm lighting"
            fill
            sizes="100vw"
            className="object-cover"
            priority
          />
          <div className="absolute inset-0 bg-gradient-to-t from-navy/40 to-transparent" />
        </div>
      </ScrollReveal>

      {/* Story */}
      <section className="container-wide mb-16 md:mb-20">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20">
          <ScrollReveal>
            <p className="section-label mb-6">Our Story</p>
            <h2 className="text-3xl md:text-4xl font-bold text-ink mb-6">
              A New Standard in Luxury Real Estate
            </h2>
          </ScrollReveal>
          <ScrollReveal delay={150}>
            <div className="space-y-4 text-base leading-relaxed text-slate">
              <p>
                Horizon Properties was founded on a simple belief: that finding the
                right home should feel considered, transparent, and entirely
                personal. We reject the commodity-driven approach of traditional
                real estate in favour of genuine, long-term relationships.
              </p>
              <p>
                Every property in our portfolio is selected for its architectural
                integrity, its relationship to place, and its capacity to elevate
                daily life. We work with a limited number of clients at a time,
                ensuring each receives the attention and expertise they deserve.
              </p>
              <p>
                Our team brings together backgrounds in architecture, finance, and
                property marketing — a combination that allows us to see properties
                not just as assets, but as expressions of design and craft.
              </p>
            </div>
          </ScrollReveal>
        </div>
      </section>

      {/* Values */}
      <section className="container-wide mb-16 md:mb-20">
        <ScrollReveal className="mb-10">
          <p className="section-label mb-4">What We Believe</p>
          <h2 className="text-3xl md:text-4xl font-bold text-ink">Our Values</h2>
        </ScrollReveal>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {[
            { title: 'Architectural Integrity', text: 'We only represent properties of genuine architectural merit — homes designed with intention and built to last.' },
            { title: 'Personal Service', text: 'We treat each client relationship as a long-term partnership, not a transaction. Our process is personal and unhurried.' },
            { title: 'Discretion & Trust', text: 'We operate with complete confidentiality. Your privacy and your interests are our highest priority.' },
          ].map((value, i) => (
            <ScrollReveal key={value.title} delay={i * 120}>
              <div className="rounded-card border border-line bg-white p-8 h-full">
                <div className="text-3xl font-bold text-champagne font-sans mb-4">0{i + 1}</div>
                <h3 className="text-lg font-semibold text-ink mb-3">{value.title}</h3>
                <p className="text-sm leading-relaxed text-slate">{value.text}</p>
              </div>
            </ScrollReveal>
          ))}
        </div>
      </section>

      {/* Stats */}
      <section className="container-wide mb-16 md:mb-20">
        <ScrollReveal className="grid grid-cols-2 md:grid-cols-4 gap-8 text-center border-y border-line py-12">
          {[
            { value: '200+', label: 'Properties Sold' },
            { value: '15', label: 'Years of Service' },
            { value: '98%', label: 'Client Satisfaction' },
            { value: '12', label: 'Cities Served' },
          ].map((stat) => (
            <div key={stat.label}>
              <p className="text-4xl md:text-5xl font-bold text-navy font-sans mb-2">{stat.value}</p>
              <p className="text-xs uppercase tracking-wide text-slate-light">{stat.label}</p>
            </div>
          ))}
        </ScrollReveal>
      </section>

      {/* CTA */}
      <section className="container-wide">
        <ScrollReveal className="text-center">
          <h2 className="text-3xl md:text-4xl font-bold mb-6">
            <span className="heading-sans">Ready to Find</span>{' '}
            <span className="heading-serif">Your Home?</span>
          </h2>
          <Link href="/properties" className="btn-amber group">
            Browse Properties <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
          </Link>
        </ScrollReveal>
      </section>
    </div>
  );
}
