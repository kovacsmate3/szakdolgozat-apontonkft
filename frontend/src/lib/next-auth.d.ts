import type {
  JWT as NextAuthJWT,
  Session as NextAuthSession,
  User as NextAuthUser,
} from "next-auth";

interface CustomUser extends NextAuthUser {
  id: number;
  name: string;
  email: string;
  username: string;
  role: string;
}

declare module "next-auth" {
  interface Session extends NextAuthSession {
    accessToken: string;
    user: NextAuthUser & {
      accessToken: string;
      user: CustomUser;
    };
  }

  interface User extends NextAuthUser, CustomUser {
    access_token: string;
  }

  interface JWT extends NextAuthJWT {
    access_token: string;
    user?: CustomUser;
  }
}
