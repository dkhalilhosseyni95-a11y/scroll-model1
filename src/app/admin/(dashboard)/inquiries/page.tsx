import { prisma } from '@/lib/prisma';
import InquiriesManager from '@/components/admin/InquiriesManager';

export default async function AdminInquiriesPage() {
  const inquiries = await prisma.inquiry.findMany({
    orderBy: { createdAt: 'desc' },
    include: { property: { select: { title: true } } },
  });

  const serialized = inquiries.map((i) => ({
    ...i,
    createdAt: i.createdAt.toISOString(),
  }));

  return <InquiriesManager inquiries={serialized as any} />;
}
