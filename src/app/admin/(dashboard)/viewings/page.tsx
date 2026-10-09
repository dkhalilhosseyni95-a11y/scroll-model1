import { prisma } from '@/lib/prisma';
import ViewingsManager from '@/components/admin/ViewingsManager';

export default async function AdminViewingsPage() {
  const viewings = await prisma.viewingRequest.findMany({
    orderBy: { createdAt: 'desc' },
    include: { property: { select: { title: true } } },
  });

  const serialized = viewings.map((v) => ({
    ...v,
    createdAt: v.createdAt.toISOString(),
  }));

  return <ViewingsManager viewings={serialized as any} />;
}
