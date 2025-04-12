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

export type User = {
  id: string;
  name: string;
  email: string;
  username: string;
  role: string | null;
  image?: string | null;
};

export interface Role {
  id: number;
  slug: string;
  title: string;
  description: string;
  created_at: string;
  updated_at: string;
}

export interface UserData {
  id: number;
  role_id: number;
  username: string;
  firstname: string;
  lastname: string;
  birthdate: string;
  phonenumber: string;
  email: string;
  email_verified_at: string | null;
  password_changed_at: string;
  created_at: string;
  updated_at: string;
  role: Role;
}
