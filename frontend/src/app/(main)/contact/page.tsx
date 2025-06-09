import CompanyInfo from "@/components/(main)/contact/CompanyInfo";
import CompanyTable from "@/components/(main)/contact/CompanyTable";
import ContactForm from "@/components/(main)/contact/ContactForm";
import { Metadata } from "next";

export const metadata: Metadata = {
  title: "A-Ponton Kft. - Kapcsolat",
};

export default function ContactPage() {
  return (
    <div className="pt-20 bg-gray-200 dark:bg-zinc-400/60 min-h-screen">
      <div className="full-width-container mx-auto px-4 md:px-8 lg:px-16 pb-12">
        <h1 className="text-center lg:text-left text-2xl sm:text-3xl md:text-4xl lg:text-5xl 2xl:text-6xl font-bold my-6">
          Elérhetőségünk
        </h1>

        {/* Cég cím szekció */}
        <CompanyInfo />

        {/* Táblázat szekció */}
        <div className="row mx-5 p-3 mb-8">
          <div className="col-12">
            <CompanyTable />
          </div>
        </div>

        {/* Üzenetküldés szekció */}
        <h1
          id="form"
          className="text-center lg:text-left text-2xl sm:text-3xl md:text-4xl lg:text-5xl 2xl:text-6xl font-bold my-6"
        >
          Üzenetküldés
        </h1>
        <p className="mb-5 text-center lg:text-justify">
          <b>Bármilyen kérdés esetén</b> az alábbi űrlap kitöltésével{" "}
          <b>forduljanak hozzánk bizalommal</b>.
        </p>

        {/* Űrlap szekció */}
        <div className="mx-5 align-items-center rounded-lg mb-6">
          <ContactForm />
        </div>
      </div>
    </div>
  );
}
