import { equipmentData } from "@/lib/data/capital-equipment-page-data";
import EquipmentCard from "./EquipmentCard";

const EquipmentList = ({ start, end }: { start: number; end: number }) => (
  <div className="grid grid-cols-2 md:grid-cols-4 gap-6 justify-items-center">
    {equipmentData.slice(start, end).map((item, index) => (
      <EquipmentCard key={index} {...item} />
    ))}
  </div>
);
export default EquipmentList;
