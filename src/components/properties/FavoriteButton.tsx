'use client';

import { useState, useEffect } from 'react';
import { Heart } from 'lucide-react';
import { useSession } from 'next-auth/react';
import { useRouter } from 'next/navigation';
import { toast } from '@/components/ui/Toaster';

export default function FavoriteButton({ propertyId }: { propertyId: string }) {
  const { data: session } = useSession();
  const router = useRouter();
  const [isFavorite, setIsFavorite] = useState(false);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!session) return;
    fetch(`/api/favorites/${propertyId}`)
      .then((res) => res.json())
      .then((data) => setIsFavorite(data.isFavorite))
      .catch(() => {});
  }, [propertyId, session]);

  const toggle = async () => {
    if (!session) {
      toast('Please sign in to save favorites.', 'error');
      router.push('/admin/login');
      return;
    }

    setLoading(true);
    try {
      if (isFavorite) {
        const res = await fetch(`/api/favorites/${propertyId}`, { method: 'DELETE' });
        if (res.ok) {
          setIsFavorite(false);
          toast('Removed from favorites.');
        }
      } else {
        const res = await fetch('/api/favorites', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ propertyId }),
        });
        if (res.ok) {
          setIsFavorite(true);
          toast('Added to favorites.');
        }
      }
    } catch {
      toast('Something went wrong. Please try again.', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      onClick={toggle}
      disabled={loading}
      className={`flex items-center gap-2 rounded-full border px-5 py-2.5 text-sm font-medium transition-all disabled:opacity-50 ${
        isFavorite
          ? 'border-amber bg-amber/10 text-amber'
          : 'border-charcoal text-stone hover:border-amber hover:text-amber'
      }`}
      aria-label={isFavorite ? 'Remove from favorites' : 'Add to favorites'}
    >
      <Heart className={`h-4 w-4 ${isFavorite ? 'fill-amber' : ''}`} />
      {isFavorite ? 'Saved' : 'Save'}
    </button>
  );
}
