// src/types/next-auth.d.ts

import { DefaultSession, DefaultUser } from "next-auth";
import { JWT as DefaultJWT } from "@auth/core/jwt"; // fontos a v5-höz!

declare module "next-auth" {
  interface Session extends DefaultSession {
    access_token?: string;
    user?: {
      id: string; // 👈 ID must be string here!
      name: string;
      email: string;
      username: string;
      role: string | null;
      image?: string | null;
      emailVerified?: Date | null;
      access_token: string;
    };
  }

  interface User extends DefaultUser {
    id: number;
    username: string;
    role: string | null;
    access_token: string;
  }
}

declare module "next-auth/jwt" {
  interface JWT extends DefaultJWT {
    access_token?: string;
    loggedUser?: {
      id: number;
      name: string;
      email: string;
      username: string;
      role: string | null;
    };
  }
}
