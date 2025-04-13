import { auth } from "@/auth";
import { columns } from "@/components/(auth)/admin/users/columns";
import { CreateUserDialog } from "@/components/(auth)/admin/users/CreateUserDialog";
import { UserTable } from "@/components/(auth)/admin/users/UserTable";
import { UserData } from "@/lib/types";

export const dynamic = "force-dynamic";

async function getUsers(token: string): Promise<UserData[]> {
  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/users`, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });

  console.log(res);

  if (!res.ok) {
    throw new Error("Nem sikerült lekérni a felhasználókat.");
  }

  const data = await res.json();
  return data;
}

export default async function UsersPage() {
  const session = await auth();

  if (!session?.user?.role || session.user.role !== "admin") {
    return <div>Hozzáférés megtagadva.</div>;
  }

  const token = session.user.access_token;

  const users = await getUsers(token);

  return (
    <div className="container mx-auto py-10">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-semibold">Felhasználók</h1>
        <CreateUserDialog />
      </div>
      <UserTable columns={columns} data={users} />
    </div>
  );
}
