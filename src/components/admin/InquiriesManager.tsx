'use client';

import { useState } from 'react';
import { Trash2, Loader2, Mail } from 'lucide-react';
import { toast } from '@/components/ui/Toaster';

interface Inquiry {
  id: string;
  customerName: string;
  email: string;
  phone: string | null;
  message: string;
  status: string;
  createdAt: string;
  property: { title: string } | null;
}

const statuses = ['PENDING', 'REPLIED', 'CLOSED'];

export default function InquiriesManager({ inquiries }: { inquiries: Inquiry[] }) {
  const [items, setItems] = useState(inquiries);
  const [loadingId, setLoadingId] = useState<string | null>(null);

  const updateStatus = async (id: string, status: string) => {
    setLoadingId(id);
    try {
      const res = await fetch(`/api/admin/inquiries/${id}`, {
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
    if (!confirm('Delete this inquiry?')) return;
    setLoadingId(id);
    try {
      const res = await fetch(`/api/admin/inquiries/${id}`, { method: 'DELETE' });
      if (res.ok) {
        setItems((prev) => prev.filter((i) => i.id !== id));
        toast('Inquiry deleted.', 'success');
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
        <h1 className="text-3xl font-bold text-parchment mb-1">Inquiries</h1>
        <p className="text-sm text-stone-dark">{items.length} total inquiries</p>
      </div>

      <div className="space-y-4">
        {items.map((inq) => (
          <div key={inq.id} className="rounded-card border border-charcoal/40 p-5">
            <div className="flex items-start justify-between gap-4 mb-3">
              <div className="flex items-start gap-3 min-w-0">
                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-amber/10 shrink-0">
                  <Mail className="h-5 w-5 text-amber" />
                </div>
                <div className="min-w-0">
                  <p className="font-semibold text-parchment">{inq.customerName}</p>
                  <p className="text-xs text-stone-dark">{inq.email}{inq.phone ? ` · ${inq.phone}` : ''}</p>
                  <p className="text-xs text-amber mt-1">{inq.property?.title || 'General inquiry'}</p>
                </div>
              </div>
              <div className="flex items-center gap-2 shrink-0">
                <select
                  value={inq.status}
                  onChange={(e) => updateStatus(inq.id, e.target.value)}
                  disabled={loadingId === inq.id}
                  className={`text-xs rounded-full px-3 py-1.5 border-0 cursor-pointer ${
                    inq.status === 'PENDING' ? 'bg-amber/10 text-amber' :
                    inq.status === 'REPLIED' ? 'bg-green-500/10 text-green-400' :
                    'bg-charcoal text-stone-dark'
                  }`}
                >
                  {statuses.map((s) => <option key={s} value={s}>{s}</option>)}
                </select>
                <button onClick={() => handleDelete(inq.id)} disabled={loadingId === inq.id} className="p-2 text-stone hover:text-red-400 transition-colors" aria-label="Delete">
                  {loadingId === inq.id ? <Loader2 className="h-4 w-4 animate-spin" /> : <Trash2 className="h-4 w-4" />}
                </button>
              </div>
            </div>
            <p className="text-sm text-stone bg-charcoal/20 rounded-lg p-3">{inq.message}</p>
            <p className="text-xs text-stone-dark mt-2">{new Date(inq.createdAt).toLocaleDateString()}</p>
          </div>
        ))}
        {items.length === 0 && (
          <div className="text-center py-16">
            <Mail className="h-12 w-12 text-charcoal mx-auto mb-4" />
            <p className="text-stone">No inquiries yet.</p>
          </div>
        )}
      </div>
    </div>
  );
}
