import { EquipmentItem } from "@/lib/types";
import Image from "next/image";

interface EquipmentCardProps extends EquipmentItem {
  className?: string;
}

const EquipmentCard = ({
  name,
  description,
  image,
  alt,
  className = "",
}: EquipmentCardProps) => (
  <div className={`bg-white rounded-lg shadow-md overflow-hidden ${className}`}>
    <div className="p-4 text-center">
      <h3 className="text-xl font-semibold mb-3 text-black">{name}</h3>
      <div className="mb-2">
        <Image
          src={image}
          alt={alt}
          title={alt}
          width={400}
          height={300}
          className="mx-auto max-w-full w-auto h-auto"
        />
      </div>
      {description && (
        <>
          <hr className="border-gray-300 my-2" />
          <p className="text-sm text-gray-600 pt-2 px-2">{description}</p>
        </>
      )}
    </div>
  </div>
);

export default EquipmentCard;
