'use client';

import { useEffect, useState } from 'react';
import { CheckCircle, AlertCircle, X } from 'lucide-react';

type ToastType = 'success' | 'error';
interface ToastItem {
  id: number;
  message: string;
  type: ToastType;
}

export function toast(message: string, type: ToastType = 'success') {
  if (typeof window !== 'undefined') {
    window.dispatchEvent(
      new CustomEvent('housen-toast', { detail: { message, type } })
    );
  }
}

export default function Toaster() {
  const [toasts, setToasts] = useState<ToastItem[]>([]);

  useEffect(() => {
    const handler = (e: Event) => {
      const detail = (e as CustomEvent).detail as { message: string; type: ToastType };
      const id = Date.now() + Math.random();
      setToasts((prev) => [...prev, { id, ...detail }]);
      setTimeout(() => {
        setToasts((prev) => prev.filter((t) => t.id !== id));
      }, 4500);
    };
    window.addEventListener('housen-toast', handler);
    return () => window.removeEventListener('housen-toast', handler);
  }, []);

  return (
    <div className="fixed bottom-6 right-6 z-[200] flex flex-col gap-3 pointer-events-none">
      {toasts.map((t) => (
        <div
          key={t.id}
          className="pointer-events-auto flex items-start gap-3 rounded-2xl border border-charcoal bg-void/95 px-5 py-4 shadow-2xl backdrop-blur-md animate-fade-up max-w-sm"
        >
          {t.type === 'success' ? (
            <CheckCircle className="h-5 w-5 shrink-0 text-amber mt-0.5" />
          ) : (
            <AlertCircle className="h-5 w-5 shrink-0 text-red-400 mt-0.5" />
          )}
          <p className="text-sm text-parchment leading-snug">{t.message}</p>
          <button
            onClick={() => setToasts((prev) => prev.filter((x) => x.id !== t.id))}
            className="text-stone-dark hover:text-parchment transition-colors"
            aria-label="Dismiss"
          >
            <X className="h-4 w-4" />
          </button>
        </div>
      ))}
    </div>
  );
}
