import { EquipmentProps } from "@/lib/types";
import PrinterEquipmentMobile from "./PrinterEquipmentMobile";
import PrinterEquipmentDesktop from "./PrinterEquipmentDesktop";

const PrinterEquipment = ({ items }: EquipmentProps) => {
  return (
    <>
      <PrinterEquipmentMobile items={items} />
      <PrinterEquipmentDesktop items={items} />
    </>
  );
};

export default PrinterEquipment;
