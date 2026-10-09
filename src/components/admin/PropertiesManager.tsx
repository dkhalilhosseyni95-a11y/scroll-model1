'use client';

import { useState } from 'react';
import Image from 'next/image';
import { Plus, Pencil, Trash2, X, Loader2, Search } from 'lucide-react';
import { toast } from '@/components/ui/Toaster';
import { formatPrice } from '@/lib/utils';
import type { PropertyCardData } from '@/lib/types';

interface AgentOption {
  id: string;
  name: string;
}

interface PropertyFormData {
  id?: string;
  title: string;
  description: string;
  price: string;
  transactionType: string;
  propertyType: string;
  location: string;
  city: string;
  bedrooms: string;
  bathrooms: string;
  area: string;
  images: string;
  featured: boolean;
  status: string;
  amenities: string;
  agentId: string;
}

const emptyForm: PropertyFormData = {
  title: '', description: '', price: '', transactionType: 'BUY',
  propertyType: 'Villa', location: '', city: '', bedrooms: '0',
  bathrooms: '0', area: '0', images: '', featured: false,
  status: 'AVAILABLE', amenities: '', agentId: '',
};

export default function PropertiesManager({
  properties,
  agents,
}: {
  properties: (PropertyCardData & { agentId?: string | null })[];
  agents: AgentOption[];
}) {
  const [showForm, setShowForm] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [form, setForm] = useState<PropertyFormData>(emptyForm);
  const [loading, setLoading] = useState(false);
  const [search, setSearch] = useState('');
  const [confirmDelete, setConfirmDelete] = useState<string | null>(null);

  const filtered = properties.filter(
    (p) => p.title.toLowerCase().includes(search.toLowerCase()) || p.location.toLowerCase().includes(search.toLowerCase())
  );

  const openCreate = () => {
    setForm(emptyForm);
    setEditingId(null);
    setShowForm(true);
  };

  const openEdit = (p: any) => {
    setForm({
      id: p.id,
      title: p.title,
      description: p.description,
      price: String(p.price),
      transactionType: p.transactionType,
      propertyType: p.propertyType,
      location: p.location,
      city: p.city,
      bedrooms: String(p.bedrooms),
      bathrooms: String(p.bathrooms),
      area: String(p.area),
      images: (p.images || []).join('\n'),
      featured: p.featured,
      status: p.status,
      amenities: (p.amenities || []).join(', '),
      agentId: p.agentId || '',
    });
    setEditingId(p.id);
    setShowForm(true);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    const payload = {
      title: form.title,
      description: form.description,
      price: Number(form.price),
      transactionType: form.transactionType,
      propertyType: form.propertyType,
      location: form.location,
      city: form.city,
      bedrooms: Number(form.bedrooms),
      bathrooms: Number(form.bathrooms),
      area: Number(form.area),
      images: form.images.split('\n').map((s) => s.trim()).filter(Boolean),
      featured: form.featured,
      status: form.status,
      amenities: form.amenities.split(',').map((s) => s.trim()).filter(Boolean),
      agentId: form.agentId || null,
    };

    try {
      const url = editingId ? `/api/admin/properties/${editingId}` : '/api/admin/properties';
      const method = editingId ? 'PUT' : 'POST';
      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (res.ok) {
        toast(editingId ? 'Property updated successfully.' : 'Property created successfully.', 'success');
        setShowForm(false);
        window.location.reload();
      } else {
        toast(data.error || 'Failed to save property.', 'error');
      }
    } catch {
      toast('Network error. Please try again.', 'error');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id: string) => {
    setLoading(true);
    try {
      const res = await fetch(`/api/admin/properties/${id}`, { method: 'DELETE' });
      if (res.ok) {
        toast('Property deleted.', 'success');
        setConfirmDelete(null);
        window.location.reload();
      } else {
        toast('Failed to delete property.', 'error');
      }
    } catch {
      toast('Network error.', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      {/* Header */}
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-3xl font-bold text-parchment mb-1">Properties</h1>
          <p className="text-sm text-stone-dark">{properties.length} total properties</p>
        </div>
        <button onClick={openCreate} className="btn-amber">
          <Plus className="h-4 w-4" /> Add Property
        </button>
      </div>

      {/* Search */}
      <div className="relative mb-6 max-w-md">
        <Search className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-stone-dark" />
        <input
          type="text"
          placeholder="Search properties..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="input-field pl-11"
        />
      </div>

      {/* Table */}
      <div className="rounded-card border border-charcoal/40 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-charcoal/40 bg-charcoal/20">
                <th className="text-left text-xs uppercase tracking-wide text-stone-dark px-4 py-3 font-medium">Property</th>
                <th className="text-left text-xs uppercase tracking-wide text-stone-dark px-4 py-3 font-medium hidden md:table-cell">Type</th>
                <th className="text-left text-xs uppercase tracking-wide text-stone-dark px-4 py-3 font-medium">Price</th>
                <th className="text-left text-xs uppercase tracking-wide text-stone-dark px-4 py-3 font-medium hidden lg:table-cell">Status</th>
                <th className="text-right text-xs uppercase tracking-wide text-stone-dark px-4 py-3 font-medium">Actions</th>
              </tr>
            </thead>
            <tbody>
              {filtered.map((p) => (
                <tr key={p.id} className="border-b border-charcoal/30 last:border-0 hover:bg-charcoal/10 transition-colors">
                  <td className="px-4 py-3">
                    <div className="flex items-center gap-3">
                      <div className="relative h-12 w-16 rounded-lg overflow-hidden shrink-0 bg-charcoal">
                        {p.images[0] && (
                          <Image src={p.images[0]} alt={p.title} fill sizes="64px" className="object-cover" />
                        )}
                      </div>
                      <div className="min-w-0">
                        <p className="text-sm font-medium text-parchment truncate">{p.title}</p>
                        <p className="text-xs text-stone-dark truncate">{p.location}</p>
                      </div>
                    </div>
                  </td>
                  <td className="px-4 py-3 hidden md:table-cell">
                    <span className="text-sm text-stone">{p.propertyType}</span>
                  </td>
                  <td className="px-4 py-3">
                    <span className="text-sm font-medium text-amber">{formatPrice(p.price, p.transactionType)}</span>
                  </td>
                  <td className="px-4 py-3 hidden lg:table-cell">
                    <span className={`text-xs px-2 py-1 rounded-full ${
                      p.status === 'AVAILABLE' ? 'bg-green-500/10 text-green-400' :
                      p.status === 'SOLD' ? 'bg-red-500/10 text-red-400' :
                      p.status === 'RENTED' ? 'bg-blue-500/10 text-blue-400' :
                      'bg-charcoal text-stone-dark'
                    }`}>
                      {p.status}
                    </span>
                  </td>
                  <td className="px-4 py-3">
                    <div className="flex items-center justify-end gap-2">
                      <button onClick={() => openEdit(p)} className="p-2 text-stone hover:text-amber transition-colors" aria-label="Edit">
                        <Pencil className="h-4 w-4" />
                      </button>
                      <button onClick={() => setConfirmDelete(p.id)} className="p-2 text-stone hover:text-red-400 transition-colors" aria-label="Delete">
                        <Trash2 className="h-4 w-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
              {filtered.length === 0 && (
                <tr>
                  <td colSpan={5} className="px-4 py-12 text-center text-stone-dark text-sm">No properties found.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Create/Edit modal */}
      {showForm && (
        <div className="fixed inset-0 z-[100] bg-void/80 backdrop-blur-sm flex items-start justify-center p-4 overflow-y-auto">
          <div className="w-full max-w-2xl my-8 rounded-card border border-charcoal/40 bg-void p-6 md:p-8">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-xl font-semibold text-parchment">{editingId ? 'Edit Property' : 'New Property'}</h2>
              <button onClick={() => setShowForm(false)} className="text-stone hover:text-parchment transition-colors">
                <X className="h-5 w-5" />
              </button>
            </div>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="md:col-span-2">
                  <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Title *</label>
                  <input type="text" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} required className="input-field" />
                </div>
                <div className="md:col-span-2">
                  <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Description *</label>
                  <textarea value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} required rows={4} className="input-field resize-none" />
                </div>
                <div>
                  <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Price *</label>
                  <input type="number" value={form.price} onChange={(e) => setForm({ ...form, price: e.target.value })} required min="0" className="input-field" />
                </div>
                <div>
                  <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Transaction Type</label>
                  <select value={form.transactionType} onChange={(e) => setForm({ ...form, transactionType: e.target.value })} className="input-field cursor-pointer">
                    <option value="BUY">Buy</option>
                    <option value="RENT">Rent</option>
                  </select>
                </div>
                <div>
                  <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Property Type *</label>
                  <select value={form.propertyType} onChange={(e) => setForm({ ...form, propertyType: e.target.value })} className="input-field cursor-pointer">
                    <option value="Villa">Villa</option>
                    <option value="Apartment">Apartment</option>
                    <option value="Penthouse">Penthouse</option>
                    <option value="Loft">Loft</option>
                  </select>
                </div>
                <div>
                  <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Status</label>
                  <select value={form.status} onChange={(e) => setForm({ ...form, status: e.target.value })} className="input-field cursor-pointer">
                    <option value="AVAILABLE">Available</option>
                    <option value="SOLD">Sold</option>
                    <option value="RENTED">Rented</option>
                    <option value="OFF_MARKET">Off Market</option>
                  </select>
                </div>
                <div>
                  <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Location *</label>
                  <input type="text" value={form.location} onChange={(e) => setForm({ ...form, location: e.target.value })} required className="input-field" />
                </div>
                <div>
                  <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">City *</label>
                  <input type="text" value={form.city} onChange={(e) => setForm({ ...form, city: e.target.value })} required className="input-field" />
                </div>
                <div>
                  <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Bedrooms</label>
                  <input type="number" value={form.bedrooms} onChange={(e) => setForm({ ...form, bedrooms: e.target.value })} min="0" className="input-field" />
                </div>
                <div>
                  <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Bathrooms</label>
                  <input type="number" value={form.bathrooms} onChange={(e) => setForm({ ...form, bathrooms: e.target.value })} min="0" className="input-field" />
                </div>
                <div>
                  <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Area (sqft)</label>
                  <input type="number" value={form.area} onChange={(e) => setForm({ ...form, area: e.target.value })} min="0" className="input-field" />
                </div>
                <div>
                  <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Agent</label>
                  <select value={form.agentId} onChange={(e) => setForm({ ...form, agentId: e.target.value })} className="input-field cursor-pointer">
                    <option value="">No agent</option>
                    {agents.map((a) => <option key={a.id} value={a.id}>{a.name}</option>)}
                  </select>
                </div>
                <div className="md:col-span-2">
                  <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Image URLs (one per line)</label>
                  <textarea value={form.images} onChange={(e) => setForm({ ...form, images: e.target.value })} rows={3} className="input-field resize-none" placeholder="https://images.unsplash.com/..." />
                </div>
                <div className="md:col-span-2">
                  <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Amenities (comma-separated)</label>
                  <input type="text" value={form.amenities} onChange={(e) => setForm({ ...form, amenities: e.target.value })} className="input-field" placeholder="Pool, Garage, Gym" />
                </div>
                <div className="md:col-span-2">
                  <label className="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" checked={form.featured} onChange={(e) => setForm({ ...form, featured: e.target.checked })} className="h-4 w-4 rounded border-charcoal accent-amber" />
                    <span className="text-sm text-stone">Featured property</span>
                  </label>
                </div>
              </div>
              <div className="flex gap-3 pt-4">
                <button type="submit" disabled={loading} className="btn-amber flex-1">
                  {loading ? <><Loader2 className="h-4 w-4 animate-spin" /> Saving...</> : editingId ? 'Update Property' : 'Create Property'}
                </button>
                <button type="button" onClick={() => setShowForm(false)} className="btn-outline">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Delete confirmation */}
      {confirmDelete && (
        <div className="fixed inset-0 z-[100] bg-void/80 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-sm rounded-card border border-charcoal/40 bg-void p-6 text-center">
            <Trash2 className="h-10 w-10 text-red-400 mx-auto mb-4" />
            <h3 className="text-lg font-semibold text-parchment mb-2">Delete Property?</h3>
            <p className="text-sm text-stone mb-6">This action cannot be undone.</p>
            <div className="flex gap-3">
              <button onClick={() => handleDelete(confirmDelete)} disabled={loading} className="flex-1 rounded-full bg-red-500/20 text-red-400 px-5 py-3 text-sm font-medium hover:bg-red-500/30 transition-colors">
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
