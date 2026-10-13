import Link from 'next/link';
import Logo from '@/components/ui/Logo';
import NewsletterForm from './NewsletterForm';

const footerNav = [
  { label: 'Home', href: '/' },
  { label: 'Listings', href: '/properties' },
  { label: 'About', href: '/about' },
  { label: 'Contact', href: '/contact' },
];

const footerSocials = [
  { label: 'Twitter', href: '#' },
  { label: 'LinkedIn', href: '#' },
  { label: 'Instagram', href: '#' },
  { label: 'Facebook', href: '#' },
];

function FooterLabel({ children }: { children: string }) {
  return (
    <p className="mb-4 text-[11px] font-medium uppercase tracking-[0.25em] text-stone-dark">
      &bull; {children}
    </p>
  );
}

export default function Footer() {
  const year = new Date().getFullYear();

  return (
    <footer className="bg-void pt-16 md:pt-20">
      <div className="container-wide">
        {/* Top: brand + contact, navigation, socials */}
        <div className="grid grid-cols-1 gap-10 md:grid-cols-12 md:gap-8">
          <div className="md:col-span-5">
            <div className="text-parchment">
              <Logo />
            </div>
            <div className="mt-8">
              <FooterLabel>Contact us</FooterLabel>
              <a
                href="mailto:info@housen.com"
                className="text-xl text-parchment transition-colors hover:text-amber md:text-2xl"
              >
                info@housen.com
              </a>
            </div>
          </div>

          <div className="md:col-span-3">
            <FooterLabel>Navigation</FooterLabel>
            <ul className="space-y-2.5">
              {footerNav.map((link) => (
                <li key={link.href}>
                  <Link
                    href={link.href}
                    className="text-sm text-stone transition-colors hover:text-parchment"
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          <div className="md:col-span-4">
            <FooterLabel>Socials</FooterLabel>
            <ul className="space-y-2.5">
              {footerSocials.map((social) => (
                <li key={social.label}>
                  <a
                    href={social.href}
                    className="text-sm text-stone transition-colors hover:text-parchment"
                  >
                    {social.label}
                  </a>
                </li>
              ))}
            </ul>
          </div>
        </div>

        {/* Newsletter row */}
        <div className="mt-14 flex flex-col gap-5 border-t border-charcoal/40 pt-8 md:flex-row md:items-center">
          <p className="shrink-0 text-[11px] font-medium uppercase tracking-[0.25em] text-stone-dark md:w-48">
            &bull; Newsletter
          </p>
          <div className="max-w-xl flex-1">
            <NewsletterForm />
          </div>
        </div>

        {/* Giant wordmark */}
        <div className="mt-8 overflow-hidden">
          <p className="select-none text-center font-sans text-[clamp(4rem,17.5vw,17rem)] font-bold uppercase leading-[0.85] tracking-tight text-parchment">
            Housen
          </p>
        </div>
      </div>

      {/* Bottom bar */}
      <div className="border-t border-charcoal/40">
        <div className="container-wide flex flex-col items-center justify-between gap-3 py-5 text-[11px] uppercase tracking-wider text-stone-dark md:flex-row">
          <p>&copy; {year} Housen, All Rights Reserved</p>
          <div className="flex gap-6">
            <Link href="/privacy" className="transition-colors hover:text-amber">
              Privacy Policy
            </Link>
            <Link href="/terms" className="transition-colors hover:text-amber">
              Terms of Service
            </Link>
          </div>
        </div>
      </div>
    </footer>
  );
}
