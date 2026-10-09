import { PrismaClient } from '@prisma/client';
import bcrypt from 'bcryptjs';

const prisma = new PrismaClient();

const img = (id: string, w = 1600) =>
  `https://images.unsplash.com/${id}?auto=format&fit=crop&w=${w}&q=80`;

const gallery = (ids: string[]) => ids.map((id) => img(id));

async function main() {
  console.log('Seeding database...');

  // --- Clean existing demo data (preserves admin users) ---
  await prisma.favorite.deleteMany();
  await prisma.inquiry.deleteMany();
  await prisma.viewingRequest.deleteMany();
  await prisma.property.deleteMany();
  await prisma.agent.deleteMany();

  // --- Admin user ---
  const adminPassword = await bcrypt.hash('Horizon2024!', 10);
  await prisma.user.upsert({
    where: { email: 'admin@horizonproperties.com' },
    update: {},
    create: {
      name: 'Horizon Admin',
      email: 'admin@horizonproperties.com',
      password: adminPassword,
      role: 'ADMIN',
    },
  });

  // --- Agents / Team ---
  const agents = await Promise.all(
    [
      {
        name: 'Daniel Morgan',
        slug: 'daniel-morgan',
        biography:
          'Managing Director with over twenty years in luxury real estate. Daniel founded Horizon Properties on a simple belief: that exceptional homes deserve exceptional representation. He oversees the firm\u2019s strategy and its most significant transactions.',
        profileImage: img('photo-1507003211169-0a1dd7228f2d', 400),
        email: 'daniel@horizonproperties.com',
        phone: '+1 (555) 246-7891',
        specialties: ['Managing Director', 'Luxury Home Sales', 'Investment'],
      },
      {
        name: 'Olivia Carter',
        slug: 'olivia-carter',
        biography:
          'Luxury Property Advisor specialising in architecturally significant residences. Olivia\u2019s background in interior design gives her a rare eye for the homes she represents and the clients she serves.',
        profileImage: img('photo-1573496359142-b8d87734a5a2', 400),
        email: 'olivia@horizonproperties.com',
        phone: '+1 (555) 246-7892',
        specialties: ['Luxury Property Advisor', 'Villas', 'Waterfront'],
      },
      {
        name: 'James Wilson',
        slug: 'james-wilson',
        biography:
          'Investment Consultant helping clients build and balance resilient real estate portfolios. James brings a decade of financial analysis and market research to every acquisition and disposition.',
        profileImage: img('photo-1500648767791-00dcc994a43e', 400),
        email: 'james@horizonproperties.com',
        phone: '+1 (555) 246-7893',
        specialties: ['Investment Consultant', 'Property Valuation', 'Advisory'],
      },
      {
        name: 'Sophia Bennett',
        slug: 'sophia-bennett',
        biography:
          'Senior Property Specialist with a passion for contemporary architecture and modern living. Sophia has guided hundreds of families through the purchase of their dream homes across prime markets.',
        profileImage: img('photo-1438761681033-6461ffad8d80', 400),
        email: 'sophia@horizonproperties.com',
        phone: '+1 (555) 246-7894',
        specialties: ['Senior Property Specialist', 'Relocation', 'Marketing'],
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
      title: 'Lakeside Modern Villa',
      slug: 'lakeside-modern-villa',
      description:
        'A sculptural lakeside residence defined by floor-to-ceiling glass, warm oak interiors, and a seamless indoor-outdoor flow. The home features a heated infinity pool, private dock, and interiors oriented entirely toward the water. Every space is calm, light-filled, and gallery-like in its simplicity.',
      price: 2350000,
      transactionType: 'BUY',
      propertyType: 'Villa',
      location: 'Austin, Texas, USA',
      city: 'Austin',
      bedrooms: 4,
      bathrooms: 3,
      area: 4200,
      images: gallery([
        'photo-1600596542815-ffad4c1539a9',
        'photo-1600585154340-be6161a56a0c',
        'photo-1512917774083-611c87c178ce',
        'photo-1517841905240-472988babdf9',
      ]),
      featured: true,
      amenities: ['Infinity Pool', 'Private Dock', 'Smart Home System', 'Wine Cellar', 'Home Cinema', '3-Car Garage'],
      agentId: agents[1].id,
    },
    {
      title: 'Pacific Glass House',
      slug: 'pacific-glass-house',
      description:
        'A minimalist glass house perched above the Pacific, designed to dissolve the boundary between architecture and ocean. Frameless glass walls wrap the residence, while a green roof and geothermal heating keep it quietly sustainable. Every room captures unobstructed sunset views.',
      price: 4800000,
      transactionType: 'BUY',
      propertyType: 'Villa',
      location: 'Malibu, California, USA',
      city: 'Los Angeles',
      bedrooms: 5,
      bathrooms: 4,
      area: 5600,
      images: gallery([
        'photo-1512917774083-611c87c178ce',
        'photo-1600596542815-ffad4c1539a9',
        'photo-1600585154340-be6161a56a0c',
        'photo-1613490493576-7fde63acd811',
      ]),
      featured: true,
      amenities: ['Ocean Views', 'Green Roof', 'Geothermal Heating', 'Smart Home System', 'Infinity Pool', '4-Car Garage'],
      agentId: agents[0].id,
    },
    {
      title: 'Desert Horizon Estate',
      slug: 'desert-horizon-estate',
      description:
        'A contemporary desert estate built from rammed earth, steel, and glass. Deep overhangs shade expansive terraces, while a central courtyard frames the desert sky. The home blends quietly into its surroundings while offering refined, light-filled living spaces.',
      price: 3150000,
      transactionType: 'BUY',
      propertyType: 'Villa',
      location: 'Scottsdale, Arizona, USA',
      city: 'Phoenix',
      bedrooms: 4,
      bathrooms: 4,
      area: 4600,
      images: gallery([
        'photo-1487956382158-bb926046304a',
        'photo-1517841905240-472988babdf9',
        'photo-1600596542815-ffad4c1539a9',
        'photo-1600585154340-be6161a56a0c',
      ]),
      featured: true,
      amenities: ['Rammed Earth Walls', 'Central Courtyard', 'Smart Home System', 'Pool', 'Solar Panels', '3-Car Garage'],
      agentId: agents[3].id,
    },
    {
      title: 'Oceanfront Residence',
      slug: 'oceanfront-residence',
      description:
        'A sweeping oceanfront residence with direct beach access and panoramic sea views. The architecture pairs clean white volumes with warm timber, while a resort-style pool deck steps down toward the sand. Interiors are bright, airy, and effortlessly elegant.',
      price: 5200000,
      transactionType: 'BUY',
      propertyType: 'Villa',
      location: 'Miami, Florida, USA',
      city: 'Miami',
      bedrooms: 6,
      bathrooms: 5,
      area: 7200,
      images: gallery([
        'photo-1517841905240-472988babdf9',
        'photo-1512917774083-611c87c178ce',
        'photo-1600596542815-ffad4c1539a9',
        'photo-1600585154340-be6161a56a0c',
      ]),
      featured: true,
      amenities: ['Beach Access', 'Resort Pool', 'Smart Home System', 'Wine Cellar', 'Gym', 'Guest House', '4-Car Garage'],
      agentId: agents[1].id,
    },
    {
      title: 'Modern Hillside Retreat',
      slug: 'modern-hillside-retreat',
      description:
        'A cantilevered hillside retreat that appears to float above the city below. Raw concrete, warm timber, and frameless glass dissolve the boundary between interior and landscape. An infinity-edge pool extends toward the horizon, capturing the skyline by night.',
      price: 3750000,
      transactionType: 'BUY',
      propertyType: 'Villa',
      location: 'Los Angeles, California, USA',
      city: 'Los Angeles',
      bedrooms: 4,
      bathrooms: 4,
      area: 5100,
      images: gallery([
        'photo-1600585154340-be6161a56a0c',
        'photo-1600596542815-ffad4c1539a9',
        'photo-1512917774083-611c87c178ce',
        'photo-1613490493576-7fde63acd811',
      ]),
      featured: true,
      amenities: ['Infinity Pool', 'City Views', 'Smart Home System', 'Home Cinema', 'Gym', '3-Car Garage'],
      agentId: agents[2].id,
    },
    {
      title: 'Palm Garden Residence',
      slug: 'palm-garden-residence',
      description:
        'A serene garden residence surrounded by mature palms and reflecting pools. The home features a central courtyard, retractable glass walls, and interiors finished in warm oak and natural stone. A private spa and screening room complete this tranquil retreat.',
      price: 6400000,
      transactionType: 'BUY',
      propertyType: 'Villa',
      location: 'Beverly Hills, California, USA',
      city: 'Los Angeles',
      bedrooms: 7,
      bathrooms: 6,
      area: 8800,
      images: gallery([
        'photo-1613490493576-7fde63acd811',
        'photo-1600585154340-be6161a56a0c',
        'photo-1600596542815-ffad4c1539a9',
        'photo-1512917774083-611c87c178ce',
      ]),
      featured: true,
      amenities: ['Central Courtyard', 'Private Spa', 'Screening Room', 'Smart Home System', 'Garden', '4-Car Garage'],
      agentId: agents[0].id,
    },
    {
      title: 'Contemporary Lake House',
      slug: 'contemporary-lake-house',
      description:
        'A modern lake house with a butterfly roof, warm cedar cladding, and expansive glass walls framing the water. The home features a private dock, boathouse, and a double-sided fireplace that warms both the living room and the screened porch.',
      price: 2950000,
      transactionType: 'BUY',
      propertyType: 'Villa',
      location: 'Lake Tahoe, Nevada, USA',
      city: 'Reno',
      bedrooms: 5,
      bathrooms: 4,
      area: 5200,
      images: gallery([
        'photo-1600596542815-ffad4c1539a9',
        'photo-1600585154340-be6161a56a0c',
        'photo-1512917774083-611c87c178ce',
        'photo-1487956382158-bb926046304a',
      ]),
      featured: true,
      amenities: ['Private Dock', 'Boathouse', 'Double-sided Fireplace', 'Screened Porch', 'Smart Home System', '3-Car Garage'],
      agentId: agents[3].id,
    },
    {
      title: 'Architectural Downtown Penthouse',
      slug: 'architectural-downtown-penthouse',
      description:
        'A sky-high penthouse with 360-degree city and river views. Floor-to-ceiling windows wrap the entire residence, bathing the interiors in natural light. The home features a private elevator, wrap-around terrace, and a primary suite with a freestanding soaking tub overlooking the skyline.',
      price: 1850000,
      transactionType: 'BUY',
      propertyType: 'Penthouse',
      location: 'Austin, Texas, USA',
      city: 'Austin',
      bedrooms: 3,
      bathrooms: 3,
      area: 2800,
      images: gallery([
        'photo-1545324418-cc1a3fa10c00',
        'photo-1613490493576-7fde63acd811',
        'photo-1517841905240-472988babdf9',
        'photo-1600596542815-ffad4c1539a9',
      ]),
      featured: true,
      amenities: ['Private Elevator', 'Wrap-around Terrace', 'Smart Home System', 'Wine Cellar', 'Concierge', 'Valet Parking'],
      agentId: agents[2].id,
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
