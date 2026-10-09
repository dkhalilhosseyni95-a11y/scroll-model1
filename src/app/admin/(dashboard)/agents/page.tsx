import { prisma } from '@/lib/prisma';
import AgentsManager from '@/components/admin/AgentsManager';

export default async function AdminAgentsPage() {
  const agents = await prisma.agent.findMany({
    orderBy: { createdAt: 'asc' },
    select: {
      id: true, name: true, slug: true, biography: true, profileImage: true,
      email: true, phone: true, specialties: true, activeStatus: true,
    },
  });

  return <AgentsManager agents={agents} />;
}
