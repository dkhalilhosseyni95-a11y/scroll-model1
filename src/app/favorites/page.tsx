'use client';

import { useState, useEffect } from 'react';
import { useSession } from 'next-auth/react';
import Link from 'next/link';
import { Heart, Loader2 } from 'lucide-react';
import PropertyCard from '@/components/ui/PropertyCard';
import type { PropertyCardData } from '@/lib/types';

export default function FavoritesPage() {
  const { data: session, status } = useSession();
  const [favorites, setFavorites] = useState<PropertyCardData[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (status === 'authenticated') {
      fetch('/api/favorites')
        .then((res) => res.json())
        .then((data) => {
          setFavorites(data.favorites?.map((f: any) => f.property) || []);
          setLoading(false);
        })
        .catch(() => setLoading(false));
    } else if (status === 'unauthenticated') {
      setLoading(false);
    }
  }, [status]);

  if (status === 'loading' || loading) {
    return (
      <div className="pt-28 pb-section">
        <div className="container-wide text-center py-20">
          <Loader2 className="h-8 w-8 text-amber animate-spin mx-auto" />
        </div>
      </div>
    );
  }

  if (!session) {
    return (
      <div className="pt-28 pb-section">
        <div className="container-wide text-center py-20">
          <Heart className="h-12 w-12 text-line mx-auto mb-4" />
          <h1 className="text-2xl font-bold text-ink mb-3">Sign in to View Favorites</h1>
          <p className="text-slate mb-6">You need to be signed in to save and view your favorite properties.</p>
          <Link href="/admin/login" className="btn-amber">Sign In</Link>
        </div>
      </div>
    );
  }

  return (
    <div className="pt-28 pb-section">
      <div className="container-wide">
        <div className="mb-10">
          <p className="section-label mb-4">Your Collection</p>
          <h1 className="text-4xl md:text-5xl lg:text-6xl leading-none">
            <span className="heading-sans">Saved</span>{' '}
            <span className="heading-serif">Favorites</span>
          </h1>
          <p className="text-slate mt-4">{favorites.length} {favorites.length === 1 ? 'property' : 'properties'} saved</p>
        </div>

        {favorites.length > 0 ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            {favorites.map((property) => (
              <PropertyCard key={property.id} property={property} />
            ))}
          </div>
        ) : (
          <div className="text-center py-20">
            <Heart className="h-12 w-12 text-line mx-auto mb-4" />
            <h2 className="text-xl font-semibold text-ink mb-2">No favorites yet</h2>
            <p className="text-slate mb-6">Browse properties and save the ones you love.</p>
            <Link href="/properties" className="btn-amber">Browse Properties</Link>
          </div>
        )}
      </div>
    </div>
  );
}
