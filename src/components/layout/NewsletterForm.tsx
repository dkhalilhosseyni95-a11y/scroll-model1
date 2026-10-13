'use client';

import { useState } from 'react';
import { toast } from '@/components/ui/Toaster';
import { ArrowUpRight, Loader2 } from 'lucide-react';

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
    <form onSubmit={handleSubmit} className="flex items-center gap-3">
      <input
        type="email"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        placeholder="Enter your email address"
        aria-label="Email for newsletter"
        disabled={loading}
        className="min-w-0 flex-1 rounded-full border border-charcoal bg-void/60 px-5 py-3 text-sm text-parchment placeholder-stone-dark transition-colors focus:border-amber focus:outline-none"
      />
      <button
        type="submit"
        disabled={loading}
        className="btn-amber shrink-0 text-sm disabled:opacity-50"
        aria-label="Submit newsletter subscription"
      >
        {loading ? (
          <Loader2 className="h-4 w-4 animate-spin" />
        ) : (
          <>
            Submit
            <ArrowUpRight className="h-4 w-4" />
          </>
        )}
      </button>
    </form>
  );
}
