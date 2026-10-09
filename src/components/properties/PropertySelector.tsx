'use client';

import { useRouter } from 'next/navigation';

interface PropertyOption {
  slug: string;
  title: string;
  location: string;
}

export default function PropertySelector({ properties }: { properties: PropertyOption[] }) {
  const router = useRouter();

  return (
    <select
      className="input-field cursor-pointer"
      defaultValue=""
      onChange={(e) => {
        if (e.target.value) router.push(`/book-viewing?property=${e.target.value}`);
      }}
    >
      <option value="">Choose a property to view...</option>
      {properties.map((p) => (
        <option key={p.slug} value={p.slug}>
          {p.title} — {p.location}
        </option>
      ))}
    </select>
  );
}
