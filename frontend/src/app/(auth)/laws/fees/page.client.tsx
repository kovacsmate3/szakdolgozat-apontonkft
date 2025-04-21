"use client";

interface Props {
  token: string;
}

export default function FeesPageClient({ token }: Props) {
  return (
    <div className="container mx-auto py-10">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-semibold">Eljárási díjak</h1>
      </div>
    </div>
  );
}
