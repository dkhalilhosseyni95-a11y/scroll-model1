import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { requireAdmin } from '@/lib/adminAuth';
import { z } from 'zod';

export async function PUT(
  req: NextRequest,
  { params }: { params: { id: string } }
) {
  const session = await requireAdmin();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });

  try {
    const body = await req.json();
    const schema = z.object({ status: z.enum(['PENDING', 'REPLIED', 'CLOSED']) });
    const parsed = schema.safeParse(body);
    if (!parsed.success) return NextResponse.json({ error: 'Invalid status' }, { status: 400 });

    const inquiry = await prisma.inquiry.update({
      where: { id: params.id },
      data: { status: parsed.data.status },
    });
    return NextResponse.json({ inquiry });
  } catch (error) {
    return NextResponse.json({ error: 'Failed to update inquiry' }, { status: 500 });
  }
}

export async function DELETE(
  req: NextRequest,
  { params }: { params: { id: string } }
) {
  const session = await requireAdmin();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });

  try {
    await prisma.inquiry.delete({ where: { id: params.id } });
    return NextResponse.json({ message: 'Inquiry deleted' });
  } catch (error) {
    return NextResponse.json({ error: 'Failed to delete inquiry' }, { status: 500 });
  }
}
