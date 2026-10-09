export function formatPrice(price: number, transactionType?: string): string {
  if (transactionType === 'RENT') {
    return `$${price.toLocaleString()}/mo`;
  }
  if (price >= 1_000_000) {
    return `$${(price / 1_000_000).toFixed(1)}M`;
  }
  return `$${price.toLocaleString()}`;
}

export function slugify(text: string): string {
  return text
    .toLowerCase()
    .trim()
    .replace(/[^\w\s-]/g, '')
    .replace(/[\s_-]+/g, '-')
    .replace(/^-+|-+$/g, '');
}
