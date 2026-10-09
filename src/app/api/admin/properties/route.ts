import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { requireAdmin } from '@/lib/adminAuth';
import { z } from 'zod';
import { slugify } from '@/lib/utils';

const propertySchema = z.object({
  title: z.string().min(2, 'Title is required'),
  slug: z.string().optional(),
  description: z.string().min(10, 'Description is required'),
  price: z.number().min(0, 'Price must be positive'),
  transactionType: z.enum(['BUY', 'RENT']),
  propertyType: z.string().min(1, 'Property type is required'),
  location: z.string().min(1, 'Location is required'),
  city: z.string().min(1, 'City is required'),
  bedrooms: z.number().int().min(0),
  bathrooms: z.number().int().min(0),
  area: z.number().int().min(0),
  images: z.array(z.string()).optional(),
  featured: z.boolean().optional(),
  status: z.enum(['AVAILABLE', 'SOLD', 'RENTED', 'OFF_MARKET']).optional(),
  amenities: z.array(z.string()).optional(),
  agentId: z.string().nullable().optional(),
});

export async function POST(req: NextRequest) {
  const session = await requireAdmin();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });

  try {
    const body = await req.json();
    const parsed = propertySchema.safeParse(body);
    if (!parsed.success) {
      return NextResponse.json({ error: parsed.error.errors[0]?.message || 'Invalid input' }, { status: 400 });
    }

    const data = parsed.data;
    const slug = data.slug || slugify(data.title);

    // Ensure unique slug
    const existing = await prisma.property.findUnique({ where: { slug } });
    if (existing) {
      return NextResponse.json({ error: 'A property with this title already exists' }, { status: 409 });
    }

    const property = await prisma.property.create({
      data: {
        title: data.title,
        slug,
        description: data.description,
        price: data.price,
        transactionType: data.transactionType,
        propertyType: data.propertyType,
        location: data.location,
        city: data.city,
        bedrooms: data.bedrooms,
        bathrooms: data.bathrooms,
        area: data.area,
        images: data.images || [],
        featured: data.featured || false,
        status: data.status || 'AVAILABLE',
        amenities: data.amenities || [],
        agentId: data.agentId || null,
      },
    });

    return NextResponse.json({ property }, { status: 201 });
  } catch (error) {
    console.error('Admin property create error:', error);
    return NextResponse.json({ error: 'Failed to create property' }, { status: 500 });
  }
}
