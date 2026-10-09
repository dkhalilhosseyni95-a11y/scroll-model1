'use client';

import { useSession } from 'next-auth/react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { signOut } from 'next-auth/react';
import {
  LayoutDashboard, Building2, Users, Mail, CalendarCheck,
  Newspaper, LogOut, Loader2,
} from 'lucide-react';
import Logo from '@/components/ui/Logo';

const navItems = [
  { label: 'Dashboard', href: '/admin', icon: LayoutDashboard },
  { label: 'Properties', href: '/admin/properties', icon: Building2 },
  { label: 'Agents', href: '/admin/agents', icon: Users },
  { label: 'Inquiries', href: '/admin/inquiries', icon: Mail },
  { label: 'Viewings', href: '/admin/viewings', icon: CalendarCheck },
  { label: 'Newsletter', href: '/admin/newsletter', icon: Newspaper },
];

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  const { data: session, status } = useSession();
  const pathname = usePathname();

  if (status === 'loading') {
    return (
      <div className="min-h-screen flex items-center justify-center bg-void">
        <Loader2 className="h-8 w-8 text-amber animate-spin" />
      </div>
    );
  }

  return (
    <div className="min-h-screen flex bg-void">
      {/* Sidebar */}
      <aside className="w-64 shrink-0 border-r border-charcoal/40 flex flex-col fixed lg:sticky top-0 h-screen z-40 bg-void">
        <div className="p-6 border-b border-charcoal/40">
          <Link href="/admin" className="text-parchment">
            <Logo />
          </Link>
          <p className="text-xs text-stone-dark mt-2 uppercase tracking-wide">Admin Panel</p>
        </div>

        <nav className="flex-1 p-4 space-y-1 overflow-y-auto">
          {navItems.map((item) => {
            const active = pathname === item.href || (item.href !== '/admin' && pathname.startsWith(item.href));
            return (
              <Link
                key={item.href}
                href={item.href}
                className={`flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-colors ${
                  active ? 'bg-amber/10 text-amber' : 'text-stone hover:text-parchment hover:bg-charcoal/30'
                }`}
              >
                <item.icon className="h-4 w-4" />
                {item.label}
              </Link>
            );
          })}
        </nav>

        <div className="p-4 border-t border-charcoal/40">
          <div className="px-4 py-2 mb-2">
            <p className="text-xs text-stone-dark truncate">{session?.user?.email}</p>
          </div>
          <button
            onClick={() => signOut({ callbackUrl: '/admin/login' })}
            className="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-stone hover:text-red-400 transition-colors w-full"
          >
            <LogOut className="h-4 w-4" />
            Sign Out
          </button>
        </div>
      </aside>

      {/* Main content */}
      <div className="flex-1 lg:ml-0 min-w-0">
        <div className="p-6 md:p-8 lg:p-10 pt-20 lg:pt-8">
          {children}
        </div>
      </div>
    </div>
  );
}
