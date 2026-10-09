export interface PropertyCardData {
  id: string;
  title: string;
  slug: string;
  price: number;
  transactionType: string;
  propertyType: string;
  location: string;
  city: string;
  bedrooms: number;
  bathrooms: number;
  area: number;
  areaUnit: string;
  images: string[];
  status: string;
  featured?: boolean;
}

export interface AgentCardData {
  id: string;
  name: string;
  slug: string;
  biography: string;
  profileImage: string;
  email: string;
  phone: string;
  specialties: string[];
  activeStatus: boolean;
}
