import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { officeData } from "@/lib/data/contact-page-data";

const CompanyTable = () => {
  return (
    <div className="w-full">
      <div className="text-center font-bold text-black dark:text-white pb-2">
        Az A-Ponton Mérnökiroda Kft. adatai
        <details className="text-sm font-normal text-gray-600 dark:text-white/85 mt-1">
          <summary className="cursor-pointer">
            A táblázat tartalmának összefoglalása
          </summary>
          <p className="py-2 px-4 text-center">
            Ezen 4×3-as táblázat tartalmazza az A-Ponton Mérnökiroda Kft.
            legfontosabb elérhetőségi útvonalait: a cég irodáinak címét, az
            irodavezetők nevét, telefonszámát és e-mail címét.
          </p>
        </details>
      </div>

      <div className="overflow-x-auto border shadow-md">
        <Table className="min-w-[600px] border-2 border-black bg-gray-200 dark:bg-zinc-400/60">
          <TableHeader>
            <TableRow className="border-b-3 border-black bg-gray-600/75 dark:[background:oklch(0.205_0_0)] hover:bg-gray-600/75 hover:dark:[background:oklch(0.205_0_0)] text-center">
              <TableHead className="p-4 text-black dark:text-white font-bold">
                Iroda
              </TableHead>
              <TableHead className="p-4 text-black dark:text-white font-bold">
                Irodavezető
              </TableHead>
              <TableHead className="p-4 text-black dark:text-white font-bold">
                E-mail cím
              </TableHead>
              <TableHead className="p-4 text-black dark:text-white font-bold">
                Telefonszám
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {officeData.map((office, index) => (
              <TableRow
                key={index}
                className="text-center bg-white dark:bg-black/70 border-b border-gray-300 dark:border-black/40 hover:bg-gray-100 dark:hover:bg-black/60"
              >
                <TableCell className="p-2 text-gray-600 dark:text-[color:oklch(0.708_0_0)]">
                  {office.office}
                </TableCell>
                <TableCell className="p-4 text-gray-600 dark:text-[color:oklch(0.708_0_0)]">
                  {office.manager}
                </TableCell>
                <TableCell className="p-4 text-gray-600 dark:text-[color:oklch(0.708_0_0)]">
                  <a
                    href={`mailto:${office.email}`}
                    className="hover:underline hover:text-black dark:hover:text-white dark:hover:underline cursor-pointer"
                  >
                    {office.email}
                  </a>
                </TableCell>
                <TableCell className="p-4 text-gray-600 dark:text-[color:oklch(0.708_0_0)]">
                  <a
                    href={`tel:${office.phone}`}
                    className="hover:underline hover:text-blackdark:hover:text-white dark:hover:underline cursor-pointer"
                  >
                    {office.phone}
                  </a>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>
    </div>
  );
};

export default CompanyTable;
