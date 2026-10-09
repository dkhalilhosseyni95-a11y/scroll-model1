import { prisma } from '@/lib/prisma';
import BookingForm from '@/components/properties/BookingForm';
import PropertySelector from '@/components/properties/PropertySelector';
import ScrollReveal from '@/components/ui/ScrollReveal';
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Book a Viewing',
  description: 'Schedule a private viewing of a HORIZON PROPERTIES luxury property.',
};

export default async function BookViewingPage({
  searchParams,
}: {
  searchParams: { property?: string };
}) {
  let selectedProperty: { id: string; title: string } | null = null;

  if (searchParams.property) {
    selectedProperty = await prisma.property.findUnique({
      where: { slug: searchParams.property },
      select: { id: true, title: true },
    });
  }

  const properties = await prisma.property.findMany({
    where: { status: 'AVAILABLE' },
    orderBy: { createdAt: 'desc' },
    select: { id: true, title: true, slug: true, location: true },
  });

  return (
    <div className="pt-28 pb-section">
      <div className="container-wide max-w-3xl">
        <ScrollReveal className="mb-10">
          <p className="section-label mb-4">Private Viewing</p>
          <h1 className="text-4xl md:text-5xl lg:text-6xl leading-[1.05]">
            <span className="heading-sans">Book a</span>{' '}
            <span className="heading-serif">Viewing</span>
          </h1>
          <p className="text-lg text-stone mt-6 leading-relaxed">
            Schedule a private viewing of any property in our portfolio. Our team
            will confirm your appointment and provide architectural context during
            your visit.
          </p>
        </ScrollReveal>

        <ScrollReveal delay={150}>
          <div className="rounded-card border border-line bg-white p-6 md:p-8 shadow-[0_10px_40px_-30px_rgba(11,31,51,0.3)]">
            {selectedProperty ? (
              <div className="mb-6 pb-6 border-b border-line">
                <p className="text-xs uppercase tracking-wide text-slate-light mb-1">Selected Property</p>
                <p className="text-lg font-semibold text-navy">{selectedProperty.title}</p>
              </div>
            ) : (
              <div className="mb-6 pb-6 border-b border-line">
                <label className="block text-xs uppercase tracking-wide text-slate-light mb-2">
                  Select a Property
                </label>
                <PropertySelector properties={properties} />
              </div>
            )}

            {selectedProperty ? (
              <BookingForm propertyId={selectedProperty.id} propertyTitle={selectedProperty.title} />
            ) : (
              <div className="text-center py-12">
                <p className="text-slate">
                  Please select a property above to book a viewing.
                </p>
              </div>
            )}
          </div>
        </ScrollReveal>
      </div>
    </div>
  );
}
