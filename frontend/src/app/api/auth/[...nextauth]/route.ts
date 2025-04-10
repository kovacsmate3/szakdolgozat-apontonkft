import { CustomUser } from "@/lib/next-auth";
import NextAuth, { NextAuthOptions } from "next-auth";
import CredentialsProvider from "next-auth/providers/credentials";

export const authOptions: NextAuthOptions = {
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
        const res = await fetch("http://localhost:8000/sanctum/csrf-cookie", {
          method: "GET",
        });

        const setCookieHeader = res.headers.get("set-cookie");
        console.log("setCookieHeader", setCookieHeader);

        const cookies = setCookieHeader?.split(", ");
        console.log(cookies);
        let sessionKey: string | null = null;
        let xsrfToken: string | null = null;

        for (const cookie of cookies!) {
          if (cookie.startsWith("laravel_session=")) {
            sessionKey = cookie.split("=")[1];
          } else if (cookie.startsWith("XSRF-TOKEN=")) {
            xsrfToken = cookie.split("=")[1];
          }

          if (sessionKey && xsrfToken) {
            break;
          }
        }
        const data = {
          identifier: credentials?.identifier,
          password: credentials?.password,
        };
        const headers = new Headers({
          Cookie: `laravel_session=${sessionKey}; XSRF-TOKEN=${xsrfToken}`,
          "Content-Type": "application/json",
        });

        if (xsrfToken) {
          headers.append("X-XSRF-TOKEN", xsrfToken);
        }

        const options = {
          method: "POST",
          headers,
          body: JSON.stringify(data),
        };
        try {
          console.log(options);
          const response = await fetch(
            `${process.env.NEXT_PUBLIC_API_URL}/login`,
            options
          );

          if (response.ok) {
            const res = await response.json();
            console.log("response", res);
            return res;
          } else {
            console.log("HTTP error! Status:", response.status);
            return { error: "Authentication failed" };
          }
        } catch (error) {
          console.log("Error", error);
        }

        return null;
      },
    }),
  ],
  callbacks: {
    async jwt({ token, user }) {
      if (user) {
        token.accessToken = user.access_token;
        const backendUser = user.user;
        token.user = { ...backendUser, id: String(backendUser.id) };
      }
      return token;
    },
    async session({ session, token }) {
      session.accessToken = token.access_token as string;
      session.user = token.user as CustomUser;
      return session;
    },
  },
};
const handler = NextAuth(authOptions);
export { handler as GET, handler as POST };
