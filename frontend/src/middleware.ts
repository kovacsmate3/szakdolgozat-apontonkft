import { auth } from "./auth";
import { NextRequest, NextResponse } from "next/server";
import {
  adminRoutes,
  mainRoutes,
  protectedRoutes,
  validRoutes,
} from "./lib/data/pathnames-data";

export async function middleware(req: NextRequest) {
  const session = await auth();
  const { pathname } = req.nextUrl;

  if (pathname === "/") {
    return NextResponse.redirect(new URL("/home", req.url));
  }

  const isProtected = protectedRoutes.some((route) =>
    pathname.startsWith(route)
  );
  const isAdminRoute = adminRoutes.some((route) => pathname.startsWith(route));

  if (isProtected && !session) {
    const url = new URL("/login", req.url);
    return NextResponse.redirect(url);
  }

  if (isAdminRoute && session?.user?.role !== "admin") {
    return NextResponse.redirect(new URL("/dashboard", req.url));
  }

  if (session) {
    // Ellenőrizzük, hogy a jelenlegi útvonal érvényes-e
    const isValidRoute = validRoutes.some(
      (route) =>
        pathname === route ||
        (route.includes("*") &&
          new RegExp(route.replace("*", ".*")).test(pathname))
    );

    if (!isValidRoute) {
      // Ha nem érvényes, ellenőrizzük, hogy melyik fő útvonal alá tartozik
      const parentRoute = Object.keys(mainRoutes).find((route) =>
        pathname.startsWith(route)
      );

      if (parentRoute) {
        // Ha van szülő útvonal, oda irányítjuk vissza
        return NextResponse.redirect(new URL(mainRoutes[parentRoute], req.url));
      } else {
        // Ha nem tartozik egyik fő útválhoz sem, a dashboard-ra irányítjuk
        return NextResponse.redirect(new URL("/dashboard", req.url));
      }
    }
  }

  return NextResponse.next();
}

export const config = {
  matcher: ["/((?!api|_next/static|_next/image|images|favicon.ico).*)"],
};
