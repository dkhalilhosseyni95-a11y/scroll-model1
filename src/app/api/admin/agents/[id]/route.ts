import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { requireAdmin } from '@/lib/adminAuth';
import { z } from 'zod';

const updateSchema = z.object({
  name: z.string().min(2).optional(),
  biography: z.string().min(10).optional(),
  profileImage: z.string().url().optional(),
  email: z.string().email().optional(),
  phone: z.string().min(5).optional(),
  specialties: z.array(z.string()).optional(),
  activeStatus: z.boolean().optional(),
});

export async function PUT(
  req: NextRequest,
  { params }: { params: { id: string } }
) {
  const session = await requireAdmin();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });

  try {
    const body = await req.json();
    const parsed = updateSchema.safeParse(body);
    if (!parsed.success) {
      return NextResponse.json({ error: 'Invalid input' }, { status: 400 });
    }

    const agent = await prisma.agent.update({
      where: { id: params.id },
      data: parsed.data,
    });

    return NextResponse.json({ agent });
  } catch (error) {
    console.error('Admin agent update error:', error);
    return NextResponse.json({ error: 'Failed to update agent' }, { status: 500 });
  }
}

export async function DELETE(
  req: NextRequest,
  { params }: { params: { id: string } }
) {
  const session = await requireAdmin();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });

  try {
    await prisma.agent.delete({ where: { id: params.id } });
    return NextResponse.json({ message: 'Agent deleted' });
  } catch (error) {
    console.error('Admin agent delete error:', error);
    return NextResponse.json({ error: 'Failed to delete agent' }, { status: 500 });
  }
}
