import ScrollReveal from '@/components/ui/ScrollReveal';
import ContactForm from '@/components/ui/ContactForm';
import { Mail, Phone, MapPin } from 'lucide-react';
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Contact',
  description: 'Get in touch with Horizon Properties — we are here to help you find your extraordinary home or investment.',
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
          <p className="text-lg text-slate max-w-2xl mt-6 leading-relaxed">
            Whether you are searching for your next home or considering selling a
            property of architectural distinction, we would be delighted to hear
            from you.
          </p>
        </ScrollReveal>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-10">
          {/* Form */}
          <ScrollReveal className="lg:col-span-2">
            <div className="rounded-card border border-line bg-white p-6 md:p-8 shadow-[0_10px_40px_-30px_rgba(11,31,51,0.3)]">
              <h2 className="text-xl font-semibold text-ink mb-6">Send a Message</h2>
              <ContactForm />
            </div>
          </ScrollReveal>

          {/* Info */}
          <ScrollReveal delay={150}>
            <div className="space-y-5">
              <div className="rounded-card border border-line bg-white p-6">
                <Mail className="h-6 w-6 text-champagne-dark mb-3" />
                <h3 className="text-xs font-medium uppercase tracking-wide text-slate-light mb-1">Email</h3>
                <a href="mailto:contact@horizonproperties.com" className="text-ink hover:text-champagne-dark transition-colors">
                  contact@horizonproperties.com
                </a>
              </div>
              <div className="rounded-card border border-line bg-white p-6">
                <Phone className="h-6 w-6 text-champagne-dark mb-3" />
                <h3 className="text-xs font-medium uppercase tracking-wide text-slate-light mb-1">Phone</h3>
                <a href="tel:+15552467890" className="text-ink hover:text-champagne-dark transition-colors">
                  (555) 246-7890
                </a>
              </div>
              <div className="rounded-card border border-line bg-white p-6">
                <MapPin className="h-6 w-6 text-champagne-dark mb-3" />
                <h3 className="text-xs font-medium uppercase tracking-wide text-slate-light mb-1">Office</h3>
                <p className="text-ink">1280 Coast Avenue, Suite 400</p>
                <p className="text-sm text-slate-light mt-1">Los Angeles, CA 90024</p>
              </div>
            </div>
          </ScrollReveal>
        </div>
      </div>
    </div>
  );
}
