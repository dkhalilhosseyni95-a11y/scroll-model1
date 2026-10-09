'use client';

import { useState } from 'react';
import Image from 'next/image';
import { Plus, Pencil, Trash2, X, Loader2 } from 'lucide-react';
import { toast } from '@/components/ui/Toaster';

interface Agent {
  id: string;
  name: string;
  slug: string;
  biography: string;
  profileImage: string;
  email: string;
  phone: string;
  specialties: string[];
  activeStatus: boolean;
}

interface AgentFormData {
  id?: string;
  name: string;
  biography: string;
  profileImage: string;
  email: string;
  phone: string;
  specialties: string;
  activeStatus: boolean;
}

const emptyForm: AgentFormData = {
  name: '', biography: '', profileImage: '', email: '', phone: '', specialties: '', activeStatus: true,
};

export default function AgentsManager({ agents }: { agents: Agent[] }) {
  const [showForm, setShowForm] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [form, setForm] = useState<AgentFormData>(emptyForm);
  const [loading, setLoading] = useState(false);
  const [confirmDelete, setConfirmDelete] = useState<string | null>(null);

  const openCreate = () => { setForm(emptyForm); setEditingId(null); setShowForm(true); };

  const openEdit = (a: Agent) => {
    setForm({
      id: a.id, name: a.name, biography: a.biography, profileImage: a.profileImage,
      email: a.email, phone: a.phone, specialties: a.specialties.join(', '), activeStatus: a.activeStatus,
    });
    setEditingId(a.id);
    setShowForm(true);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    const payload = {
      name: form.name, biography: form.biography, profileImage: form.profileImage,
      email: form.email, phone: form.phone,
      specialties: form.specialties.split(',').map((s) => s.trim()).filter(Boolean),
      activeStatus: form.activeStatus,
    };
    try {
      const url = editingId ? `/api/admin/agents/${editingId}` : '/api/admin/agents';
      const method = editingId ? 'PUT' : 'POST';
      const res = await fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
      const data = await res.json();
      if (res.ok) {
        toast(editingId ? 'Agent updated.' : 'Agent created.', 'success');
        setShowForm(false);
        window.location.reload();
      } else {
        toast(data.error || 'Failed to save agent.', 'error');
      }
    } catch {
      toast('Network error.', 'error');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id: string) => {
    setLoading(true);
    try {
      const res = await fetch(`/api/admin/agents/${id}`, { method: 'DELETE' });
      if (res.ok) {
        toast('Agent deleted.', 'success');
        setConfirmDelete(null);
        window.location.reload();
      } else {
        toast('Failed to delete agent.', 'error');
      }
    } catch {
      toast('Network error.', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-3xl font-bold text-parchment mb-1">Agents</h1>
          <p className="text-sm text-stone-dark">{agents.length} total agents</p>
        </div>
        <button onClick={openCreate} className="btn-amber">
          <Plus className="h-4 w-4" /> Add Agent
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {agents.map((agent) => (
          <div key={agent.id} className="rounded-card border border-charcoal/40 p-5">
            <div className="flex items-center gap-4 mb-3">
              <div className="relative h-14 w-14 rounded-full overflow-hidden shrink-0 bg-charcoal">
                <Image src={agent.profileImage} alt={agent.name} fill sizes="56px" className="object-cover" />
              </div>
              <div className="min-w-0">
                <p className="font-semibold text-parchment truncate">{agent.name}</p>
                <p className="text-xs text-stone-dark truncate">{agent.email}</p>
                <span className={`text-xs ${agent.activeStatus ? 'text-green-400' : 'text-stone-dark'}`}>
                  {agent.activeStatus ? 'Active' : 'Inactive'}
                </span>
              </div>
            </div>
            <p className="text-xs text-stone line-clamp-2 mb-3">{agent.biography}</p>
            <div className="flex gap-2">
              <button onClick={() => openEdit(agent)} className="p-2 text-stone hover:text-amber transition-colors" aria-label="Edit">
                <Pencil className="h-4 w-4" />
              </button>
              <button onClick={() => setConfirmDelete(agent.id)} className="p-2 text-stone hover:text-red-400 transition-colors" aria-label="Delete">
                <Trash2 className="h-4 w-4" />
              </button>
            </div>
          </div>
        ))}
      </div>

      {showForm && (
        <div className="fixed inset-0 z-[100] bg-void/80 backdrop-blur-sm flex items-start justify-center p-4 overflow-y-auto">
          <div className="w-full max-w-xl my-8 rounded-card border border-charcoal/40 bg-void p-6 md:p-8">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-xl font-semibold text-parchment">{editingId ? 'Edit Agent' : 'New Agent'}</h2>
              <button onClick={() => setShowForm(false)} className="text-stone hover:text-parchment"><X className="h-5 w-5" /></button>
            </div>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Name *</label>
                <input type="text" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required className="input-field" />
              </div>
              <div>
                <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Biography *</label>
                <textarea value={form.biography} onChange={(e) => setForm({ ...form, biography: e.target.value })} required rows={3} className="input-field resize-none" />
              </div>
              <div>
                <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Profile Image URL *</label>
                <input type="url" value={form.profileImage} onChange={(e) => setForm({ ...form, profileImage: e.target.value })} required className="input-field" placeholder="https://..." />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Email *</label>
                  <input type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required className="input-field" />
                </div>
                <div>
                  <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Phone *</label>
                  <input type="tel" value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} required className="input-field" />
                </div>
              </div>
              <div>
                <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Specialties (comma-separated)</label>
                <input type="text" value={form.specialties} onChange={(e) => setForm({ ...form, specialties: e.target.value })} className="input-field" placeholder="Villas, Penthouses" />
              </div>
              <label className="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" checked={form.activeStatus} onChange={(e) => setForm({ ...form, activeStatus: e.target.checked })} className="h-4 w-4 rounded border-charcoal accent-amber" />
                <span className="text-sm text-stone">Active agent</span>
              </label>
              <div className="flex gap-3 pt-2">
                <button type="submit" disabled={loading} className="btn-amber flex-1">
                  {loading ? <Loader2 className="h-4 w-4 animate-spin" /> : editingId ? 'Update Agent' : 'Create Agent'}
                </button>
                <button type="button" onClick={() => setShowForm(false)} className="btn-outline">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {confirmDelete && (
        <div className="fixed inset-0 z-[100] bg-void/80 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-sm rounded-card border border-charcoal/40 bg-void p-6 text-center">
            <Trash2 className="h-10 w-10 text-red-400 mx-auto mb-4" />
            <h3 className="text-lg font-semibold text-parchment mb-2">Delete Agent?</h3>
            <p className="text-sm text-stone mb-6">This action cannot be undone.</p>
            <div className="flex gap-3">
              <button onClick={() => handleDelete(confirmDelete)} disabled={loading} className="flex-1 rounded-full bg-red-500/20 text-red-400 px-5 py-3 text-sm font-medium hover:bg-red-500/30">
                {loading ? <Loader2 className="h-4 w-4 animate-spin mx-auto" /> : 'Delete'}
              </button>
              <button onClick={() => setConfirmDelete(null)} className="btn-outline flex-1">Cancel</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
