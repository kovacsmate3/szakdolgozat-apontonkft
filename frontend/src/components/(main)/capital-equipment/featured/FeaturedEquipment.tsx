import { EquipmentProps } from "@/lib/types";
import FeaturedEquipmentMobile from "./FeaturedEquipmentMobile";
import FeaturedEquipmentDesktop from "./FeaturedEquipmentDesktop";

const FeaturedEquipment = ({ items }: EquipmentProps) => {
  return (
    <>
      <FeaturedEquipmentMobile items={items} />
      <FeaturedEquipmentDesktop items={items} />
    </>
  );
};

export default FeaturedEquipment;
