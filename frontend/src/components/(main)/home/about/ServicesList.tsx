import React from "react";
import { servicesList } from "@/lib/data/about-section-data";

interface ServicesListProps {
  textSize?: string;
}

const ServicesList: React.FC<ServicesListProps> = ({ textSize = "" }) => {
  return (
    <div className="px-4">
      <ul
        className={`grid grid-cols-2 gap-2 list-disc list-inside text-gray-900 dark:text-gray-200 ${textSize}`}
      >
        {servicesList.map((service, index) => (
          <li key={index}>{service}</li>
        ))}
      </ul>
    </div>
  );
};
export default ServicesList;
