import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { z } from 'zod';

const newsletterSchema = z.object({
  email: z.string().email('Invalid email address'),
});

export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const parsed = newsletterSchema.safeParse(body);

    if (!parsed.success) {
      return NextResponse.json(
        { error: parsed.error.errors[0]?.message || 'Invalid email address' },
        { status: 400 }
      );
    }

    const { email } = parsed.data;

    const existing = await prisma.newsletterSubscriber.findUnique({
      where: { email },
    });

    if (existing && existing.subscriptionStatus === 'ACTIVE') {
      return NextResponse.json(
        { message: 'You are already subscribed.' },
        { status: 200 }
      );
    }

    await prisma.newsletterSubscriber.upsert({
      where: { email },
      update: { subscriptionStatus: 'ACTIVE' },
      create: { email, subscriptionStatus: 'ACTIVE' },
    });

    return NextResponse.json(
      { message: 'Subscribed successfully. Welcome to HORIZON PROPERTIES.' },
      { status: 201 }
    );
  } catch (error) {
    console.error('Newsletter error:', error);
    return NextResponse.json(
      { error: 'Subscription failed. Please try again.' },
      { status: 500 }
    );
  }
}
