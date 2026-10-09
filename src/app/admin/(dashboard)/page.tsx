import { prisma } from '@/lib/prisma';
import Link from 'next/link';
import { Building2, Users, Mail, CalendarCheck, Newspaper, ArrowRight, Clock } from 'lucide-react';
import { formatPrice } from '@/lib/utils';

export default async function AdminDashboard() {
  const [
    propertyCount, agentCount, pendingInquiries, pendingViewings,
    subscriberCount, recentInquiries, recentViewings,
  ] = await Promise.all([
    prisma.property.count(),
    prisma.agent.count(),
    prisma.inquiry.count({ where: { status: 'PENDING' } }),
    prisma.viewingRequest.count({ where: { status: 'PENDING' } }),
    prisma.newsletterSubscriber.count({ where: { subscriptionStatus: 'ACTIVE' } }),
    prisma.inquiry.findMany({ take: 5, orderBy: { createdAt: 'desc' }, include: { property: { select: { title: true } } } }),
    prisma.viewingRequest.findMany({ take: 5, orderBy: { createdAt: 'desc' }, include: { property: { select: { title: true } } } }),
  ]);

  const stats = [
    { label: 'Properties', value: propertyCount, icon: Building2, href: '/admin/properties', color: 'text-amber' },
    { label: 'Agents', value: agentCount, icon: Users, href: '/admin/agents', color: 'text-amber' },
    { label: 'Pending Inquiries', value: pendingInquiries, icon: Mail, href: '/admin/inquiries', color: 'text-amber' },
    { label: 'Pending Viewings', value: pendingViewings, icon: CalendarCheck, href: '/admin/viewings', color: 'text-amber' },
    { label: 'Subscribers', value: subscriberCount, icon: Newspaper, href: '/admin/newsletter', color: 'text-amber' },
  ];

  return (
    <div>
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-parchment mb-2">Dashboard</h1>
        <p className="text-stone-dark text-sm">Overview of your HORIZON PROPERTIES platform</p>
      </div>

      {/* Stats grid */}
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-10">
        {stats.map((stat) => (
          <Link
            key={stat.label}
            href={stat.href}
            className="rounded-card border border-charcoal/40 p-5 hover:border-amber/40 transition-colors group"
          >
            <stat.icon className={`h-6 w-6 ${stat.color} mb-3`} />
            <p className="text-3xl font-bold text-parchment">{stat.value}</p>
            <p className="text-xs uppercase tracking-wide text-stone-dark mt-1">{stat.label}</p>
          </Link>
        ))}
      </div>

      {/* Recent activity */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Recent inquiries */}
        <div className="rounded-card border border-charcoal/40 p-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-semibold text-parchment">Recent Inquiries</h2>
            <Link href="/admin/inquiries" className="text-sm text-amber hover:text-amber-light transition-colors flex items-center gap-1">
              View all <ArrowRight className="h-3 w-3" />
            </Link>
          </div>
          {recentInquiries.length > 0 ? (
            <div className="space-y-3">
              {recentInquiries.map((inq) => (
                <div key={inq.id} className="flex items-center gap-3 py-3 border-b border-charcoal/30 last:border-0">
                  <Mail className="h-4 w-4 text-amber shrink-0" />
                  <div className="min-w-0 flex-1">
                    <p className="text-sm text-parchment truncate">{inq.customerName} — {inq.property?.title}</p>
                    <p className="text-xs text-stone-dark truncate">{inq.email}</p>
                  </div>
                  <span className={`text-xs px-2 py-1 rounded-full shrink-0 ${
                    inq.status === 'PENDING' ? 'bg-amber/10 text-amber' : 'bg-charcoal text-stone-dark'
                  }`}>
                    {inq.status}
                  </span>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-sm text-stone-dark py-4">No inquiries yet.</p>
          )}
        </div>

        {/* Recent viewings */}
        <div className="rounded-card border border-charcoal/40 p-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-semibold text-parchment">Recent Viewing Requests</h2>
            <Link href="/admin/viewings" className="text-sm text-amber hover:text-amber-light transition-colors flex items-center gap-1">
              View all <ArrowRight className="h-3 w-3" />
            </Link>
          </div>
          {recentViewings.length > 0 ? (
            <div className="space-y-3">
              {recentViewings.map((v) => (
                <div key={v.id} className="flex items-center gap-3 py-3 border-b border-charcoal/30 last:border-0">
                  <Clock className="h-4 w-4 text-amber shrink-0" />
                  <div className="min-w-0 flex-1">
                    <p className="text-sm text-parchment truncate">{v.customerName} — {v.property?.title}</p>
                    <p className="text-xs text-stone-dark truncate">{v.preferredDate} · {v.preferredTime}</p>
                  </div>
                  <span className={`text-xs px-2 py-1 rounded-full shrink-0 ${
                    v.status === 'PENDING' ? 'bg-amber/10 text-amber' : 'bg-charcoal text-stone-dark'
                  }`}>
                    {v.status}
                  </span>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-sm text-stone-dark py-4">No viewing requests yet.</p>
          )}
        </div>
      </div>
    </div>
  );
}
