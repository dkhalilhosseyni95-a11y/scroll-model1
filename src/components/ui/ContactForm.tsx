'use client';

import { useState } from 'react';
import { Loader2, Send } from 'lucide-react';
import { toast } from '@/components/ui/Toaster';

export default function ContactForm() {
  const [form, setForm] = useState({ name: '', email: '', phone: '', subject: '', message: '' });
  const [loading, setLoading] = useState(false);
  const [sent, setSent] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!form.name.trim() || form.name.trim().length < 2) return toast('Please enter your name.', 'error');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) return toast('Please enter a valid email.', 'error');
    if (!form.subject.trim()) return toast('Please enter a subject.', 'error');
    if (!form.message.trim() || form.message.trim().length < 5) return toast('Please enter a message (at least 5 characters).', 'error');

    setLoading(true);
    try {
      const res = await fetch('/api/contact', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });
      const data = await res.json();
      if (res.ok) {
        toast(data.message || 'Message sent successfully.', 'success');
        setSent(true);
        setForm({ name: '', email: '', phone: '', subject: '', message: '' });
      } else {
        toast(data.error || 'Failed to send message.', 'error');
      }
    } catch {
      toast('Network error. Please try again.', 'error');
    } finally {
      setLoading(false);
    }
  };

  if (sent) {
    return (
      <div className="rounded-card border border-amber/30 bg-amber/5 p-8 text-center">
        <p className="text-amber font-medium mb-2">Message Sent</p>
        <p className="text-sm text-stone mb-4">Thank you for reaching out. We will respond shortly.</p>
        <button onClick={() => setSent(false)} className="text-sm text-amber hover:text-amber-light transition-colors">
          Send another message
        </button>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="text" placeholder="Your Name *" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} disabled={loading} className="input-field" aria-label="Your name" />
        <input type="email" placeholder="Email Address *" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} disabled={loading} className="input-field" aria-label="Email address" />
      </div>
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="tel" placeholder="Phone (optional)" value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} disabled={loading} className="input-field" aria-label="Phone number" />
        <input type="text" placeholder="Subject *" value={form.subject} onChange={(e) => setForm({ ...form, subject: e.target.value })} disabled={loading} className="input-field" aria-label="Subject" />
      </div>
      <textarea placeholder="Your Message *" value={form.message} onChange={(e) => setForm({ ...form, message: e.target.value })} disabled={loading} rows={5} className="input-field resize-none" aria-label="Your message" />
      <button type="submit" disabled={loading} className="btn-amber w-full md:w-auto">
        {loading ? <><Loader2 className="h-4 w-4 animate-spin" /> Sending...</> : <>Send Message <Send className="h-4 w-4" /></>}
      </button>
    </form>
  );
}
