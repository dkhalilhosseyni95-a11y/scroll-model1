'use client';

import { useState, useMemo } from 'react';
import { Search, SlidersHorizontal, X, MapPin, ArrowUpDown } from 'lucide-react';
import PropertyCard from '@/components/ui/PropertyCard';
import ScrollReveal from '@/components/ui/ScrollReveal';
import type { PropertyCardData } from '@/lib/types';

interface Filters {
  query: string;
  transactionType: string;
  propertyType: string;
  minPrice: string;
  maxPrice: string;
  bedrooms: string;
  minArea: string;
  maxArea: string;
  sortBy: string;
}

const propertyTypes = ['ALL', 'Villa', 'Apartment', 'Penthouse', 'Loft'];

export default function PropertiesClient({
  properties,
  initialType = '',
}: {
  properties: PropertyCardData[];
  initialType?: string;
}) {
  const [filters, setFilters] = useState<Filters>({
    query: '',
    transactionType: 'ALL',
    propertyType: initialType || 'ALL',
    minPrice: '',
    maxPrice: '',
    bedrooms: '0',
    minArea: '',
    maxArea: '',
    sortBy: 'newest',
  });
  const [showAdvanced, setShowAdvanced] = useState(false);

  const update = (key: keyof Filters, value: string) =>
    setFilters((prev) => ({ ...prev, [key]: value }));

  const filtered = useMemo(() => {
    let result = [...properties];

    if (filters.query.trim()) {
      const q = filters.query.toLowerCase();
      result = result.filter(
        (p) =>
          p.title.toLowerCase().includes(q) ||
          p.location.toLowerCase().includes(q) ||
          p.city.toLowerCase().includes(q) ||
          p.propertyType.toLowerCase().includes(q)
      );
    }
    if (filters.transactionType !== 'ALL')
      result = result.filter((p) => p.transactionType === filters.transactionType);
    if (filters.propertyType !== 'ALL')
      result = result.filter((p) => p.propertyType === filters.propertyType);
    if (filters.minPrice) result = result.filter((p) => p.price >= Number(filters.minPrice));
    if (filters.maxPrice) result = result.filter((p) => p.price <= Number(filters.maxPrice));
    if (filters.bedrooms !== '0')
      result = result.filter((p) => p.bedrooms >= Number(filters.bedrooms));
    if (filters.minArea) result = result.filter((p) => p.area >= Number(filters.minArea));
    if (filters.maxArea) result = result.filter((p) => p.area <= Number(filters.maxArea));

    switch (filters.sortBy) {
      case 'price-asc': result.sort((a, b) => a.price - b.price); break;
      case 'price-desc': result.sort((a, b) => b.price - a.price); break;
    }
    return result;
  }, [properties, filters]);

  const hasActiveFilters =
    filters.query ||
    filters.transactionType !== 'ALL' ||
    filters.propertyType !== 'ALL' ||
    filters.minPrice ||
    filters.maxPrice ||
    filters.bedrooms !== '0' ||
    filters.minArea ||
    filters.maxArea;

  const clearAll = () =>
    setFilters({
      query: '',
      transactionType: 'ALL',
      propertyType: 'ALL',
      minPrice: '',
      maxPrice: '',
      bedrooms: '0',
      minArea: '',
      maxArea: '',
      sortBy: 'newest',
    });

  return (
    <div className="pt-28 pb-section">
      <div className="container-wide">
        {/* Page header */}
        <div className="mb-10">
          <p className="section-label mb-4">Our Portfolio</p>
          <h1 className="text-4xl md:text-5xl lg:text-6xl leading-none">
            <span className="heading-sans">All</span>{' '}
            <span className="heading-serif">Properties</span>
          </h1>
        </div>

        {/* Filter bar */}
        <div className="rounded-card border border-charcoal/40 bg-void/60 backdrop-blur-sm p-4 md:p-6 mb-8">
          <div className="flex flex-col md:flex-row gap-3 md:items-center">
            <div className="relative flex-1">
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-stone-dark" />
              <input
                type="text"
                placeholder="Search by name, location, or city..."
                value={filters.query}
                onChange={(e) => update('query', e.target.value)}
                className="w-full rounded-xl border border-charcoal bg-void/60 pl-11 pr-4 py-3 text-sm text-parchment placeholder-stone-dark focus:border-amber focus:outline-none transition-colors"
              />
            </div>
            <div className="flex rounded-xl border border-charcoal overflow-hidden">
              {['ALL', 'BUY', 'RENT'].map((type) => (
                <button
                  key={type}
                  onClick={() => update('transactionType', type)}
                  className={`px-5 py-3 text-sm font-medium transition-colors ${
                    filters.transactionType === type ? 'bg-amber text-void' : 'text-stone hover:text-parchment'
                  }`}
                >
                  {type === 'ALL' ? 'All' : type === 'BUY' ? 'Buy' : 'Rent'}
                </button>
              ))}
            </div>
            <div className="relative">
              <ArrowUpDown className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-stone-dark pointer-events-none" />
              <select
                value={filters.sortBy}
                onChange={(e) => update('sortBy', e.target.value)}
                className="appearance-none rounded-xl border border-charcoal bg-void/60 pl-10 pr-8 py-3 text-sm text-parchment focus:border-amber focus:outline-none cursor-pointer"
              >
                <option value="newest">Newest</option>
                <option value="price-asc">Price: Low to High</option>
                <option value="price-desc">Price: High to Low</option>
              </select>
            </div>
            <button
              onClick={() => setShowAdvanced(!showAdvanced)}
              className={`flex items-center gap-2 rounded-xl border px-5 py-3 text-sm font-medium transition-colors ${
                showAdvanced || hasActiveFilters ? 'border-amber text-amber' : 'border-charcoal text-stone hover:text-parchment'
              }`}
            >
              <SlidersHorizontal className="h-4 w-4" />
              Filters
            </button>
          </div>

          {showAdvanced && (
            <div className="mt-4 pt-4 border-t border-charcoal/40 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div>
                <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Property Type</label>
                <select value={filters.propertyType} onChange={(e) => update('propertyType', e.target.value)} className="input-field cursor-pointer">
                  {propertyTypes.map((t) => <option key={t} value={t}>{t === 'ALL' ? 'All Types' : t}</option>)}
                </select>
              </div>
              <div>
                <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Bedrooms</label>
                <select value={filters.bedrooms} onChange={(e) => update('bedrooms', e.target.value)} className="input-field cursor-pointer">
                  <option value="0">Any</option>
                  {[1, 2, 3, 4, 5].map((n) => <option key={n} value={n}>{n}+</option>)}
                </select>
              </div>
              <div>
                <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Min Price ($)</label>
                <input type="number" placeholder="0" value={filters.minPrice} onChange={(e) => update('minPrice', e.target.value)} className="input-field" />
              </div>
              <div>
                <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Max Price ($)</label>
                <input type="number" placeholder="Any" value={filters.maxPrice} onChange={(e) => update('maxPrice', e.target.value)} className="input-field" />
              </div>
              <div>
                <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Min Area (sqft)</label>
                <input type="number" placeholder="0" value={filters.minArea} onChange={(e) => update('minArea', e.target.value)} className="input-field" />
              </div>
              <div>
                <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Max Area (sqft)</label>
                <input type="number" placeholder="Any" value={filters.maxArea} onChange={(e) => update('maxArea', e.target.value)} className="input-field" />
              </div>
            </div>
          )}

          <div className="mt-4 flex items-center justify-between">
            <p className="text-sm text-stone">
              <span className="font-semibold text-parchment">{filtered.length}</span> {filtered.length === 1 ? 'property' : 'properties'} found
            </p>
            {hasActiveFilters && (
              <button onClick={clearAll} className="flex items-center gap-1.5 text-sm text-amber hover:text-amber-light transition-colors">
                <X className="h-3.5 w-3.5" />
                Clear filters
              </button>
            )}
          </div>
        </div>

        {/* Results */}
        {filtered.length > 0 ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            {filtered.map((property, i) => (
              <ScrollReveal key={property.id} delay={Math.min(i * 80, 400)}>
                <PropertyCard property={property} />
              </ScrollReveal>
            ))}
          </div>
        ) : (
          <div className="text-center py-20">
            <MapPin className="h-12 w-12 text-charcoal mx-auto mb-4" />
            <h3 className="text-xl font-semibold text-parchment mb-2">No properties found</h3>
            <p className="text-stone mb-6">Try adjusting your filters to see more results.</p>
            <button onClick={clearAll} className="btn-outline">Clear all filters</button>
          </div>
        )}
      </div>
    </div>
  );
}
