export interface ImageData {
  src: string;
  alt: string;
  title: string;
  width: number;
  height: number;
}

export interface ReferenceImage {
  src: string;
  originalSrc: string;
  alt: string;
  title: string;
}

export interface Reference {
  image: ReferenceImage;
  title: string;
  description: string;
}

export interface EquipmentItem {
  name: string;
  description?: string;
  image: string;
  alt: string;
}

export interface EquipmentProps {
  items: [EquipmentItem, EquipmentItem];
}

export interface CompanyOfficeData {
  office: string;
  manager: string;
  email: string;
  phone: string;
}
