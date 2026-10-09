'use client';

import { useEffect, useRef, ReactNode } from 'react';

interface ScrollRevealProps {
  children: ReactNode;
  className?: string;
  delay?: number;
  variant?: 'reveal' | 'line-draw';
  as?: 'div' | 'section' | 'span' | 'li';
}

export default function ScrollReveal({
  children,
  className = '',
  delay = 0,
  variant = 'reveal',
  as: Tag = 'div',
}: ScrollRevealProps) {
  const ref = useRef<HTMLElement>(null);

  useEffect(() => {
    const el = ref.current;
    if (!el) return;

    // Hydration was slow and the CSS failsafe already revealed this element: keep it visible.
    if (!el.classList.contains('is-visible') && getComputedStyle(el).opacity === '1') {
      el.classList.add('is-visible');
      return;
    }

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setTimeout(() => el.classList.add('is-visible'), delay);
          observer.unobserve(el);
        }
      },
      { threshold: 0.12, rootMargin: '0px 0px -60px 0px' }
    );

    observer.observe(el);
    return () => observer.disconnect();
  }, [delay]);

  return (
    <Tag ref={ref as any} className={`${variant} ${className}`}>
      {children}
    </Tag>
  );
}
