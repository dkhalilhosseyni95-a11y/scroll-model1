import Image from 'next/image';
import Link from 'next/link';
import { ArrowRight } from 'lucide-react';
import ScrollReveal from '@/components/ui/ScrollReveal';

export default function WhoWeAre() {
  return (
    <section className="py-section bg-ivory">
      <div className="container-wide">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
          {/* Copy */}
          <ScrollReveal>
            <p className="section-label mb-6">About Us</p>
            <h2 className="text-4xl md:text-5xl lg:text-[3.5rem] leading-[1.05] mb-7">
              <span className="heading-sans">Who</span>{' '}
              <span className="heading-serif">We Are</span>
            </h2>
            <div className="space-y-5 text-base md:text-[1.05rem] leading-relaxed text-slate max-w-lg">
              <p>
                At Horizon Properties, we connect people with extraordinary homes
                and smart investments. Our team brings decades of combined
                experience across luxury residential sales, investment advisory,
                and architectural property marketing.
              </p>
              <p>
                Integrity, transparency, and client satisfaction are at the heart
                of everything we do. We take the time to understand your goals —
                whether you are searching for a forever home or building a
                long-term portfolio — and guide you with clarity at every step.
              </p>
            </div>
            <div className="mt-9">
              <Link
                href="/about"
                className="group inline-flex items-center gap-2.5 rounded-full border border-ink/15 px-7 py-3.5 text-sm font-medium text-ink hover:border-champagne hover:text-champagne-dark transition-all"
              >
                Learn More
                <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
              </Link>
            </div>

            {/* Stats */}
            <div className="mt-12 grid grid-cols-3 gap-6 max-w-md border-t border-line pt-8">
              <div>
                <p className="text-3xl md:text-4xl font-bold text-navy font-sans">200+</p>
                <p className="text-xs uppercase tracking-wide text-slate-light mt-1.5">
                  Properties Sold
                </p>
              </div>
              <div>
                <p className="text-3xl md:text-4xl font-bold text-navy font-sans">15</p>
                <p className="text-xs uppercase tracking-wide text-slate-light mt-1.5">
                  Years Experience
                </p>
              </div>
              <div>
                <p className="text-3xl md:text-4xl font-bold text-navy font-sans">98%</p>
                <p className="text-xs uppercase tracking-wide text-slate-light mt-1.5">
                  Client Satisfaction
                </p>
              </div>
            </div>
          </ScrollReveal>

          {/* Asymmetric image composition */}
          <ScrollReveal delay={150} className="relative">
            <div className="grid grid-cols-5 grid-rows-6 gap-4 h-[34rem] md:h-[40rem]">
              <div className="col-span-3 row-span-6 relative rounded-card overflow-hidden group">
                <Image
                  src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1000&q=80"
                  alt="Modern luxury home with pool and warm architectural lighting"
                  fill
                  sizes="(max-width: 1024px) 100vw, 60vw"
                  className="object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                />
              </div>
              <div className="col-span-2 row-span-4 col-start-4 row-start-1 relative rounded-card overflow-hidden group">
                <Image
                  src="https://images.unsplash.com/photo-1512917774083-611c87c178ce?auto=format&fit=crop&w=800&q=80"
                  alt="Vertical architectural detail of a contemporary residence"
                  fill
                  sizes="(max-width: 1024px) 100vw, 40vw"
                  className="object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                />
              </div>
              <div className="col-span-2 row-span-2 col-start-4 row-start-5 relative rounded-card overflow-hidden bg-navy flex items-center justify-center p-5">
                <div>
                  <p className="text-3xl font-bold text-champagne-light font-sans leading-none">
                    25+
                  </p>
                  <p className="text-xs uppercase tracking-wide text-ivory/60 mt-2">
                    Years of Trusted Service
                  </p>
                </div>
              </div>
            </div>

            {/* Floating circular arrow button */}
            <Link
              href="/about"
              aria-label="Learn more about Horizon Properties"
              className="group absolute -bottom-2 -left-2 md:bottom-6 md:left-6 flex h-16 w-16 items-center justify-center rounded-full bg-champagne text-navy shadow-xl hover:bg-champagne-light transition-all duration-300 hover:scale-105"
            >
              <ArrowRight className="h-6 w-6 transition-transform group-hover:translate-x-0.5" />
            </Link>
          </ScrollReveal>
        </div>
      </div>
    </section>
  );
}
