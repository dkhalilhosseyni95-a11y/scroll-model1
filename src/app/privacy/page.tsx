import ScrollReveal from '@/components/ui/ScrollReveal';
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Privacy Policy',
  description: 'HORIZON PROPERTIES privacy policy — how we collect, use, and protect your data.',
};

export default function PrivacyPage() {
  const sections = [
    { title: 'Information We Collect', text: 'We collect information you provide directly to us, such as your name, email address, phone number, and any messages you send through our contact or inquiry forms. We also collect information automatically, such as your IP address, browser type, and usage data, through cookies and similar technologies.' },
    { title: 'How We Use Your Information', text: 'We use your information to respond to inquiries, provide our services, send newsletters (if you subscribe), improve our website, and comply with legal obligations. We do not sell your personal information to third parties.' },
    { title: 'Data Storage and Security', text: 'Your data is stored securely using industry-standard encryption. Access to personal data is restricted to authorized personnel only. We retain your information for as long as necessary to provide our services or as required by law.' },
    { title: 'Cookies', text: 'We use cookies to enhance your browsing experience, analyze website traffic, and remember your preferences. You can control cookies through your browser settings, but disabling them may affect website functionality.' },
    { title: 'Third-Party Services', text: 'We may use third-party services for analytics, email delivery, and other functions. These services have their own privacy policies, and we encourage you to review them.' },
    { title: 'Your Rights', text: 'You have the right to access, correct, or delete your personal information. You may also unsubscribe from our newsletter at any time. To exercise these rights, please contact us.' },
    { title: 'Changes to This Policy', text: 'We may update this privacy policy from time to time. We will notify you of significant changes by posting a notice on our website or by email.' },
  ];

  return (
    <div className="pt-28 pb-section">
      <div className="container-wide max-w-3xl">
        <ScrollReveal>
          <p className="section-label mb-4">Legal</p>
          <h1 className="text-4xl md:text-5xl font-bold mb-8">Privacy Policy</h1>
          <p className="text-sm text-stone-dark mb-10">Last updated: {new Date().getFullYear()}</p>
        </ScrollReveal>

        <ScrollReveal delay={100}>
          <div className="space-y-8">
            <p className="text-base leading-relaxed text-stone">
              This privacy policy describes how HORIZON PROPERTIES collects, uses, and protects
              your personal information when you use our website and services. This
              is a demonstration website and this policy is provided for illustrative
              purposes only.
            </p>
            {sections.map((section) => (
              <div key={section.title}>
                <h2 className="text-xl font-semibold text-ink mb-3">{section.title}</h2>
                <p className="text-base leading-relaxed text-stone">{section.text}</p>
              </div>
            ))}
            <div>
              <h2 className="text-xl font-semibold text-ink mb-3">Contact Us</h2>
              <p className="text-base leading-relaxed text-stone">
                If you have questions about this privacy policy, please contact us at{' '}
                <a href="mailto:contact@horizonproperties.com" className="text-amber hover:text-amber-light transition-colors">
                  contact@horizonproperties.com
                </a>
                .
              </p>
            </div>
          </div>
        </ScrollReveal>
      </div>
    </div>
  );
}
