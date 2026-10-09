import { NextRequest, NextResponse } from 'next/server';
import { getServerSession } from 'next-auth';
import { authOptions } from '@/lib/auth';
import { prisma } from '@/lib/prisma';

export async function DELETE(
  req: NextRequest,
  { params }: { params: { propertyId: string } }
) {
  try {
    const session = await getServerSession(authOptions);
    if (!session?.user) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const userId = (session.user as any).id;
    const { propertyId } = params;

    await prisma.favorite.deleteMany({
      where: { userId, propertyId },
    });

    return NextResponse.json({ message: 'Removed from favorites' });
  } catch (error) {
    console.error('Favorites DELETE error:', error);
    return NextResponse.json({ error: 'Failed to remove favorite' }, { status: 500 });
  }
}

export async function GET(
  req: NextRequest,
  { params }: { params: { propertyId: string } }
) {
  try {
    const session = await getServerSession(authOptions);
    if (!session?.user) {
      return NextResponse.json({ isFavorite: false });
    }

    const userId = (session.user as any).id;
    const { propertyId } = params;

    const favorite = await prisma.favorite.findUnique({
      where: {
        userId_propertyId: { userId, propertyId },
      },
    });

    return NextResponse.json({ isFavorite: !!favorite });
  } catch (error) {
    console.error('Favorites check error:', error);
    return NextResponse.json({ isFavorite: false });
  }
}
