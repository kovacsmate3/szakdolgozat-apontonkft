import { Metadata } from "next";

export const metadata: Metadata = {
  title: "A-Ponton Kft. - Kezdőlap",
};

export default function HomePage() {
  return (
    <main className="container">
      <section className="py-24">
        <div className="container">
          <h1 className="text-3xl font-bold">File Conventions in NextJs</h1>
        </div>
      </section>
    </main>
  );
}
