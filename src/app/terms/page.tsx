import ScrollReveal from '@/components/ui/ScrollReveal';
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Terms of Service',
  description: 'HOUSEN terms of service — the terms governing use of our website and services.',
};

export default function TermsPage() {
  const sections = [
    { title: 'Acceptance of Terms', text: 'By accessing and using this website, you accept and agree to be bound by these terms of service. If you do not agree, please do not use our website.' },
    { title: 'Use of Our Services', text: 'You may use our website for lawful purposes only. You agree not to misuse the site, attempt to gain unauthorized access, or interfere with its operation. All property listings are for demonstration purposes and do not represent actual properties for sale.' },
    { title: 'Property Information', text: 'While we strive to provide accurate information, property details including prices, specifications, and availability may change without notice. We recommend verifying all information before making decisions.' },
    { title: 'User Accounts', text: 'Certain features may require an account. You are responsible for maintaining the confidentiality of your login credentials and for all activities under your account.' },
    { title: 'Intellectual Property', text: 'All content on this website, including text, images, graphics, and design, is the property of HOUSEN or its licensors and is protected by copyright and other laws. You may not reproduce or distribute our content without permission.' },
    { title: 'Limitation of Liability', text: 'HOUSEN is not liable for any direct, indirect, incidental, or consequential damages arising from your use of our website or services. We provide our services on an "as is" basis without warranties of any kind.' },
    { title: 'Third-Party Links', text: 'Our website may contain links to third-party websites. We are not responsible for the content or practices of these external sites.' },
    { title: 'Changes to Terms', text: 'We may update these terms at any time. Continued use of the website after changes constitutes acceptance of the updated terms.' },
  ];

  return (
    <div className="pt-28 pb-section">
      <div className="container-wide max-w-3xl">
        <ScrollReveal>
          <p className="section-label mb-4">Legal</p>
          <h1 className="text-4xl md:text-5xl font-bold mb-8">Terms of Service</h1>
          <p className="text-sm text-stone-dark mb-10">Last updated: {new Date().getFullYear()}</p>
        </ScrollReveal>

        <ScrollReveal delay={100}>
          <div className="space-y-8">
            <p className="text-base leading-relaxed text-stone">
              These terms of service govern your use of the HOUSEN website. This is
              a demonstration website and these terms are provided for illustrative
              purposes only.
            </p>
            {sections.map((section) => (
              <div key={section.title}>
                <h2 className="text-xl font-semibold text-ink mb-3">{section.title}</h2>
                <p className="text-base leading-relaxed text-stone">{section.text}</p>
              </div>
            ))}
          </div>
        </ScrollReveal>
      </div>
    </div>
  );
}
