'use client';

import { useState } from 'react';
import { Trash2, Loader2, CalendarCheck } from 'lucide-react';
import { toast } from '@/components/ui/Toaster';

interface Viewing {
  id: string;
  customerName: string;
  email: string;
  phone: string;
  preferredDate: string;
  preferredTime: string;
  message: string | null;
  status: string;
  createdAt: string;
  property: { title: string } | null;
}

const statuses = ['PENDING', 'CONFIRMED', 'COMPLETED', 'CANCELLED'];

export default function ViewingsManager({ viewings }: { viewings: Viewing[] }) {
  const [items, setItems] = useState(viewings);
  const [loadingId, setLoadingId] = useState<string | null>(null);

  const updateStatus = async (id: string, status: string) => {
    setLoadingId(id);
    try {
      const res = await fetch(`/api/admin/viewings/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status }),
      });
      if (res.ok) {
        setItems((prev) => prev.map((i) => (i.id === id ? { ...i, status } : i)));
        toast('Status updated.', 'success');
      } else {
        toast('Failed to update status.', 'error');
      }
    } catch {
      toast('Network error.', 'error');
    } finally {
      setLoadingId(null);
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm('Delete this viewing request?')) return;
    setLoadingId(id);
    try {
      const res = await fetch(`/api/admin/viewings/${id}`, { method: 'DELETE' });
      if (res.ok) {
        setItems((prev) => prev.filter((i) => i.id !== id));
        toast('Viewing deleted.', 'success');
      } else {
        toast('Failed to delete.', 'error');
      }
    } catch {
      toast('Network error.', 'error');
    } finally {
      setLoadingId(null);
    }
  };

  return (
    <div>
      <div className="mb-6">
        <h1 className="text-3xl font-bold text-parchment mb-1">Viewing Requests</h1>
        <p className="text-sm text-stone-dark">{items.length} total requests</p>
      </div>

      <div className="space-y-4">
        {items.map((v) => (
          <div key={v.id} className="rounded-card border border-charcoal/40 p-5">
            <div className="flex items-start justify-between gap-4 mb-3">
              <div className="flex items-start gap-3 min-w-0">
                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-amber/10 shrink-0">
                  <CalendarCheck className="h-5 w-5 text-amber" />
                </div>
                <div className="min-w-0">
                  <p className="font-semibold text-parchment">{v.customerName}</p>
                  <p className="text-xs text-stone-dark">{v.email} · {v.phone}</p>
                  <p className="text-xs text-amber mt-1">{v.property?.title || 'Property'}</p>
                </div>
              </div>
              <div className="flex items-center gap-2 shrink-0">
                <select
                  value={v.status}
                  onChange={(e) => updateStatus(v.id, e.target.value)}
                  disabled={loadingId === v.id}
                  className={`text-xs rounded-full px-3 py-1.5 border-0 cursor-pointer ${
                    v.status === 'PENDING' ? 'bg-amber/10 text-amber' :
                    v.status === 'CONFIRMED' ? 'bg-green-500/10 text-green-400' :
                    v.status === 'COMPLETED' ? 'bg-blue-500/10 text-blue-400' :
                    'bg-red-500/10 text-red-400'
                  }`}
                >
                  {statuses.map((s) => <option key={s} value={s}>{s}</option>)}
                </select>
                <button onClick={() => handleDelete(v.id)} disabled={loadingId === v.id} className="p-2 text-stone hover:text-red-400 transition-colors" aria-label="Delete">
                  {loadingId === v.id ? <Loader2 className="h-4 w-4 animate-spin" /> : <Trash2 className="h-4 w-4" />}
                </button>
              </div>
            </div>
            <div className="flex flex-wrap gap-4 text-sm text-stone bg-charcoal/20 rounded-lg p-3">
              <span><span className="text-stone-dark">Date:</span> {v.preferredDate}</span>
              <span><span className="text-stone-dark">Time:</span> {v.preferredTime}</span>
            </div>
            {v.message && <p className="text-sm text-stone mt-2">{v.message}</p>}
            <p className="text-xs text-stone-dark mt-2">{new Date(v.createdAt).toLocaleDateString()}</p>
          </div>
        ))}
        {items.length === 0 && (
          <div className="text-center py-16">
            <CalendarCheck className="h-12 w-12 text-charcoal mx-auto mb-4" />
            <p className="text-stone">No viewing requests yet.</p>
          </div>
        )}
      </div>
    </div>
  );
}
