import Image from 'next/image';
import Link from 'next/link';
import ScrollReveal from '@/components/ui/ScrollReveal';
import { ArrowRight } from 'lucide-react';
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'About Us',
  description: 'HOUSEN curates exceptional architecture and remarkable places to call home. Learn about our philosophy and approach.',
};

export default function AboutPage() {
  return (
    <div className="pt-28 pb-section">
      {/* Hero */}
      <section className="container-wide mb-20">
        <ScrollReveal>
          <p className="section-label mb-4">About HOUSEN</p>
          <h1 className="text-4xl md:text-6xl lg:text-7xl leading-[1.05] max-w-4xl">
            <span className="heading-sans">We Curate</span>{' '}
            <span className="heading-serif">Living Art.</span>
          </h1>
        </ScrollReveal>
      </section>

      {/* Image */}
      <ScrollReveal className="container-wide mb-20">
        <div className="relative aspect-[21/9] rounded-card overflow-hidden">
          <Image
            src="https://images.unsplash.com/photo-1600607687938-ce4d6c4b8be1?auto=format&fit=crop&w=2000&q=80"
            alt="Modern architectural villa with organic forms"
            fill
            sizes="100vw"
            className="object-cover"
            priority
          />
          <div className="absolute inset-0 bg-gradient-to-t from-void/60 to-transparent" />
        </div>
      </ScrollReveal>

      {/* Story */}
      <section className="container-wide mb-20">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20">
          <ScrollReveal>
            <p className="section-label mb-6">Our Story</p>
            <h2 className="text-3xl md:text-4xl font-bold mb-6">A New Standard in Luxury Real Estate</h2>
          </ScrollReveal>
          <ScrollReveal delay={150}>
            <div className="space-y-4 text-base leading-relaxed text-stone">
              <p>
                HOUSEN was founded on a simple belief: that a home is the most personal
                form of architecture, and finding the right one should feel like
                discovering a work of art. We reject the commodity-driven approach of
                traditional real estate in favour of a curatorial practice.
              </p>
              <p>
                Every property in our portfolio is selected for its architectural
                integrity, its relationship to place, and its capacity to elevate daily
                life. We work with a limited number of clients at a time, ensuring each
                receives the attention and expertise they deserve.
              </p>
              <p>
                Our team brings together backgrounds in architecture, fine art, and
                real estate finance — a combination that allows us to see properties not
                just as assets, but as expressions of design and craft.
              </p>
            </div>
          </ScrollReveal>
        </div>
      </section>

      {/* Values */}
      <section className="container-wide mb-20">
        <ScrollReveal className="mb-10">
          <p className="section-label mb-4">What We Believe</p>
          <h2 className="text-3xl md:text-4xl font-bold">Our Values</h2>
        </ScrollReveal>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {[
            { title: 'Architectural Integrity', text: 'We only represent properties of genuine architectural merit — homes designed with intention and built to last.' },
            { title: 'Curatorial Service', text: 'We treat each client relationship as a long-term partnership, not a transaction. Our process is personal and unhurried.' },
            { title: 'Discretion & Trust', text: 'We operate with complete confidentiality. Your privacy and your interests are our highest priority.' },
          ].map((value, i) => (
            <ScrollReveal key={value.title} delay={i * 120}>
              <div className="rounded-card border border-charcoal/40 p-8 h-full">
                <div className="text-3xl font-bold text-amber font-sans mb-4">0{i + 1}</div>
                <h3 className="text-lg font-semibold text-parchment mb-3">{value.title}</h3>
                <p className="text-sm leading-relaxed text-stone">{value.text}</p>
              </div>
            </ScrollReveal>
          ))}
        </div>
      </section>

      {/* Stats */}
      <section className="container-wide mb-20">
        <ScrollReveal className="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
          {[
            { value: '200+', label: 'Properties Curated' },
            { value: '15', label: 'Years of Service' },
            { value: '98%', label: 'Client Satisfaction' },
            { value: '12', label: 'Cities Served' },
          ].map((stat) => (
            <div key={stat.label}>
              <p className="text-4xl md:text-5xl font-bold text-amber font-sans mb-2">{stat.value}</p>
              <p className="text-xs uppercase tracking-wide text-stone-dark">{stat.label}</p>
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
