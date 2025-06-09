import { EquipmentProps } from "@/lib/types";
import EquipmentCard from "../EquipmentCard";

const PrinterEquipmentDesktop = ({ items }: EquipmentProps) => {
  return (
    <div className="hidden lg:flex justify-between items-stretch gap-4">
      <div className="w-1/3">
        <EquipmentCard {...items[0]} className="h-full" />
      </div>

      <div className="w-1/3 bg-black/90 rounded-lg p-5 flex items-center">
        <p className="text-white text-justify">
          A <b>hatékony irodai munkánk elengedhetetlen kelléke</b>i a különböző
          irodatechnikai berendezések. Ezek közül a legjelentősebb <b>a</b>{" "}
          <span lang="en">
            <b>HP DesignJet</b>
          </span>{" "}
          <b>T520-as plotterünk, valamint hordozható</b>{" "}
          <span lang="en">
            <b>HP OfficeJet 200</b>
          </span>{" "}
          <b>mobil nyomtatónk</b>.<br />
          <br />
          Eszközeinket és gépkocsiparkunkat dolgozóink igényeinek
          figyelembevételével folyamatosan fejlesztjük.
        </p>
      </div>

      <div className="w-1/3">
        <EquipmentCard {...items[1]} className="h-full" />
      </div>
    </div>
  );
};
export default PrinterEquipmentDesktop;
