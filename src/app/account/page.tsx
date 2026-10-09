'use client';

import { useSession } from 'next-auth/react';
import Link from 'next/link';
import { User, Heart, Mail, Shield, LogOut, Settings } from 'lucide-react';
import { signOut } from 'next-auth/react';

export default function AccountPage() {
  const { data: session, status } = useSession();

  if (status === 'loading') {
    return (
      <div className="pt-28 pb-section">
        <div className="container-wide text-center py-20">
          <div className="h-8 w-8 border-2 border-amber border-t-transparent rounded-full animate-spin mx-auto" />
        </div>
      </div>
    );
  }

  if (!session) {
    return (
      <div className="pt-28 pb-section">
        <div className="container-wide text-center py-20">
          <User className="h-12 w-12 text-charcoal mx-auto mb-4" />
          <h1 className="text-2xl font-bold text-parchment mb-3">Sign in to View Your Account</h1>
          <Link href="/admin/login" className="btn-amber">Sign In</Link>
        </div>
      </div>
    );
  }

  const isAdmin = (session.user as any).role === 'ADMIN';

  return (
    <div className="pt-28 pb-section">
      <div className="container-wide max-w-3xl">
        <div className="mb-10">
          <p className="section-label mb-4">Your Account</p>
          <h1 className="text-4xl md:text-5xl font-bold">
            <span className="heading-sans">Account</span>{' '}
            <span className="heading-serif">Settings</span>
          </h1>
        </div>

        {/* Profile card */}
        <div className="rounded-card border border-charcoal/40 p-6 md:p-8 mb-6">
          <div className="flex items-center gap-4 mb-6">
            <div className="flex h-16 w-16 items-center justify-center rounded-full bg-amber/10 border border-amber/30">
              <User className="h-8 w-8 text-amber" />
            </div>
            <div>
              <h2 className="text-xl font-semibold text-parchment">{session.user?.name || 'User'}</h2>
              <p className="text-sm text-stone-dark">{session.user?.email}</p>
              {isAdmin && (
                <span className="inline-flex items-center gap-1 mt-1 text-xs text-amber">
                  <Shield className="h-3 w-3" /> Administrator
                </span>
              )}
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 pt-6 border-t border-charcoal/40">
            <div className="flex items-center gap-3">
              <Mail className="h-5 w-5 text-amber" />
              <div>
                <p className="text-xs uppercase tracking-wide text-stone-dark">Email</p>
                <p className="text-sm text-parchment">{session.user?.email}</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <Settings className="h-5 w-5 text-amber" />
              <div>
                <p className="text-xs uppercase tracking-wide text-stone-dark">Role</p>
                <p className="text-sm text-parchment">{isAdmin ? 'Administrator' : 'User'}</p>
              </div>
            </div>
          </div>
        </div>

        {/* Quick links */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <Link href="/favorites" className="rounded-card border border-charcoal/40 p-6 hover:border-amber/40 transition-colors group">
            <Heart className="h-6 w-6 text-amber mb-3" />
            <h3 className="font-semibold text-parchment group-hover:text-amber transition-colors">My Favorites</h3>
            <p className="text-sm text-stone mt-1">View your saved properties</p>
          </Link>
          {isAdmin && (
            <Link href="/admin" className="rounded-card border border-charcoal/40 p-6 hover:border-amber/40 transition-colors group">
              <Shield className="h-6 w-6 text-amber mb-3" />
              <h3 className="font-semibold text-parchment group-hover:text-amber transition-colors">Admin Dashboard</h3>
              <p className="text-sm text-stone mt-1">Manage properties and inquiries</p>
            </Link>
          )}
        </div>

        {/* Sign out */}
        <button
          onClick={() => signOut({ callbackUrl: '/' })}
          className="flex items-center gap-2 rounded-full border border-charcoal px-5 py-2.5 text-sm text-stone hover:border-red-400 hover:text-red-400 transition-all"
        >
          <LogOut className="h-4 w-4" />
          Sign Out
        </button>
      </div>
    </div>
  );
}
