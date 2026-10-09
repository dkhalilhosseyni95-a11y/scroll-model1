'use client';

import { useEffect } from 'react';

/**
 * Marks <html> once React has hydrated. The CSS failsafe in globals.css
 * (which reveals server-rendered content if JS is slow or broken) keys off this.
 */
export default function HydrationFlag() {
  useEffect(() => {
    document.documentElement.dataset.hydrated = 'true';
  }, []);
  return null;
}
