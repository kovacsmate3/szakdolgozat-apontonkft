type UserValidationErrorResponse = {
  message: string;
  errors: {
    username?: string[];
    phonenumber?: string[];
    email?: string[];
  };
};

export class ApiError extends Error {
  constructor(
    public status: number,
    public data: UserValidationErrorResponse
  ) {
    super("API error");
  }
}
