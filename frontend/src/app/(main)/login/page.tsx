import LoginForm from "@/components/(main)/login/LoginForm";
import { Metadata } from "next";
import { Info } from "lucide-react";

export const metadata: Metadata = {
  title: "A-Ponton Kft. - Bejelentkezés",
};

export default function LoginPage() {
  return (
    <div className="min-h-screen flex items-center justify-center pb-12 pt-32 px-4 sm:px-6 lg:px-8 bg-gray-200 dark:bg-zinc-400/60">
      <div className="w-full max-w-md space-y-8">
        <div className="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg mb-6">
          <div className="flex items-start gap-4">
            <div className="bg-blue-100 dark:bg-blue-900 p-2 rounded-full">
              <Info className="h-5 w-5 text-blue-700 dark:text-blue-300" />
            </div>
            <div>
              <h2 className="text-lg font-semibold mb-2 text-justify">
                Zárt rendszer
              </h2>
              <p className="text-gray-600 dark:text-gray-300 text-sm  text-justify">
                Ez a felület kizárólag az A-Ponton Mérnökiroda Kft. munkatársai
                számára érhető el. Amennyiben Ön nem tartozik a fent említett
                körbe, a bejelentkezés és a belső rendszer használata nem
                lehetséges. Érdeklődés esetén keresse az iroda munkatársait az
                alábbi elérhetőségeken:
              </p>
              <div className="mt-3 text-sm">
                <p className="font-semibold text-gray-700 dark:text-gray-200">
                  Kapcsolat:
                </p>
                <p>
                  Email:{" "}
                  <a
                    href="mailto:info@a-ponton.hu"
                    className="text-blue-600 dark:text-blue-400 hover:underline"
                  >
                    info@a-ponton.hu
                  </a>
                </p>
                <p>
                  Telefon:{" "}
                  <a
                    href="tel:+3612345678"
                    className="text-blue-600 dark:text-blue-400 hover:underline"
                  >
                    +36 1 234 5678
                  </a>
                </p>
              </div>
            </div>
          </div>
        </div>

        <div className="mt-8">
          <LoginForm />
        </div>
      </div>
    </div>
  );
}
