import { EquipmentItem } from "@/lib/types";
import EquipmentCard from "../EquipmentCard";

interface SecondaryEquipmentProps {
  items: EquipmentItem[];
}

const SecondaryEquipment = ({ items }: SecondaryEquipmentProps) => {
  return (
    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
      {items.map((item, index) => (
        <EquipmentCard key={`secondary-equipment-${index}`} {...item} />
      ))}
    </div>
  );
};

export default SecondaryEquipment;
