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
