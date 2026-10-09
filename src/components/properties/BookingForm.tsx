'use client';

import { useState } from 'react';
import { Loader2, Calendar } from 'lucide-react';
import { toast } from '@/components/ui/Toaster';

export default function BookingForm({ propertyId, propertyTitle }: { propertyId: string; propertyTitle: string }) {
  const [form, setForm] = useState({
    customerName: '',
    email: '',
    phone: '',
    preferredDate: '',
    preferredTime: '',
    message: '',
  });
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
    if (!form.phone.trim() || form.phone.trim().length < 5) {
      toast('Please enter a valid phone number.', 'error');
      return;
    }
    if (!form.preferredDate) {
      toast('Please select a preferred date.', 'error');
      return;
    }
    if (!form.preferredTime) {
      toast('Please select a preferred time.', 'error');
      return;
    }

    setLoading(true);
    try {
      const res = await fetch('/api/viewings', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...form, propertyId }),
      });
      const data = await res.json();
      if (res.ok) {
        toast(data.message || 'Viewing request submitted.', 'success');
        setSent(true);
        setForm({ customerName: '', email: '', phone: '', preferredDate: '', preferredTime: '', message: '' });
      } else {
        toast(data.error || 'Failed to submit request.', 'error');
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
        <Calendar className="h-10 w-10 text-amber mx-auto mb-4" />
        <p className="text-amber font-medium mb-2">Request Received</p>
        <p className="text-sm text-slate mb-4">
          Your viewing request for <span className="text-navy font-medium">{propertyTitle}</span> has been submitted.
          We will confirm your appointment shortly.
        </p>
        <button
          onClick={() => setSent(false)}
          className="text-sm text-amber hover:text-amber-light transition-colors"
        >
          Book another viewing
        </button>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label className="block text-xs uppercase tracking-wide text-slate-light mb-2">Full Name *</label>
          <input
            type="text"
            value={form.customerName}
            onChange={(e) => setForm({ ...form, customerName: e.target.value })}
            disabled={loading}
            className="input-field"
          />
        </div>
        <div>
          <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Email *</label>
          <input
            type="email"
            value={form.email}
            onChange={(e) => setForm({ ...form, email: e.target.value })}
            disabled={loading}
            className="input-field"
          />
        </div>
        <div>
          <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Phone *</label>
          <input
            type="tel"
            value={form.phone}
            onChange={(e) => setForm({ ...form, phone: e.target.value })}
            disabled={loading}
            className="input-field"
          />
        </div>
        <div>
          <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Preferred Date *</label>
          <input
            type="date"
            value={form.preferredDate}
            onChange={(e) => setForm({ ...form, preferredDate: e.target.value })}
            disabled={loading}
            min={new Date().toISOString().split('T')[0]}
            className="input-field"
          />
        </div>
        <div>
          <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Preferred Time *</label>
          <select
            value={form.preferredTime}
            onChange={(e) => setForm({ ...form, preferredTime: e.target.value })}
            disabled={loading}
            className="input-field cursor-pointer"
          >
            <option value="">Select a time</option>
            <option value="Morning (9-12)">Morning (9-12)</option>
            <option value="Afternoon (12-15)">Afternoon (12-15)</option>
            <option value="Late Afternoon (15-18)">Late Afternoon (15-18)</option>
          </select>
        </div>
      </div>
      <div>
        <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Message (optional)</label>
        <textarea
          value={form.message}
          onChange={(e) => setForm({ ...form, message: e.target.value })}
          disabled={loading}
          rows={3}
          className="input-field resize-none"
          placeholder="Any specific questions or requests?"
        />
      </div>
      <button type="submit" disabled={loading} className="btn-amber w-full">
        {loading ? (
          <><Loader2 className="h-4 w-4 animate-spin" /> Submitting...</>
        ) : (
          <>Request Viewing <Calendar className="h-4 w-4" /></>
        )}
      </button>
    </form>
  );
}
