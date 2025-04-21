"use client";

import { useQuery } from "@tanstack/react-query";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { getUser } from "@/server/users";
import { PersonalInfoForm } from "@/components/(auth)/profile/PersonalInfoForm";
import { ContactInfoForm } from "@/components/(auth)/profile/ContactInfoForm";
import { Skeleton } from "@/components/ui/skeleton";
import { PasswordChangeForm } from "@/components/(auth)/profile/PasswordChangeForm";
import { AdminPersonalInfoForm } from "@/components/(auth)/profile/AdminPersonalInfoForm";
import { AdminContactInfoForm } from "@/components/(auth)/profile/AdminContactInfoForm";

interface Props {
  token: string;
  userId: string;
  isAdmin: boolean;
}

export default function ProfilePageClient({ token, userId, isAdmin }: Props) {
  const { data: user, isLoading } = useQuery({
    queryKey: ["user", userId, token],
    queryFn: () => getUser({ userId, token }),
  });

  if (isLoading) {
    return (
      <div className="container mx-auto py-10">
        <h1 className="text-2xl font-semibold mb-6">Profilom</h1>
        <Card>
          <CardHeader>
            <Skeleton className="h-8 w-64 mb-2" />
            <Skeleton className="h-4 w-full max-w-md" />
          </CardHeader>
          <CardContent>
            <Skeleton className="h-64 w-full" />
          </CardContent>
        </Card>
      </div>
    );
  }

  if (!user) {
    return (
      <div className="container mx-auto py-10">
        <h1 className="text-2xl font-semibold mb-6">Profilom</h1>
        <Card>
          <CardHeader>
            <CardTitle>Hiba történt</CardTitle>
            <CardDescription>
              Nem sikerült betölteni a felhasználói adatokat.
            </CardDescription>
          </CardHeader>
        </Card>
      </div>
    );
  }

  return (
    <div className="container mx-auto py-10">
      <h1 className="text-2xl font-semibold mb-6">Profilom</h1>

      {!isAdmin && (
        <div className="bg-blue-50 dark:bg-blue-950/30 p-4 rounded-md border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200 mb-6">
          <p>
            A rendszerben csak a jelszavadat tudod módosítani. Az egyéb
            személyes és kapcsolati adatok módosításához kérjük, fordulj az
            adminisztrátorhoz.
          </p>
        </div>
      )}

      <Tabs defaultValue="password" className="w-full">
        <TabsList className="grid w-full grid-cols-3">
          <TabsTrigger value="personal">Személyes adatok</TabsTrigger>
          <TabsTrigger value="contact">Kapcsolati adatok</TabsTrigger>
          <TabsTrigger value="password">Jelszó módosítás</TabsTrigger>
        </TabsList>

        <TabsContent value="personal">
          <Card>
            <CardHeader>
              <CardTitle>Személyes adatok</CardTitle>
              <CardDescription>
                {isAdmin
                  ? "A személyes adataid szerkesztése"
                  : "A személyes adataid megtekintése. Ezek az adatok nem módosíthatók."}
              </CardDescription>
            </CardHeader>
            <CardContent>
              {isAdmin ? (
                <AdminPersonalInfoForm user={user} token={token} />
              ) : (
                <PersonalInfoForm user={user} token={token} />
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="contact">
          <Card>
            <CardHeader>
              <CardTitle>Kapcsolati adatok</CardTitle>
              <CardDescription>
                A kapcsolati adataid megtekintése. Ezek az adatok nem
                módosíthatók.
              </CardDescription>
            </CardHeader>
            <CardContent>
              {isAdmin ? (
                <AdminContactInfoForm user={user} token={token} />
              ) : (
                <ContactInfoForm user={user} token={token} />
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="password">
          <Card>
            <CardHeader>
              <CardTitle>Jelszó módosítása</CardTitle>
              <CardDescription>
                A jelszavadat bármikor módosíthatod. Biztonsági okokból meg kell
                adnod a jelenlegi jelszavadat is.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <PasswordChangeForm userId={user.id} token={token} />
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}
