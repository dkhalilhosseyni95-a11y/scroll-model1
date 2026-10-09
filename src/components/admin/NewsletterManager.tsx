'use client';

import { useState } from 'react';
import { Trash2, Loader2, Newspaper } from 'lucide-react';
import { toast } from '@/components/ui/Toaster';

interface Subscriber {
  id: string;
  email: string;
  subscriptionStatus: string;
  createdAt: string;
}

export default function NewsletterManager({ subscribers }: { subscribers: Subscriber[] }) {
  const [items, setItems] = useState(subscribers);
  const [loadingId, setLoadingId] = useState<string | null>(null);

  const handleDelete = async (id: string) => {
    if (!confirm('Remove this subscriber?')) return;
    setLoadingId(id);
    try {
      const res = await fetch(`/api/admin/newsletter/${id}`, { method: 'DELETE' });
      if (res.ok) {
        setItems((prev) => prev.filter((s) => s.id !== id));
        toast('Subscriber removed.', 'success');
      } else {
        toast('Failed to remove subscriber.', 'error');
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
        <h1 className="text-3xl font-bold text-parchment mb-1">Newsletter Subscribers</h1>
        <p className="text-sm text-stone-dark">{items.length} total subscribers</p>
      </div>

      <div className="rounded-card border border-charcoal/40 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-charcoal/40 bg-charcoal/20">
                <th className="text-left text-xs uppercase tracking-wide text-stone-dark px-4 py-3 font-medium">Email</th>
                <th className="text-left text-xs uppercase tracking-wide text-stone-dark px-4 py-3 font-medium hidden md:table-cell">Status</th>
                <th className="text-left text-xs uppercase tracking-wide text-stone-dark px-4 py-3 font-medium hidden lg:table-cell">Subscribed</th>
                <th className="text-right text-xs uppercase tracking-wide text-stone-dark px-4 py-3 font-medium">Actions</th>
              </tr>
            </thead>
            <tbody>
              {items.map((sub) => (
                <tr key={sub.id} className="border-b border-charcoal/30 last:border-0">
                  <td className="px-4 py-3">
                    <div className="flex items-center gap-3">
                      <Newspaper className="h-4 w-4 text-amber shrink-0" />
                      <span className="text-sm text-parchment">{sub.email}</span>
                    </div>
                  </td>
                  <td className="px-4 py-3 hidden md:table-cell">
                    <span className={`text-xs px-2 py-1 rounded-full ${sub.subscriptionStatus === 'ACTIVE' ? 'bg-green-500/10 text-green-400' : 'bg-charcoal text-stone-dark'}`}>
                      {sub.subscriptionStatus}
                    </span>
                  </td>
                  <td className="px-4 py-3 hidden lg:table-cell">
                    <span className="text-sm text-stone-dark">{new Date(sub.createdAt).toLocaleDateString()}</span>
                  </td>
                  <td className="px-4 py-3 text-right">
                    <button onClick={() => handleDelete(sub.id)} disabled={loadingId === sub.id} className="p-2 text-stone hover:text-red-400 transition-colors" aria-label="Delete">
                      {loadingId === sub.id ? <Loader2 className="h-4 w-4 animate-spin" /> : <Trash2 className="h-4 w-4" />}
                    </button>
                  </td>
                </tr>
              ))}
              {items.length === 0 && (
                <tr><td colSpan={4} className="px-4 py-12 text-center text-stone-dark text-sm">No subscribers yet.</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
