import Link from 'next/link';
import { Home, ArrowLeft } from 'lucide-react';

export default function NotFound() {
  return (
    <div className="min-h-[70vh] flex items-center justify-center pt-28">
      <div className="container-wide text-center">
        <p className="font-sans font-bold text-[clamp(6rem,20vw,16rem)] leading-none text-charcoal/40">
          404
        </p>
        <h1 className="text-2xl md:text-3xl font-bold text-ink mt-4 mb-3">
          Page Not Found
        </h1>
        <p className="text-stone mb-8 max-w-md mx-auto">
          The page you are looking for may have been moved, deleted, or never existed.
        </p>
        <Link href="/" className="btn-amber group inline-flex">
          <ArrowLeft className="h-4 w-4 transition-transform group-hover:-translate-x-1" />
          Back to Home
        </Link>
      </div>
    </div>
  );
}
