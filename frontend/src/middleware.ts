import { auth } from "./auth";
import { NextRequest, NextResponse } from "next/server";

export const protectedRoutes = [
  "/dashboard",
  //"/timesheet",
  "/road-record",
  "/basic-data",
  "/laws",
  "/admin",
];

export const adminRoutes = ["/admin"];

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

  return NextResponse.next();
}

export const config = {
  matcher: [
    "/",
    "/dashboard/:path*",
    //"/timesheet/:path*",
    "/road-record/:path*",
    "/basic-data/:path*",
    "/laws/:path*",
    "/admin/:path*",
  ],
};
