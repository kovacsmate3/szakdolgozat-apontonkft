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
  }
}
