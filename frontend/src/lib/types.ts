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
  role: Role;
}

export interface PasswordChangeData {
  current_password: string;
  password: string;
  password_confirmation: string;
}

export interface UserPayload {
  username: string;
  firstname: string;
  lastname: string;
  birthdate: string;
  phonenumber: string;
  email: string;
  password: string;
  role_id: number;
}

export interface Location {
  id: number;
  name: string;
  location_type: string;
  is_headquarter: number;
}

export interface Address {
  id: number;
  location_id: number | null;
  country: string;
  postalcode: number;
  city: string;
  road_name: string;
  public_space_type: string;
  building_number: string;
  location: Location | null;
}

export interface Car {
  id: number;
  user_id: number;
  car_type: string;
  license_plate: string;
  manufacturer: string;
  model: string;
  fuel_type: string;
  standard_consumption: number;
  capacity: number;
  fuel_tank_capacity: number;
  user: UserData;
}

export interface CarComponentProps {
  car: Car;
}

export interface FuelPrice {
  id: number;
  period: string;
  petrol: number;
  mixture: number;
  diesel: number;
  lp_gas: number;
}

export interface FuelPricePayload {
  period: string;
  petrol: number;
  mixture: number;
  diesel: number;
  lp_gas: number;
}

export enum LawCategories {
  LAND_MEASUREMENT = 1,
  PROPERTY_REGISTRY = 2,
  CONSTRUCTION = 3,
  LAND_AFFAIRS = 4,
  FEES = 5,
  OTHER_LAWS = 6,
}

export interface Law {
  id: number;
  title: string;
  official_ref: string;
  date_of_enactment: string | null;
  is_active: boolean;
  link: string;
  category_id: number;
}
