import { NextRequest, NextResponse } from 'next/server';
import { getServerSession } from 'next-auth';
import { authOptions } from '@/lib/auth';
import { prisma } from '@/lib/prisma';
import { z } from 'zod';

export async function GET() {
  try {
    const session = await getServerSession(authOptions);
    if (!session?.user) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const userId = (session.user as any).id;
    const favorites = await prisma.favorite.findMany({
      where: { userId },
      include: {
        property: {
          select: {
            id: true,
            title: true,
            slug: true,
            price: true,
            transactionType: true,
            propertyType: true,
            location: true,
            city: true,
            bedrooms: true,
            bathrooms: true,
            area: true,
            areaUnit: true,
            images: true,
            status: true,
          },
        },
      },
      orderBy: { createdAt: 'desc' },
    });

    return NextResponse.json({ favorites });
  } catch (error) {
    console.error('Favorites GET error:', error);
    return NextResponse.json({ error: 'Failed to fetch favorites' }, { status: 500 });
  }
}

const favoriteSchema = z.object({
  propertyId: z.string().min(1),
});

export async function POST(req: NextRequest) {
  try {
    const session = await getServerSession(authOptions);
    if (!session?.user) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const body = await req.json();
    const parsed = favoriteSchema.safeParse(body);
    if (!parsed.success) {
      return NextResponse.json({ error: 'Invalid input' }, { status: 400 });
    }

    const userId = (session.user as any).id;
    const { propertyId } = parsed.data;

    const existing = await prisma.favorite.findUnique({
      where: {
        userId_propertyId: { userId, propertyId },
      },
    });

    if (existing) {
      return NextResponse.json({ message: 'Already in favorites' }, { status: 200 });
    }

    await prisma.favorite.create({
      data: { userId, propertyId },
    });

    return NextResponse.json({ message: 'Added to favorites' }, { status: 201 });
  } catch (error) {
    console.error('Favorites POST error:', error);
    return NextResponse.json({ error: 'Failed to add favorite' }, { status: 500 });
  }
}
