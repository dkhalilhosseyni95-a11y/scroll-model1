'use client';

import { useState } from 'react';
import { toast } from '@/components/ui/Toaster';
import { ArrowRight, Loader2 } from 'lucide-react';

export default function NewsletterForm() {
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const trimmed = email.trim();

    if (!trimmed || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed)) {
      toast('Please enter a valid email address.', 'error');
      return;
    }

    setLoading(true);
    try {
      const res = await fetch('/api/newsletter', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: trimmed }),
      });
      const data = await res.json();

      if (res.ok) {
        toast(data.message || 'Subscribed successfully. Welcome to HOUSEN.', 'success');
        setEmail('');
      } else {
        toast(data.error || 'Subscription failed. Please try again.', 'error');
      }
    } catch {
      toast('Network error. Please try again.', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="relative w-full">
      <input
        type="email"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        placeholder="Enter your email"
        aria-label="Email for newsletter"
        disabled={loading}
        className="w-full rounded-full border border-charcoal bg-void/60 py-3 pl-5 pr-14 text-sm text-parchment placeholder-stone-dark focus:border-amber focus:outline-none transition-colors"
      />
      <button
        type="submit"
        disabled={loading}
        aria-label="Subscribe"
        className="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-full bg-amber p-2.5 text-void hover:bg-amber-light transition-colors disabled:opacity-50"
      >
        {loading ? (
          <Loader2 className="h-4 w-4 animate-spin" />
        ) : (
          <ArrowRight className="h-4 w-4" />
        )}
      </button>
    </form>
  );
}
