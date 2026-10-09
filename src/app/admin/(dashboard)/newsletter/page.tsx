import { prisma } from '@/lib/prisma';
import NewsletterManager from '@/components/admin/NewsletterManager';

export default async function AdminNewsletterPage() {
  const subscribers = await prisma.newsletterSubscriber.findMany({
    orderBy: { createdAt: 'desc' },
  });

  const serialized = subscribers.map((s) => ({
    ...s,
    createdAt: s.createdAt.toISOString(),
  }));

  return <NewsletterManager subscribers={serialized as any} />;
}
