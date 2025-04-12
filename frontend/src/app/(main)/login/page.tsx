import LoginForm from "@/components/(main)/login/LoginForm";

import { Metadata } from "next";

export const metadata: Metadata = {
  title: "A-Ponton Kft. - Bejelentkezés",
};

export default function LoginPage() {
  return (
    <div className="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-200 dark:bg-zinc-400/60">
      <div className="w-full max-w-md space-y-8">
        <div className="mt-8">
          <LoginForm />
        </div>
      </div>
    </div>
  );
}
