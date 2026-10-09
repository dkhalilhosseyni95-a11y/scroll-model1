import Image from 'next/image';
import ScrollReveal from '@/components/ui/ScrollReveal';

export default function BrandStory() {
  return (
    <section className="py-section bg-gradient-to-b from-void to-charcoal/20">
      <div className="container-wide">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
          {/* Image */}
          <ScrollReveal className="relative aspect-[4/5] rounded-card overflow-hidden">
            <Image
              src="https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=1200&q=80"
              alt="Architectural detail of a modern villa with organic concrete forms"
              fill
              sizes="(max-width: 1024px) 100vw, 50vw"
              className="object-cover"
            />
            <div className="absolute inset-0 bg-gradient-to-t from-void/40 to-transparent" />
          </ScrollReveal>

          {/* Copy */}
          <ScrollReveal delay={150}>
            <p className="section-label mb-6">Our Philosophy</p>
            <h2 className="text-4xl md:text-5xl lg:text-6xl leading-[1.05] mb-8">
              <span className="heading-sans">Spaces</span>{' '}
              <span className="heading-serif">That Move You.</span>
            </h2>
            <div className="space-y-5 text-base leading-relaxed text-stone max-w-lg">
              <p>
                At HORIZON PROPERTIES, we believe a home is more than an address — it is a
                work of architecture that shapes daily life. We partner with
                visionary architects and discerning owners to curate properties
                of genuine distinction.
              </p>
              <p>
                Our approach is rooted in long-term relationships, thoughtful
                service, and a deep respect for the craft of building. Every
                property in our portfolio is selected for its architectural
                integrity, its sense of place, and its capacity to inspire.
              </p>
            </div>
            <div className="mt-10 grid grid-cols-3 gap-6 max-w-md">
              <div>
                <p className="text-3xl font-bold text-amber font-sans">200+</p>
                <p className="text-xs uppercase tracking-wide text-stone-dark mt-1">Properties Curated</p>
              </div>
              <div>
                <p className="text-3xl font-bold text-amber font-sans">15</p>
                <p className="text-xs uppercase tracking-wide text-stone-dark mt-1">Years of Service</p>
              </div>
              <div>
                <p className="text-3xl font-bold text-amber font-sans">98%</p>
                <p className="text-xs uppercase tracking-wide text-stone-dark mt-1">Client Satisfaction</p>
              </div>
            </div>
          </ScrollReveal>
        </div>
      </div>
    </section>
  );
}
