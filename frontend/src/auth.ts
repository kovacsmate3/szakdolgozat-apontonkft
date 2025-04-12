import NextAuth from "next-auth";
import CredentialsProvider from "next-auth/providers/credentials";
import type { JWT } from "next-auth/jwt";

type CustomUser = {
  id: number;
  name: string;
  email: string;
  username: string;
  role: string | null;
  access_token: string;
};

export const { auth, handlers, signIn, signOut } = NextAuth({
  session: { strategy: "jwt" },
  providers: [
    CredentialsProvider({
      name: "Identifier and Password",
      credentials: {
        identifier: {
          label: "Identifier",
          type: "text",
          placeholder: "Your Email or Username",
        },
        password: { label: "Password", type: "password" },
      },
      async authorize(credentials) {
        if (!credentials?.identifier || !credentials?.password) return null;

        try {
          const response = await fetch(
            `${process.env.NEXT_PUBLIC_API_URL}/login`,
            {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
              },
              body: JSON.stringify({
                identifier: credentials.identifier,
                password: credentials.password,
              }),
            }
          );

          if (!response.ok) {
            const errorData = await response.json();
            console.error(
              "[NextAuth][authorize] Hibás válasz a bejelentkezéskor:",
              errorData
            );
            return null;
          }

          const data = await response.json();
          if (data?.status && data?.token && data?.user) {
            return {
              ...data.user,
              access_token: data.token,
            };
          } else {
            console.error(
              "[NextAuth][authorize] Hiányos válasz a szervertől:",
              data
            );
            return null;
          }
        } catch (err) {
          console.error(
            "[NextAuth][authorize] Hiba történt a hitelesítés során:",
            err
          );
          return null;
        }
      },
    }),
  ],
  callbacks: {
    async jwt({ token, user }) {
      if (user) {
        const customUser = user as unknown as CustomUser;

        token.access_token = customUser.access_token;
        token.loggedUser = {
          id: customUser.id,
          name: customUser.name,
          email: customUser.email,
          username: customUser.username,
          role: customUser.role,
        };
      }
      return token;
    },
    async session({ session, token }) {
      const typedToken = token as JWT;

      if (typedToken.access_token) {
        session.access_token = typedToken.access_token;
      }

      if (typedToken.loggedUser && typedToken.access_token) {
        session.user = {
          ...typedToken.loggedUser,
          id: String(typedToken.loggedUser.id),
          image: null,
          emailVerified: null,
          access_token: typedToken.access_token,
        };
      }

      return session;
    },
  },
});
