interface LogoProps {
  className?: string;
  showMark?: boolean;
  variant?: 'light' | 'dark';
}

export default function Logo({ className = '', showMark = true, variant = 'light' }: LogoProps) {
  const textColor = variant === 'light' ? 'text-ivory' : 'text-navy';
  return (
    <span className={`inline-flex items-center gap-2.5 ${className}`}>
      {showMark && (
        <svg
          width="26"
          height="26"
          viewBox="0 0 26 26"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
          aria-hidden="true"
          className="text-champagne shrink-0"
        >
          <path
            d="M3 22V11L13 4L23 11V22"
            stroke="currentColor"
            strokeWidth="1.4"
            strokeLinejoin="round"
            strokeLinecap="round"
          />
          <path
            d="M9 22V15H17V22"
            stroke="currentColor"
            strokeWidth="1.4"
            strokeLinejoin="round"
            strokeLinecap="round"
          />
          <circle cx="13" cy="11" r="1.6" fill="currentColor" />
        </svg>
      )}
      <span className={`font-sans font-bold uppercase tracking-[0.18em] text-[0.95rem] leading-none ${textColor}`}>
        Horizon<span className="text-champagne"> Properties</span>
      </span>
    </span>
  );
}
