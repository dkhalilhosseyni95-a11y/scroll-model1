import Link from 'next/link';
import { ArrowRight, KeyRound } from 'lucide-react';
import ScrollReveal from '@/components/ui/ScrollReveal';

export default function CtaSection() {
  return (
    <section className="py-section bg-ivory">
      <div className="container-wide">
        <ScrollReveal>
          <div className="relative overflow-hidden rounded-card bg-mist border border-line px-6 py-14 md:px-16 md:py-20">
            <div className="grid grid-cols-1 md:grid-cols-12 gap-10 items-center">
              {/* Icon */}
              <div className="md:col-span-2 flex md:justify-start">
                <span className="flex h-20 w-20 items-center justify-center rounded-full bg-navy text-champagne-light shrink-0">
                  <KeyRound className="h-9 w-9" strokeWidth={1.4} />
                </span>
              </div>

              {/* Copy */}
              <div className="md:col-span-7">
                <h2 className="text-3xl md:text-4xl lg:text-5xl leading-[1.1] text-ink">
                  <span className="heading-serif">Ready to Find Your</span>{' '}
                  <span className="heading-sans">Perfect Property?</span>
                </h2>
                <p className="mt-4 text-base md:text-lg text-slate max-w-xl leading-relaxed">
                  Let our experts guide you to the right home or investment.
                </p>
              </div>

              {/* CTA */}
              <div className="md:col-span-3 md:flex md:justify-end">
                <Link href="/contact" className="btn-navy group">
                  Get in Touch
                  <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                </Link>
              </div>
            </div>
          </div>
        </ScrollReveal>
      </div>
    </section>
  );
}
