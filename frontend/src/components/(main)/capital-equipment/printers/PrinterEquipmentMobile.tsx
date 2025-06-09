import { EquipmentProps } from "@/lib/types";
import EquipmentCard from "../EquipmentCard";

const PrinterEquipmentMobile = ({ items }: EquipmentProps) => {
  return (
    <div className="lg:hidden space-y-6">
      <EquipmentCard {...items[0]} className="max-w-md mx-auto" />

      <div className="text-white bg-black/90 p-5 rounded-lg max-w-md mx-auto">
        <p className="text-justify">
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

      <EquipmentCard {...items[1]} className="max-w-md mx-auto" />
    </div>
  );
};
export default PrinterEquipmentMobile;
