import { PrismaClient } from '@prisma/client';
import bcrypt from 'bcryptjs';

const prisma = new PrismaClient();

const img = (id: string, w = 1600) =>
  `https://images.unsplash.com/${id}?auto=format&fit=crop&w=${w}&q=80`;

const gallery = (ids: string[]) => ids.map((id) => img(id));

async function main() {
  console.log('Seeding database...');

  // --- Admin user ---
  const adminPassword = await bcrypt.hash('Housen2024!', 10);
  await prisma.user.upsert({
    where: { email: 'admin@housen.com' },
    update: {},
    create: {
      name: 'HOUSEN Admin',
      email: 'admin@housen.com',
      password: adminPassword,
      role: 'ADMIN',
    },
  });

  // --- Agents ---
  const agents = await Promise.all(
    [
      {
        name: 'Elena Marchetti',
        slug: 'elena-marchetti',
        biography:
          'With over fifteen years in luxury residential sales, Elena specialises in architecturally significant homes across the Mediterranean coast. Her background in fine art brings a curatorial eye to every acquisition.',
        profileImage: img('photo-1494790108755-2616b612b5ed', 400),
        email: 'elena@housen.com',
        phone: '+1 (310) 555-0142',
        specialties: ['Contemporary Villas', 'Waterfront Homes', 'Mediterranean'],
      },
      {
        name: 'Marcus Chen',
        slug: 'marcus-chen',
        biography:
          'Marcus brings a decade of experience in urban luxury properties, from penthouse residences to converted industrial lofts. His architectural training informs a detail-driven approach to every transaction.',
        profileImage: img('photo-1500648767791-00dcc994a43e', 400),
        email: 'marcus@housen.com',
        phone: '+1 (212) 555-0188',
        specialties: ['Penthouses', 'Luxury Apartments', 'Urban Lofts'],
      },
      {
        name: 'Sofia Almeida',
        slug: 'sofia-almeida',
        biography:
          'Sofia focuses on waterfront and beachfront estates, combining coastal expertise with a passion for sustainable architecture. She has closed over 200 transactions along the Pacific coastline.',
        profileImage: img('photo-1438761681033-6461ffad8d80', 400),
        email: 'sofia@housen.com',
        phone: '+1 (415) 555-0173',
        specialties: ['Waterfront Homes', 'Beachside Villas', 'Eco-Luxury'],
      },
      {
        name: 'James Whitfield',
        slug: 'james-whitfield',
        biography:
          'A former property developer, James brings deep construction knowledge to his clients. He specialises in new-build contemporary villas and renovation projects with architectural merit.',
        profileImage: img('photo-1507003211169-0a1dd7228f2d', 400),
        email: 'james@housen.com',
        phone: '+1 (305) 555-0156',
        specialties: ['Contemporary Villas', 'New Builds', 'Investment'],
      },
    ].map((a) =>
      prisma.agent.upsert({
        where: { slug: a.slug },
        update: {},
        create: a,
      })
    )
  );

  // --- Properties (demonstration listings) ---
  const properties = [
    {
      title: 'Beachside Villa',
      slug: 'beachside-villa',
      description:
        'A sculptural beachside residence defined by organic concrete forms, floor-to-ceiling glass, and a seamless indoor-outdoor flow. The home features a heated infinity pool, private beach access, and interiors finished in warm oak and natural stone. Every space is oriented toward the ocean horizon, creating a serene, gallery-like atmosphere.',
      price: 4200000,
      transactionType: 'BUY',
      propertyType: 'Villa',
      location: 'Malibu, California',
      city: 'Los Angeles',
      bedrooms: 5,
      bathrooms: 4,
      area: 6200,
      images: gallery([
        'photo-1600585154340-be6161a8a8a4',
        'photo-1600596542815-ff1a41622a3a',
        'photo-1600210492486-724fe5c67fb0',
        'photo-1600607687938-ce4d6c4b8be1',
      ]),
      featured: true,
      amenities: ['Infinity Pool', 'Private Beach', 'Smart Home System', 'Wine Cellar', 'Home Cinema', 'Gym', 'Solar Panels', '3-Car Garage'],
      agentId: agents[2].id,
    },
    {
      title: 'Urban Loft',
      slug: 'urban-loft',
      description:
        'An industrial-chic loft in a converted warehouse, featuring exposed brick walls, polished concrete floors, and double-height ceilings. The open-plan layout includes a chef\'s kitchen, mezzanine office space, and a private rooftop terrace with panoramic city views.',
      price: 1850000,
      transactionType: 'BUY',
      propertyType: 'Loft',
      location: 'Tribeca, New York',
      city: 'New York',
      bedrooms: 2,
      bathrooms: 2,
      area: 2100,
      images: gallery([
        'photo-1600569660653-95e60979c014',
        'photo-1600210492486-724fe5c67fb0',
        'photo-1600607687938-ce4d6c4b8be1',
        'photo-1600585154340-be6161a8a8a4',
      ]),
      featured: true,
      amenities: ['Rooftop Terrace', 'Exposed Brick', 'Chef\'s Kitchen', 'Smart Home System', 'Bike Storage', 'Concierge'],
      agentId: agents[1].id,
    },
    {
      title: 'Penthouse View',
      slug: 'penthouse-view',
      description:
        'A sky-high penthouse with 360-degree city and ocean views. Floor-to-ceiling windows wrap the entire residence, bathing the interiors in natural light. The home features a private elevator, wrap-around terrace, and a master suite with a freestanding soaking tub overlooking the skyline.',
      price: 6750000,
      transactionType: 'BUY',
      propertyType: 'Penthouse',
      location: 'Miami Beach, Florida',
      city: 'Miami',
      bedrooms: 3,
      bathrooms: 3,
      area: 3400,
      images: gallery([
        'photo-1600569660653-95e60979c014',
        'photo-1600596542815-ff1a41622a3a',
        'photo-1600210492486-724fe5c67fb0',
        'photo-1512917774080-9991f1c4c750',
      ]),
      featured: true,
      amenities: ['Private Elevator', 'Wrap-around Terrace', 'Smart Home System', 'Wine Cellar', 'Gym Access', 'Concierge', 'Valet Parking'],
      agentId: agents[1].id,
    },
    {
      title: 'Cliffside Modern Villa',
      slug: 'cliffside-modern-villa',
      description:
        'Perched on a dramatic cliff edge, this cantilevered villa appears to float above the valley below. The architecture blends raw concrete, warm timber, and frameless glass to dissolve the boundary between interior and landscape. An infinity-edge pool extends toward the horizon.',
      price: 8900000,
      transactionType: 'BUY',
      propertyType: 'Villa',
      location: 'Big Sur, California',
      city: 'San Francisco',
      bedrooms: 6,
      bathrooms: 5,
      area: 8400,
      images: gallery([
        'photo-1600607687938-ce4d6c4b8be1',
        'photo-1600585154340-be6161a8a8a4',
        'photo-1600596542815-ff1a41622a3a',
        'photo-1600210492486-724fe5c67fb0',
      ]),
      featured: false,
      amenities: ['Infinity Pool', 'Cliff Views', 'Smart Home System', 'Wine Cellar', 'Home Cinema', 'Gym', 'Solar Panels', '4-Car Garage', 'Guest House'],
      agentId: agents[0].id,
    },
    {
      title: 'Garden Pavilion',
      slug: 'garden-pavilion',
      description:
        'A serene garden pavilion surrounded by mature trees and reflecting pools. The home features a central courtyard, retractable glass walls, and interiors finished in warm oak and natural stone. A meditation room and private spa complete this tranquil retreat.',
      price: 3200000,
      transactionType: 'BUY',
      propertyType: 'Villa',
      location: 'Beverly Hills, California',
      city: 'Los Angeles',
      bedrooms: 4,
      bathrooms: 4,
      area: 4800,
      images: gallery([
        'photo-1600596542815-ff1a41622a3a',
        'photo-1600585154340-be6161a8a8a4',
        'photo-1600210492486-724fe5c67fb0',
        'photo-1600607687938-ce4d6c4b8be1',
      ]),
      featured: false,
      amenities: ['Central Courtyard', 'Private Spa', 'Meditation Room', 'Smart Home System', 'Garden', '2-Car Garage'],
      agentId: agents[0].id,
    },
    {
      title: 'Skyline Apartment',
      slug: 'skyline-apartment',
      description:
        'A sleek high-floor apartment with unobstructed skyline views. The residence features wide-plank oak flooring, a custom Italian kitchen, and a primary bedroom with a private balcony. Building amenities include a pool, fitness center, and 24-hour concierge.',
      price: 2400000,
      transactionType: 'BUY',
      propertyType: 'Apartment',
      location: 'Manhattan, New York',
      city: 'New York',
      bedrooms: 3,
      bathrooms: 2,
      area: 1850,
      images: gallery([
        'photo-1600569660653-95e60979c014',
        'photo-1600210492486-724fe5c67fb0',
        'photo-1512917774080-9991f1c4c750',
        'photo-1600607687938-ce4d6c4b8be1',
      ]),
      featured: false,
      amenities: ['City Views', 'Italian Kitchen', 'Building Pool', 'Fitness Center', '24h Concierge', 'Valet Parking'],
      agentId: agents[1].id,
    },
    {
      title: 'Lakeside Retreat',
      slug: 'lakeside-retreat',
      description:
        'A modern lakeside home with a boathouse and private dock. The architecture features a butterfly roof, warm cedar cladding, and expansive glass walls framing the water. Interiors include a double-sided fireplace and a screened porch for summer evenings.',
      price: 5500000,
      transactionType: 'BUY',
      propertyType: 'Villa',
      location: 'Lake Tahoe, Nevada',
      city: 'Reno',
      bedrooms: 5,
      bathrooms: 4,
      area: 5200,
      images: gallery([
        'photo-1600585154340-be6161a8a8a4',
        'photo-1600607687938-ce4d6c4b8be1',
        'photo-1600596542815-ff1a41622a3a',
        'photo-1600210492486-724fe5c67fb0',
      ]),
      featured: false,
      amenities: ['Private Dock', 'Boathouse', 'Double-sided Fireplace', 'Screened Porch', 'Smart Home System', '3-Car Garage'],
      agentId: agents[2].id,
    },
    {
      title: 'Downtown Penthouse Lease',
      slug: 'downtown-penthouse-lease',
      description:
        'A fully furnished penthouse available for long-term lease. Features include a private rooftop deck, chef\'s kitchen with integrated appliances, and a primary suite with a spa-like en-suite. Located in the heart of the financial district with walkable dining and transit.',
      price: 12000,
      transactionType: 'RENT',
      propertyType: 'Penthouse',
      location: 'Financial District, Chicago',
      city: 'Chicago',
      bedrooms: 3,
      bathrooms: 3,
      area: 2800,
      images: gallery([
        'photo-1600569660653-95e60979c014',
        'photo-1600210492486-724fe5c67fb0',
        'photo-1600596542815-ff1a41622a3a',
        'photo-1512917774080-9991f1c4c750',
      ]),
      featured: false,
      amenities: ['Private Rooftop Deck', 'Chef\'s Kitchen', 'Furnished', 'Building Pool', 'Fitness Center', '24h Concierge'],
      agentId: agents[1].id,
    },
    {
      title: 'Coastal Glass House',
      slug: 'coastal-glass-house',
      description:
        'A minimalist glass house perched on the coastline, designed to blur the line between architecture and nature. The home features a green roof, geothermal heating, and an outdoor shower. Every room captures ocean views through floor-to-ceiling glass.',
      price: 3800000,
      transactionType: 'BUY',
      propertyType: 'Villa',
      location: 'Carmel, California',
      city: 'San Francisco',
      bedrooms: 3,
      bathrooms: 3,
      area: 3100,
      images: gallery([
        'photo-1600607687938-ce4d6c4b8be1',
        'photo-1600585154340-be6161a8a8a4',
        'photo-1600210492486-724fe5c67fb0',
        'photo-1600596542815-ff1a41622a3a',
      ]),
      featured: false,
      amenities: ['Green Roof', 'Geothermal Heating', 'Outdoor Shower', 'Ocean Views', 'Smart Home System', '2-Car Garage'],
      agentId: agents[2].id,
    },
    {
      title: 'Heritage Loft Lease',
      slug: 'heritage-loft-lease',
      description:
        'A character-filled loft in a heritage textile building, available for lease. Features include 14-foot ceilings, original timber beams, oversized factory windows, and a renovated kitchen. The building offers a shared rooftop and bike room.',
      price: 4500,
      transactionType: 'RENT',
      propertyType: 'Loft',
      location: 'DUMBO, Brooklyn',
      city: 'New York',
      bedrooms: 1,
      bathrooms: 1,
      area: 1100,
      images: gallery([
        'photo-1600569660653-95e60979c014',
        'photo-1600210492486-724fe5c67fb0',
        'photo-1600607687938-ce4d6c4b8be1',
        'photo-1600585154340-be6161a8a8a4',
      ]),
      featured: false,
      amenities: ['14ft Ceilings', 'Timber Beams', 'Factory Windows', 'Renovated Kitchen', 'Shared Rooftop', 'Bike Room'],
      agentId: agents[1].id,
    },
  ];

  for (const p of properties) {
    await prisma.property.upsert({
      where: { slug: p.slug },
      update: {},
      create: p,
    });
  }

  // --- Newsletter subscribers (demo) ---
  const subscribers = [
    { email: 'demo.subscriber@example.com' },
    { email: 'jane.doe@example.com' },
  ];
  for (const s of subscribers) {
    await prisma.newsletterSubscriber.upsert({
      where: { email: s.email },
      update: {},
      create: s,
    });
  }

  console.log('Seed complete.');
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
