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
  is_headquarter: boolean;
  user_id?: number;
  address?: Address;
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

  fullAddress?(): string;
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

export interface Trip {
  id: number;
  car_id: number;
  user_id: number;
  start_location_id: number;
  destination_location_id: number;
  start_time: string;
  end_time: string | null;
  planned_distance: number | null;
  actual_distance: number | null;
  start_odometer: number | null;
  end_odometer: number | null;
  planned_duration: string | null;
  actual_duration: string | null;
  dict_id: number | null;
  car?: Car;
  user?: UserData;
  start_location?: Location;
  destination_location?: Location;
  travel_purpose?: TravelPurposeDictionary;
}

export interface TravelPurposeDictionary {
  id: number;
  travel_purpose: string;
  type: string;
  note: string | null;
  is_system: boolean;
  user_id?: number;
}

export interface FuelExpense {
  id: number;
  car_id: number;
  user_id: number;
  location_id: number;
  trip_id: number | null;
  expense_date: string;
  amount: number;
  currency: string;
  fuel_quantity: number;
  odometer: number;
  car?: Car;
  user?: UserData;
  location?: Location;
  trip?: Trip;
}

export interface ChartProps {
  token: string;
}
