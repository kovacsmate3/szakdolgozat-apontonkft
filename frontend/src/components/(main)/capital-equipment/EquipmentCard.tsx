import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { EquipmentItem } from "@/lib/types";
import Image from "next/image";

const EquipmentCard = ({ name, description, image, alt }: EquipmentItem) => (
  <Card className="shadow-md rounded-lg w-full max-w-[350px]">
    <CardHeader className="text-center flex justify-center">
      <CardTitle className="text-xl font-semibold">{name}</CardTitle>
    </CardHeader>
    <CardContent className="text-center">
      <Image
        src={image}
        alt={alt}
        width={400}
        height={300}
        className="rounded-md mx-auto max-w-full h-auto"
      />
      {description && (
        <p className="mt-2 border-t border-gray-300 pt-2 text-sm text-gray-600">
          {description}
        </p>
      )}
    </CardContent>
  </Card>
);
export default EquipmentCard;
