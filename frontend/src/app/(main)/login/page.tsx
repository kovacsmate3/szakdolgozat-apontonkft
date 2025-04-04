import LoginForm from "@/components/(auth)/login/LoginForm";
import AuthSimpleLayout from "@/layouts/auth/auth-layout";

import { Metadata } from "next";

export const metadata: Metadata = {
  title: "A-Ponton Kft. - Bejelentkezés",
};

export default function LoginPage() {
  return (
    <AuthSimpleLayout
      title="Lépj be a felhasználói fiókodba"
      description="Add meg az email címedet vagy felhasználónevedet, illetve a jelszavadat a bejelentkezéshez"
    >
      <LoginForm />
    </AuthSimpleLayout>
  );
}
