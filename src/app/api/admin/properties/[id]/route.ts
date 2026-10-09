import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { requireAdmin } from '@/lib/adminAuth';
import { z } from 'zod';

const updateSchema = z.object({
  title: z.string().min(2).optional(),
  description: z.string().min(10).optional(),
  price: z.number().min(0).optional(),
  transactionType: z.enum(['BUY', 'RENT']).optional(),
  propertyType: z.string().min(1).optional(),
  location: z.string().min(1).optional(),
  city: z.string().min(1).optional(),
  bedrooms: z.number().int().min(0).optional(),
  bathrooms: z.number().int().min(0).optional(),
  area: z.number().int().min(0).optional(),
  images: z.array(z.string()).optional(),
  featured: z.boolean().optional(),
  status: z.enum(['AVAILABLE', 'SOLD', 'RENTED', 'OFF_MARKET']).optional(),
  amenities: z.array(z.string()).optional(),
  agentId: z.string().nullable().optional(),
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
      return NextResponse.json({ error: parsed.error.errors[0]?.message || 'Invalid input' }, { status: 400 });
    }

    const property = await prisma.property.update({
      where: { id: params.id },
      data: parsed.data,
    });

    return NextResponse.json({ property });
  } catch (error) {
    console.error('Admin property update error:', error);
    return NextResponse.json({ error: 'Failed to update property' }, { status: 500 });
  }
}

export async function DELETE(
  req: NextRequest,
  { params }: { params: { id: string } }
) {
  const session = await requireAdmin();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });

  try {
    await prisma.property.delete({ where: { id: params.id } });
    return NextResponse.json({ message: 'Property deleted' });
  } catch (error) {
    console.error('Admin property delete error:', error);
    return NextResponse.json({ error: 'Failed to delete property' }, { status: 500 });
  }
}
