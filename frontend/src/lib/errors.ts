type UserValidationErrorResponse = {
  message: string;
  errors: {
    username?: string[];
    phonenumber?: string[];
    email?: string[];
  };
};

export class UserApiError extends Error {
  constructor(
    public status: number,
    public data: UserValidationErrorResponse
  ) {
    super("API error");
    this.name = "UserApiError";
  }
}

type FuelPriceValidationErrorResponse = {
  message: string;
  errors: {
    period?: string[];
  };
};

export class FuelPriceApiError extends Error {
  constructor(
    public status: number,
    public data: FuelPriceValidationErrorResponse
  ) {
    super("API error");
    this.name = "FuelPriceApiError";
  }
}

type PasswordChangeValidationErrorResponse = {
  message: string;
  errors?: {
    current_password?: string[];
    password?: string[];
    password_confirmation?: string[];
  };
};

export class PasswordChangeApiError extends Error {
  constructor(
    public status: number,
    public data: PasswordChangeValidationErrorResponse
  ) {
    super("Password change error");
    this.name = "PasswordChangeApiError";
  }
}

type CarValidationErrorResponse = {
  message: string;
  errors: {
    user_id?: string[];
    car_type?: string[];
    license_plate?: string[];
    manufacturer?: string[];
    model?: string[];
    fuel_type?: string[];
    standard_consumption?: string[];
    capacity?: string[];
    fuel_tank_capacity?: string[];
  };
};

export class CarApiError extends Error {
  constructor(
    public status: number,
    public data: CarValidationErrorResponse
  ) {
    super("Car API error");
    this.name = "CarApiError";
  }
}

type TravelPurposeDictionaryValidationErrorResponse = {
  message: string;
  errors: {
    travel_purpose?: string[];
    type?: string[];
    note?: string[];
  };
};

export class TravelPurposeDictionaryApiError extends Error {
  constructor(
    public status: number,
    public data: TravelPurposeDictionaryValidationErrorResponse
  ) {
    super("Travel Purpose Dictionary API error");
    this.name = "TravelPurposeDictionaryApiError";
  }
}

type LocationValidationErrorResponse = {
  message: string;
  errors: {
    name?: string[];
    location_type?: string[];
    is_headquarter?: string[];
    country?: string[];
    postalcode?: string[];
    city?: string[];
    road_name?: string[];
    public_space_type?: string[];
    building_number?: string[];
  };
};

export class LocationApiError extends Error {
  constructor(
    public status: number,
    public data: LocationValidationErrorResponse
  ) {
    super("Location API error");
    this.name = "LocationApiError";
  }
}

// Utazás validációs hiba
type TripValidationErrorResponse = {
  message: string;
  errors?: {
    car_id?: string[];
    start_location_id?: string[];
    destination_location_id?: string[];
    start_time?: string[];
    end_time?: string[];
    start_odometer?: string[];
    end_odometer?: string[];
    actual_distance?: string[];
    dict_id?: string[];
  };
};

export class TripApiError extends Error {
  constructor(
    public status: number,
    public data: TripValidationErrorResponse
  ) {
    super("Trip API error");
    this.name = "TripApiError";
  }
}

// Tankolás validációs hiba
type FuelExpenseValidationErrorResponse = {
  message: string;
  errors?: {
    car_id?: string[];
    location_id?: string[];
    expense_date?: string[];
    amount?: string[];
    currency?: string[];
    fuel_quantity?: string[];
    odometer?: string[];
    trip_id?: string[];
  };
};

export class FuelExpenseApiError extends Error {
  constructor(
    public status: number,
    public data: FuelExpenseValidationErrorResponse
  ) {
    super("Fuel Expense API error");
    this.name = "FuelExpenseApiError";
  }
}
