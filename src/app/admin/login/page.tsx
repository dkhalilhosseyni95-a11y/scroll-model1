'use client';

import { useState } from 'react';
import { signIn } from 'next-auth/react';
import { useRouter } from 'next/navigation';
import { Loader2, Lock, Mail } from 'lucide-react';
import Logo from '@/components/ui/Logo';
import { toast } from '@/components/ui/Toaster';

export default function AdminLoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const router = useRouter();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    const result = await signIn('credentials', {
      email,
      password,
      redirect: false,
    });

    if (result?.error) {
      toast('Invalid credentials. Please try again.', 'error');
      setLoading(false);
    } else {
      toast('Welcome back.', 'success');
      router.push('/admin');
      router.refresh();
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center px-6 bg-void">
      <div className="w-full max-w-md">
        <div className="text-center mb-10">
          <div className="inline-block text-parchment mb-6">
            <Logo />
          </div>
          <h1 className="text-3xl font-bold text-parchment mb-2">Admin Access</h1>
          <p className="text-sm text-stone-dark">Sign in to manage HOUSEN</p>
        </div>

        <form onSubmit={handleSubmit} className="rounded-card border border-charcoal/40 p-6 md:p-8 space-y-4">
          <div>
            <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Email</label>
            <div className="relative">
              <Mail className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-stone-dark" />
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                disabled={loading}
                className="input-field pl-11"
                placeholder="admin@housen.com"
                required
              />
            </div>
          </div>
          <div>
            <label className="block text-xs uppercase tracking-wide text-stone-dark mb-2">Password</label>
            <div className="relative">
              <Lock className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-stone-dark" />
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                disabled={loading}
                className="input-field pl-11"
                placeholder="••••••••"
                required
              />
            </div>
          </div>
          <button type="submit" disabled={loading} className="btn-amber w-full">
            {loading ? <><Loader2 className="h-4 w-4 animate-spin" /> Signing in...</> : 'Sign In'}
          </button>
        </form>

        <p className="text-center text-xs text-stone-dark mt-6">
          Demo credentials: admin@housen.com / Housen2024!
        </p>
        <p className="text-center mt-4">
          <a href="/" className="text-sm text-stone hover:text-amber transition-colors">← Back to website</a>
        </p>
      </div>
    </div>
  );
}
