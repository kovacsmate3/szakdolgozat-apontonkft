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
        <details className="text-sm font-normal text-gray-600 dark:text-gray-400 mt-1">
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

      <div className="overflow-x-auto border">
        <Table className="min-w-[600px] border-2 border-black bg-white dark:bg-gray-800">
          <TableHeader>
            <TableRow className="border-b-3 border-black bg-gray-400 dark:bg-gray-700 text-center">
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
                className="text-center border-b border-gray-300 dark:border-gray-600"
              >
                <TableCell className="p-2 text-black dark:text-white">
                  {office.office}
                </TableCell>
                <TableCell className="p-4 text-black dark:text-white">
                  {office.manager}
                </TableCell>
                <TableCell className="p-4 text-black dark:text-white">
                  <a
                    href={`mailto:${office.email}`}
                    className="hover:underline text-blue-600 dark:text-blue-400"
                  >
                    {office.email}
                  </a>
                </TableCell>
                <TableCell className="p-4 text-black dark:text-white">
                  <a
                    href={`tel:${office.phone}`}
                    className="hover:underline text-blue-600 dark:text-blue-400"
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
