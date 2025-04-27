import { parse, subYears } from "date-fns";
import { z } from "zod";
import { publicSpaceTypes } from "./data/location-pages-data";
import {
  ACCEPTED_FILE_TYPES,
  firstNameRegex,
  lastNameRegex,
  MAX_FILE_SIZE,
  phoneRegex,
} from "./constants";

export const contactFormSchema = z.object({
  firstName: z
    .string()
    .min(2, { message: "A keresztnév legalább 2 karakter hosszú kell legyen." })
    .max(50, { message: "A keresztnév legfeljebb 50 karakter lehet." })
    .regex(firstNameRegex, {
      message:
        "A keresztnévnek nagybetűvel kell kezdődnie, és csak betűket, szóközt vagy pontot tartalmazhat.",
    }),

  lastName: z
    .string()
    .min(2, { message: "A vezetéknév legalább 2 karakter hosszú kell legyen." })
    .max(50, { message: "A vezetéknév legfeljebb 50 karakter lehet." })
    .regex(lastNameRegex, {
      message:
        "A vezetéknévnek nagybetűvel kell kezdődnie, és csak betűket, szóközt, kötőjelet vagy pontot tartalmazhat.",
    }),

  email: z
    .string()
    .min(1, "Az email cím megadása kötelező.")
    .email("Érvénytelen email cím formátum.")
    .max(255, "Az email cím maximum 255 karakter hosszú lehet."),

  phone: z
    .string()
    .min(1, "A telefonszám megadása kötelező.")
    .max(30, "A telefonszám maximum 30 karakter hosszú lehet.")
    .regex(phoneRegex, {
      message:
        "Érvénytelen magyar telefonszám formátum. (+36 vagy 06, majd érvényes körzetszám és 7 számjegy)",
    }),

  reason: z.enum(["quotation", "employment", "other"], {
    errorMap: () => ({
      message: "Kérjük, válassza ki a kapcsolatfelvétel okát.",
    }),
  }),

  message: z
    .string()
    .min(10, {
      message: "Az üzenetnek legalább 10 karakter hosszúnak kell lennie.",
    })
    .max(500, {
      message: "Az üzenet legfeljebb 500 karakter hosszú lehet.",
    }),

  robot: z.boolean().refine((val) => val === true, {
    message: "Kérjük, igazolja, hogy nem robot.",
  }),
  file: z
    .instanceof(File)
    .refine(
      (file) => !file || (file instanceof File && file.size <= MAX_FILE_SIZE),
      {
        message: "A fájl mérete nem haladhatja meg az 5 MB-ot.",
      }
    )
    .refine((file) => !file || ACCEPTED_FILE_TYPES.includes(file.type), {
      message:
        "Csak PDF, Word, Excel, JPG vagy PNG fájlok feltöltése engedélyezett.",
    })
    .optional(),
  base64File: z.string().optional(),
  fileName: z.string().optional(),
});

export const userCreateSchema = z.object({
  username: z
    .string({ required_error: "A felhasználónév megadása kötelező." })
    .min(1, "A felhasználónév megadása kötelező.")
    .max(25, "A felhasználónév maximum 25 karakter hosszú lehet."),
  firstname: z
    .string({ required_error: "A keresztnév megadása kötelező." })
    .min(1, "A keresztnév megadása kötelező.")
    .max(50, "A keresztnév maximum 50 karakter hosszú lehet.")
    .regex(
      firstNameRegex,
      "A keresztnévnek nagybetűvel kell kezdődnie, és csak betűket, szóközt vagy pontot tartalmazhat."
    ),
  lastname: z
    .string({ required_error: "A vezetéknév megadása kötelező." })
    .min(1, "A vezetéknév megadása kötelező.")
    .max(50, "A vezetéknév maximum 50 karakter hosszú lehet.")
    .regex(
      lastNameRegex,
      "A vezetéknévnek nagybetűvel kell kezdődnie, és csak betűket, szóközt, kötőjelet vagy pontot tartalmazhat."
    ),
  birthdate: z
    .string({ required_error: "A születési dátum megadása kötelező." })
    .refine((date) => {
      const d = new Date(date);
      const min = new Date();
      min.setFullYear(min.getFullYear() - 18);
      return d <= min;
    }, "A felhasználónak legalább 18 évesnek kell lennie."),
  phonenumber: z
    .string()
    .min(1, "A telefonszám megadása kötelező.")
    .max(30, "A telefonszám maximum 30 karakter hosszú lehet.")
    .regex(
      phoneRegex,
      "Érvénytelen magyar telefonszám formátum. (+36 vagy 06, majd érvényes körzetszám és 7 számjegy)"
    ),
  email: z
    .string()
    .min(1, "Az email cím megadása kötelező.")
    .email("Érvénytelen email cím formátum.")
    .max(255, "Az email cím maximum 255 karakter hosszú lehet."),
  password: z
    .string()
    .min(1, "A jelszó megadása kötelező.")
    .superRefine((val, ctx) => {
      if (val.length < 8) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: "A jelszónak legalább 8 karakter hosszúnak kell lennie.",
        });
      }
      if (!/[a-z]/.test(val)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: "A jelszónak tartalmaznia kell kisbetűt is.",
        });
      }
      if (!/[A-Z]/.test(val)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: "A jelszónak tartalmaznia kell nagybetűt is.",
        });
      }
      if (!/\d/.test(val)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: "A jelszónak tartalmaznia kell legalább egy számot.",
        });
      }
      if (!/[^a-zA-Z0-9]/.test(val)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message:
            "A jelszónak tartalmaznia kell legalább egy speciális karaktert.",
        });
      }
    }),

  role_id: z.enum(["1", "2", "3"], {
    required_error: "A szerepkör kiválasztása kötelező.",
  }),
});

export const userEditSchema = userCreateSchema.extend({
  password: z
    .string()
    .min(1, "A jelszó megadása kötelező.")
    .superRefine((val, ctx) => {
      if (!val || val.length === 0) return;
      if (val.length < 8) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: "A jelszónak legalább 8 karakter hosszúnak kell lennie.",
        });
      }
      if (!/[a-z]/.test(val)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: "A jelszónak tartalmaznia kell kisbetűt is.",
        });
      }
      if (!/[A-Z]/.test(val)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: "A jelszónak tartalmaznia kell nagybetűt is.",
        });
      }
      if (!/\d/.test(val)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: "A jelszónak tartalmaznia kell legalább egy számot.",
        });
      }
      if (!/[^a-zA-Z0-9]/.test(val)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message:
            "A jelszónak tartalmaznia kell legalább egy speciális karaktert.",
        });
      }
    }),
});

export const personalInfoSchema = z.object({
  lastname: z
    .string({ required_error: "A vezetéknév megadása kötelező." })
    .trim()
    .min(1, "A vezetéknév megadása kötelező.")
    .max(50, "A vezetéknév maximum 50 karakter hosszú lehet.")
    .regex(
      lastNameRegex,
      "A vezetéknévnek nagybetűvel kell kezdődnie, és csak betűket, szóközt, kötőjelet vagy pontot tartalmazhat."
    ),

  firstname: z
    .string({ required_error: "A keresztnév megadása kötelező." })
    .trim()
    .min(1, "A keresztnév megadása kötelező.")
    .max(50, "A keresztnév maximum 50 karakter hosszú lehet.")
    .regex(
      firstNameRegex,
      "A keresztnévnek nagybetűvel kell kezdődnie, és csak betűket, szóközt vagy pontot tartalmazhat."
    ),

  birthdate: z
    .string({ required_error: "A születési dátum megadása kötelező." })
    .refine(
      (val) => {
        const parsedDate = parse(val, "yyyy-MM-dd", new Date());
        const minDate = subYears(new Date(), 18);
        return !isNaN(parsedDate.getTime()) && parsedDate <= minDate;
      },
      { message: "A felhasználónak legalább 18 évesnek kell lennie." }
    ),
});

export const contactInfoSchema = z.object({
  email: z
    .string({ required_error: "Az email cím megadása kötelező." })
    .trim()
    .min(1, "Az email cím megadása kötelező.")
    .max(255, "Az email cím maximum 255 karakter hosszú lehet.")
    .email("Érvénytelen email cím formátum."),

  phonenumber: z
    .string({ required_error: "A telefonszám megadása kötelező." })
    .trim()
    .min(1, "A telefonszám megadása kötelező.")
    .max(30, "A telefonszám maximum 30 karakter hosszú lehet.")
    .regex(
      phoneRegex,
      "Érvénytelen magyar telefonszám formátum. (+36 vagy 06, majd érvényes körzetszám és 7 számjegy)"
    ),
});

// Először definiáljuk az alap objektum sémát refine nélkül
export const passwordChangeBaseSchema = z.object({
  current_password: z.string().min(1, "A jelenlegi jelszó megadása kötelező."),
  password: z.string().min(1, "Az új jelszó megadása kötelező."),
  password_confirmation: z
    .string()
    .min(1, "Az új jelszó megerősítése kötelező."),
});

// Majd hozzáadjuk a refine szabályokat
export const passwordChangeFormSchema = passwordChangeBaseSchema
  .refine((data) => data.password === data.password_confirmation, {
    message: "Az új jelszó és a megerősítés nem egyezik meg.",
    path: ["password_confirmation"],
  })
  .refine((data) => data.current_password !== data.password, {
    message: "Az új jelszó nem lehet azonos a jelenlegi jelszóval.",
    path: ["password"],
  });

export const fuelPriceFormSchema = z.object({
  period: z.string().nonempty("Az időszak megadása kötelező."),
  petrol: z.coerce.number().min(0, "A benzin ára nem lehet negatív."),
  mixture: z.coerce.number().min(0, "A keverék ára nem lehet negatív."),
  diesel: z.coerce.number().min(0, "A dízel ára nem lehet negatív."),
  lp_gas: z.coerce.number().min(0, "Az LPG ára nem lehet negatív."),
});

export const carFormSchema = z.object({
  user_id: z.string({
    required_error: "A felhasználó kiválasztása kötelező.",
  }),
  car_type: z
    .string({ required_error: "A jármű típusának megadása kötelező." })
    .min(1, "A jármű típusának megadása kötelező.")
    .max(30, "A jármű típusa maximum 30 karakter hosszú lehet."),

  license_plate: z
    .string({ required_error: "A rendszám megadása kötelező." })
    .min(1, "A rendszám megadása kötelező.")
    .max(20, "A rendszám maximum 20 karakter hosszú lehet.")
    .refine(
      (val) => {
        // Régi formátum: ABC-123
        const oldFormat = /^[A-Z]{3}-\d{3}$/.test(val);
        if (oldFormat) return true;

        // Új formátum: AA-BB-123
        // Pontosan 3 számjegyet követelünk meg
        const newFormat = /^([A-Z]{2})-([A-Z]{2})-(\d{3})$/.exec(val);
        if (newFormat) {
          const firstPart = newFormat[1]; // Az első két betű

          const vowels = "AEIOU";

          // Az első két betű vagy mind magánhangzó, vagy mind mássalhangzó
          const isFirstPartValid =
            (vowels.includes(firstPart[0]) && vowels.includes(firstPart[1])) ||
            (!vowels.includes(firstPart[0]) && !vowels.includes(firstPart[1]));

          // A számok 000-999 között lehetnek, ami a \d{3} regex miatt már biztosított
          return isFirstPartValid;
        }

        return false;
      },
      {
        message:
          "A rendszám formátuma érvénytelen. Példa: ABC-123 vagy AA-BB-123 (első betűpár: 2 magán- vagy 2 mássalhangzó, pontosan 3 számjegy)",
      }
    ),
  manufacturer: z
    .string({ required_error: "A gyártó megadása kötelező." })
    .min(1, "A gyártó megadása kötelező.")
    .max(100, "A gyártó neve maximum 100 karakter hosszú lehet."),
  model: z
    .string({ required_error: "A modell megadása kötelező." })
    .min(1, "A modell megadása kötelező.")
    .max(100, "A modell maximum 100 karakter hosszú lehet."),
  fuel_type: z
    .string({ required_error: "Az üzemanyag típus megadása kötelező." })
    .min(1, "Az üzemanyag típus megadása kötelező.")
    .max(50, "Az üzemanyag típus maximum 50 karakter hosszú lehet."),
  standard_consumption: z
    .string({ required_error: "A normál fogyasztás megadása kötelező." })
    .min(1, "A normál fogyasztás megadása kötelező.")
    .refine((val) => !isNaN(parseFloat(val)) && parseFloat(val) >= 0, {
      message: "A normál fogyasztás nem lehet negatív érték.",
    }),
  capacity: z
    .string({ required_error: "A hengerűrtartalom megadása kötelező." })
    .min(1, "A hengerűrtartalom megadása kötelező.")
    .refine((val) => !isNaN(parseInt(val)) && parseInt(val) > 0, {
      message: "A hengerűrtartalom pozitív egész szám kell legyen.",
    }),
  fuel_tank_capacity: z
    .string({
      required_error: "Az üzemanyagtartály kapacitás megadása kötelező.",
    })
    .min(1, "Az üzemanyagtartály kapacitás megadása kötelező.")
    .refine((val) => !isNaN(parseInt(val)) && parseInt(val) > 0, {
      message: "Az üzemanyagtartály kapacitás pozitív egész szám kell legyen.",
    }),
});

export const travelPurposeDictionaryFormSchema = z.object({
  travel_purpose: z
    .string({ required_error: "Az utazási cél megadása kötelező." })
    .min(1, "Az utazási cél megadása kötelező.")
    .max(100, "Az utazási cél maximum 100 karakter hosszú lehet."),
  type: z
    .string({ required_error: "Az utazási cél típusának megadása kötelező." })
    .min(1, "Az utazási cél típusának megadása kötelező.")
    .max(50, "Az utazási cél típusa maximum 50 karakter hosszú lehet."),
  note: z
    .string()
    .max(500, "A megjegyzés maximum 500 karakter hosszú lehet.")
    .optional()
    .nullable(),
  is_system: z.boolean().optional(),
});

export const locationFormSchema = z.object({
  name: z
    .string({ required_error: "A helyszín nevének megadása kötelező." })
    .min(1, "A helyszín nevének megadása kötelező.")
    .max(255, "A helyszín neve maximum 255 karakter hosszú lehet."),
  location_type: z
    .string({ required_error: "A helyszín típusának megadása kötelező." })
    .min(1, "A helyszín típusának megadása kötelező.")
    .refine(
      (val) =>
        ["partner", "töltőállomás", "bolt", "egyéb", "telephely"].includes(val),
      "A helyszín típusa csak partner, töltőállomás, bolt vagy egyéb lehet."
    ),
  is_headquarter: z.boolean().default(false),
  country: z
    .string({ required_error: "Az ország megadása kötelező." })
    .min(1, "Az ország megadása kötelező.")
    .max(100, "Az ország neve maximum 100 karakter hosszú lehet.")
    .refine((val) => /^[\p{L}\s\-\.]+$/u.test(val), {
      message:
        "Az ország neve csak betűket, szóközöket és kötőjeleket tartalmazhat.",
    }),
  postalcode: z
    .string({ required_error: "Az irányítószám megadása kötelező." })
    .min(1, "Az irányítószám megadása kötelező.")
    .refine((val) => /^\d{4}$/.test(val), {
      message: "Az irányítószám pontosan 4 számjegyből kell álljon.",
    }),
  city: z
    .string({ required_error: "A város megadása kötelező." })
    .min(1, "A város megadása kötelező.")
    .max(100, "A város neve maximum 100 karakter hosszú lehet.")
    .refine((val) => /^[\p{L}\s\-\.]+$/u.test(val), {
      message:
        "A város neve csak betűket, szóközöket és kötőjeleket tartalmazhat.",
    }),
  road_name: z
    .string({ required_error: "A közterület nevének megadása kötelező." })
    .min(1, "A közterület nevének megadása kötelező.")
    .max(100, "A közterület neve maximum 100 karakter hosszú lehet.")
    .refine((val) => /^[\p{L}0-9\s\-\.]+$/u.test(val), {
      message:
        "A közterület neve csak betűket, számokat, szóközöket és kötőjeleket tartalmazhat.",
    }),
  public_space_type: z
    .string({ required_error: "A közterület jellegének megadása kötelező." })
    .min(1, "A közterület jellegének megadása kötelező.")
    .max(50, "A közterület jellege maximum 50 karakter hosszú lehet.")
    .refine((val) => publicSpaceTypes.includes(val), {
      message: "A közterület jellege nem megfelelő érték.",
    }),
  building_number: z
    .string({ required_error: "A házszám megadása kötelező." })
    .min(1, "A házszám megadása kötelező.")
    .max(50, "A házszám maximum 50 karakter hosszú lehet.")
    .refine((val) => /^[0-9]+(?:[\/-][0-9A-Za-z]+)*\.$/.test(val), {
      message:
        "A házszám formátuma érvénytelen. A házszámnak számmal kell kezdődnie és ponttal végződnie (pl. 1., 3/A., 5-7.).",
    }),
});

// Utazás űrlap validációs séma
export const tripFormSchema = z
  .object({
    car_id: z
      .string({
        required_error: "A jármű azonosító megadása kötelező.",
      })
      .min(1, "A jármű kiválasztása kötelező"),

    start_location_id: z
      .string({
        required_error: "Az indulási helyszín megadása kötelező.",
      })
      .min(1, "Az indulási helyszín kiválasztása kötelező"),

    destination_location_id: z
      .string({
        required_error: "Az érkezési helyszín megadása kötelező.",
      })
      .min(1, "Az érkezési helyszín kiválasztása kötelező"),

    start_time: z.date({
      required_error: "Az indulási idő megadása kötelező.",
    }),

    end_time: z
      .date()
      .refine((date) => date instanceof Date && !isNaN(date.getTime()), {
        message: "Érvényes érkezési időpontot adjon meg",
      })
      .optional(),

    planned_distance: z.coerce
      .number()
      .min(0, "A tervezett távolság nem lehet negatív érték.")
      .optional(),

    actual_distance: z.coerce
      .number()
      .min(0, "A tényleges távolság nem lehet negatív érték.")
      .optional(),

    start_odometer: z.coerce
      .number()
      .min(0, "A kilométeróra kezdő állása nem lehet negatív érték.")
      .int("A kilométeróra kezdő állása csak egész szám lehet.")
      .optional(),

    end_odometer: z.coerce
      .number()
      .min(0, "A kilométeróra záró állása nem lehet negatív érték.")
      .int("A kilométeróra záró állása csak egész szám lehet.")
      .optional(),

    planned_duration: z
      .string()
      .regex(
        /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/,
        "A tervezett időtartam érvénytelen formátumú (óra:perc:másodperc)."
      )
      .optional(),

    actual_duration: z
      .string()
      .regex(
        /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/,
        "A tényleges időtartam érvénytelen formátumú (óra:perc:másodperc)."
      )
      .optional(),

    dict_id: z.string().optional().nullable(),

    // Ez a mező csak a frontend működését befolyásolja, az adatbázisba nem kerül
    // Azt jelzi, hogy a távolság számításához a kilométeróra értékeket vagy a
    // közvetlenül megadott távolságot használjuk-e
    use_odometer: z.boolean().default(true),
  })
  .refine((data) => data.start_location_id !== data.destination_location_id, {
    message: "Az indulási és érkezési helyszín nem lehet azonos.",
    path: ["destination_location_id"],
  })
  .refine((data) => !data.end_time || data.start_time <= data.end_time, {
    message: "Az érkezési idő nem lehet korábbi, mint az indulási idő.",
    path: ["end_time"],
  })
  .refine(
    (data) =>
      !data.start_odometer ||
      !data.end_odometer ||
      data.start_odometer <= data.end_odometer,
    {
      message:
        "A kilométeróra záró állása nem lehet kisebb, mint a kezdő állása.",
      path: ["end_odometer"],
    }
  )
  .refine(
    (data) =>
      !data.use_odometer ||
      (data.start_odometer !== undefined && data.end_odometer !== undefined),
    {
      message:
        "Ha kilométeróra alapján számol, adja meg a kezdő és záró kilométeróra állást.",
      path: ["start_odometer", "end_odometer"],
    }
  )
  .refine((data) => data.use_odometer || data.actual_distance !== undefined, {
    message:
      "Ha nem kilométeróra alapján számol, adja meg a megtett távolságot.",
    path: ["actual_distance"],
  });

// Tankolás űrlap validációs séma
export const fuelExpenseFormSchema = z.object({
  car_id: z
    .string({
      required_error: "A jármű azonosító megadása kötelező.",
    })
    .min(1, "A jármű kiválasztása kötelező"),

  location_id: z
    .string({
      required_error: "A helyszín azonosító megadása kötelező.",
    })
    .min(1, "A helyszín kiválasztása kötelező"),

  expense_date: z.date({
    required_error: "A költség dátumának megadása kötelező.",
  }),

  amount: z.coerce
    .number()
    .min(0, "Az összeg nem lehet negatív érték.")
    .refine((value) => value > 0, {
      message: "Az összeg megadása kötelező.",
    }),

  currency: z
    .string({
      required_error: "A pénznem megadása kötelező.",
    })
    .max(10, "A pénznem maximum 10 karakter hosszú lehet.")
    .default("HUF"),

  fuel_quantity: z.coerce
    .number()
    .min(0, "Az üzemanyag mennyiség nem lehet negatív érték.")
    .refine((value) => value > 0, {
      message: "Az üzemanyag mennyiség megadása kötelező.",
    }),

  odometer: z.coerce
    .number()
    .min(0, "A kilométeróra állása nem lehet negatív érték.")
    .int("A kilométeróra állása csak egész szám lehet.")
    .refine((value) => value > 0, {
      message: "A kilométeróra állás megadása kötelező.",
    }),

  trip_id: z.string().optional().nullable(),
  user_id: z.string().optional(),
});
