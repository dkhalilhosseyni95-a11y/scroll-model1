import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { requireAdmin } from '@/lib/adminAuth';
import { z } from 'zod';
import { slugify } from '@/lib/utils';

const agentSchema = z.object({
  name: z.string().min(2, 'Name is required'),
  slug: z.string().optional(),
  biography: z.string().min(10, 'Biography is required'),
  profileImage: z.string().url('Valid image URL is required'),
  email: z.string().email('Valid email is required'),
  phone: z.string().min(5, 'Valid phone is required'),
  specialties: z.array(z.string()).optional(),
  activeStatus: z.boolean().optional(),
});

export async function POST(req: NextRequest) {
  const session = await requireAdmin();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });

  try {
    const body = await req.json();
    const parsed = agentSchema.safeParse(body);
    if (!parsed.success) {
      return NextResponse.json({ error: parsed.error.errors[0]?.message || 'Invalid input' }, { status: 400 });
    }

    const data = parsed.data;
    const slug = data.slug || slugify(data.name);

    const existing = await prisma.agent.findUnique({ where: { slug } });
    if (existing) {
      return NextResponse.json({ error: 'An agent with this name already exists' }, { status: 409 });
    }

    const agent = await prisma.agent.create({
      data: {
        name: data.name,
        slug,
        biography: data.biography,
        profileImage: data.profileImage,
        email: data.email,
        phone: data.phone,
        specialties: data.specialties || [],
        activeStatus: data.activeStatus ?? true,
      },
    });

    return NextResponse.json({ agent }, { status: 201 });
  } catch (error) {
    console.error('Admin agent create error:', error);
    return NextResponse.json({ error: 'Failed to create agent' }, { status: 500 });
  }
}
