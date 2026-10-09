'use client';

import { Share2 } from 'lucide-react';
import { toast } from '@/components/ui/Toaster';

export default function ShareButton() {
  const handleShare = async () => {
    const url = window.location.href;
    if (navigator.share) {
      try {
        await navigator.share({ url });
      } catch {}
    } else if (navigator.clipboard) {
      await navigator.clipboard.writeText(url);
      toast('Link copied to clipboard.', 'success');
    }
  };

  return (
    <button
      onClick={handleShare}
      className="ml-auto flex items-center gap-2 rounded-full border border-charcoal px-4 py-2 text-sm text-stone hover:border-amber hover:text-amber transition-all"
    >
      <Share2 className="h-4 w-4" />
      Share
    </button>
  );
}
