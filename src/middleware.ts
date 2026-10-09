import { withAuth } from 'next-auth/middleware';

export default withAuth({
  pages: { signIn: '/admin/login' },
  callbacks: {
    authorized: ({ token, req }) => {
      const path = req.nextUrl.pathname;
      // Login page is always accessible
      if (path === '/admin/login') return true;
      // Admin routes require ADMIN role
      if (path.startsWith('/admin')) return token?.role === 'ADMIN';
      // Other protected routes require any authenticated user
      return !!token;
    },
  },
});

export const config = {
  matcher: ['/admin/:path*', '/account', '/favorites'],
};
