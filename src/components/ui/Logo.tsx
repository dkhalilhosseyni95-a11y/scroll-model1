interface LogoProps {
  className?: string;
  showMark?: boolean;
}

export default function Logo({ className = '', showMark = true }: LogoProps) {
  return (
    <span className={`inline-flex items-center gap-2 ${className}`}>
      {showMark && (
        <svg
          width="22"
          height="22"
          viewBox="0 0 22 22"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
          aria-hidden="true"
        >
          <path
            d="M3 19V9L11 3L19 9V19H14V13H8V19H3Z"
            stroke="currentColor"
            strokeWidth="1.5"
            strokeLinejoin="round"
          />
          <path d="M11 3V7" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
        </svg>
      )}
      <span className="font-sans font-bold uppercase tracking-[0.15em] text-[1.05rem] leading-none">
        Housen
      </span>
    </span>
  );
}
