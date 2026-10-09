import ScrollReveal from '@/components/ui/ScrollReveal';
import ContactForm from '@/components/ui/ContactForm';
import { Mail, Phone, MapPin } from 'lucide-react';
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Contact',
  description: 'Get in touch with HOUSEN — we are here to help you find your extraordinary home.',
};

export default function ContactPage() {
  return (
    <div className="pt-28 pb-section">
      <div className="container-wide">
        <ScrollReveal className="mb-12">
          <p className="section-label mb-4">Get in Touch</p>
          <h1 className="text-4xl md:text-6xl lg:text-7xl leading-[1.05]">
            <span className="heading-sans">Contact</span>{' '}
            <span className="heading-serif">Us</span>
          </h1>
          <p className="text-lg text-stone max-w-2xl mt-6 leading-relaxed">
            Whether you are searching for your next home or considering selling a
            property of architectural distinction, we would be delighted to hear
            from you.
          </p>
        </ScrollReveal>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-12">
          {/* Form */}
          <ScrollReveal className="lg:col-span-2">
            <div className="rounded-card border border-charcoal/40 p-6 md:p-8">
              <h2 className="text-xl font-semibold text-parchment mb-6">Send a Message</h2>
              <ContactForm />
            </div>
          </ScrollReveal>

          {/* Info */}
          <ScrollReveal delay={150}>
            <div className="space-y-6">
              <div className="rounded-card border border-charcoal/40 p-6">
                <Mail className="h-6 w-6 text-amber mb-3" />
                <h3 className="text-sm font-medium uppercase tracking-wide text-stone-dark mb-1">Email</h3>
                <a href="mailto:contact@housen.com" className="text-parchment hover:text-amber transition-colors">
                  contact@housen.com
                </a>
              </div>
              <div className="rounded-card border border-charcoal/40 p-6">
                <Phone className="h-6 w-6 text-amber mb-3" />
                <h3 className="text-sm font-medium uppercase tracking-wide text-stone-dark mb-1">Phone</h3>
                <p className="text-parchment">Available upon request</p>
              </div>
              <div className="rounded-card border border-charcoal/40 p-6">
                <MapPin className="h-6 w-6 text-amber mb-3" />
                <h3 className="text-sm font-medium uppercase tracking-wide text-stone-dark mb-1">Office</h3>
                <p className="text-parchment">By appointment only</p>
                <p className="text-sm text-stone-dark mt-1">Serving clients globally</p>
              </div>
            </div>
          </ScrollReveal>
        </div>
      </div>
    </div>
  );
}
