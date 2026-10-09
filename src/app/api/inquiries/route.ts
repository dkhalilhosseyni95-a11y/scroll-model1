import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { z } from 'zod';

const inquirySchema = z.object({
  propertyId: z.string().min(1, 'Property is required'),
  customerName: z.string().min(2, 'Name is required'),
  email: z.string().email('Invalid email address'),
  phone: z.string().optional(),
  message: z.string().min(5, 'Message must be at least 5 characters'),
});

export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const parsed = inquirySchema.safeParse(body);

    if (!parsed.success) {
      return NextResponse.json(
        { error: parsed.error.errors[0]?.message || 'Invalid input' },
        { status: 400 }
      );
    }

    const { propertyId, customerName, email, phone, message } = parsed.data;

    const property = await prisma.property.findUnique({
      where: { id: propertyId },
      select: { id: true },
    });

    if (!property) {
      return NextResponse.json({ error: 'Property not found' }, { status: 404 });
    }

    await prisma.inquiry.create({
      data: { propertyId, customerName, email, phone, message },
    });

    return NextResponse.json(
      { message: 'Inquiry submitted successfully. We will be in touch shortly.' },
      { status: 201 }
    );
  } catch (error) {
    console.error('Inquiry error:', error);
    return NextResponse.json(
      { error: 'Failed to submit inquiry. Please try again.' },
      { status: 500 }
    );
  }
}
