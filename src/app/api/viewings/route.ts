import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { z } from 'zod';

const viewingSchema = z.object({
  propertyId: z.string().min(1, 'Property is required'),
  customerName: z.string().min(2, 'Name is required'),
  email: z.string().email('Invalid email address'),
  phone: z.string().min(5, 'Valid phone number is required'),
  preferredDate: z.string().min(1, 'Preferred date is required'),
  preferredTime: z.string().min(1, 'Preferred time is required'),
  message: z.string().optional(),
});

export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const parsed = viewingSchema.safeParse(body);

    if (!parsed.success) {
      return NextResponse.json(
        { error: parsed.error.errors[0]?.message || 'Invalid input' },
        { status: 400 }
      );
    }

    const { propertyId, customerName, email, phone, preferredDate, preferredTime, message } = parsed.data;

    const property = await prisma.property.findUnique({
      where: { id: propertyId },
      select: { id: true },
    });

    if (!property) {
      return NextResponse.json({ error: 'Property not found' }, { status: 404 });
    }

    await prisma.viewingRequest.create({
      data: { propertyId, customerName, email, phone, preferredDate, preferredTime, message },
    });

    return NextResponse.json(
      { message: 'Viewing request submitted. We will confirm your appointment shortly.' },
      { status: 201 }
    );
  } catch (error) {
    console.error('Viewing request error:', error);
    return NextResponse.json(
      { error: 'Failed to submit viewing request. Please try again.' },
      { status: 500 }
    );
  }
}
