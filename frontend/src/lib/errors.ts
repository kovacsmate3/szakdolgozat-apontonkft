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
