'use client';

import { useState } from 'react';
import { Loader2, Send } from 'lucide-react';
import { toast } from '@/components/ui/Toaster';

export default function InquiryForm({ propertyId }: { propertyId: string }) {
  const [form, setForm] = useState({ customerName: '', email: '', phone: '', message: '' });
  const [loading, setLoading] = useState(false);
  const [sent, setSent] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!form.customerName.trim() || form.customerName.trim().length < 2) {
      toast('Please enter your name.', 'error');
      return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
      toast('Please enter a valid email address.', 'error');
      return;
    }
    if (!form.message.trim() || form.message.trim().length < 5) {
      toast('Please enter a message (at least 5 characters).', 'error');
      return;
    }

    setLoading(true);
    try {
      const res = await fetch('/api/inquiries', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...form, propertyId }),
      });
      const data = await res.json();
      if (res.ok) {
        toast(data.message || 'Inquiry sent successfully.', 'success');
        setSent(true);
        setForm({ customerName: '', email: '', phone: '', message: '' });
      } else {
        toast(data.error || 'Failed to send inquiry.', 'error');
      }
    } catch {
      toast('Network error. Please try again.', 'error');
    } finally {
      setLoading(false);
    }
  };

  if (sent) {
    return (
      <div className="rounded-card border border-amber/30 bg-amber/5 p-6 text-center">
        <p className="text-amber font-medium mb-2">Inquiry Sent</p>
        <p className="text-sm text-stone mb-4">
          Thank you for your interest. We will be in touch shortly.
        </p>
        <button
          onClick={() => setSent(false)}
          className="text-sm text-amber hover:text-amber-light transition-colors"
        >
          Send another inquiry
        </button>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-3">
      <div>
        <input
          type="text"
          placeholder="Your Name"
          value={form.customerName}
          onChange={(e) => setForm({ ...form, customerName: e.target.value })}
          disabled={loading}
          className="input-field"
          aria-label="Your name"
        />
      </div>
      <div>
        <input
          type="email"
          placeholder="Email Address"
          value={form.email}
          onChange={(e) => setForm({ ...form, email: e.target.value })}
          disabled={loading}
          className="input-field"
          aria-label="Email address"
        />
      </div>
      <div>
        <input
          type="tel"
          placeholder="Phone (optional)"
          value={form.phone}
          onChange={(e) => setForm({ ...form, phone: e.target.value })}
          disabled={loading}
          className="input-field"
          aria-label="Phone number"
        />
      </div>
      <div>
        <textarea
          placeholder="Your Message"
          value={form.message}
          onChange={(e) => setForm({ ...form, message: e.target.value })}
          disabled={loading}
          rows={3}
          className="input-field resize-none"
          aria-label="Your message"
        />
      </div>
      <button type="submit" disabled={loading} className="btn-amber w-full">
        {loading ? (
          <><Loader2 className="h-4 w-4 animate-spin" /> Sending...</>
        ) : (
          <>Send Inquiry <Send className="h-4 w-4" /></>
        )}
      </button>
    </form>
  );
}
