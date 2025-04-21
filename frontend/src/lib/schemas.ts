import { parse, subYears } from "date-fns";
import { z } from "zod";

const MAX_FILE_SIZE = 5 * 1024 * 1024;
const ACCEPTED_FILE_TYPES = [
  "application/pdf",
  "application/msword",
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
  "application/vnd.ms-excel",
  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
  "image/jpeg",
  "image/png",
];

export const contactFormSchema = z.object({
  firstName: z
    .string()
    .min(2, { message: "A keresztnév legalább 2 karakter hosszú kell legyen." })
    .max(50, { message: "A keresztnév legfeljebb 50 karakter lehet." })
    .regex(/^[A-Za-zÁÉÍÓÖŐÚÜŰáéíóöőúüű\s'-]+$/, {
      message: "A keresztnév csak betűket és szóközöket tartalmazhat.",
    }),

  lastName: z
    .string()
    .min(2, { message: "A vezetéknév legalább 2 karakter hosszú kell legyen." })
    .max(50, { message: "A vezetéknév legfeljebb 50 karakter lehet." })
    .regex(/^[A-Za-zÁÉÍÓÖŐÚÜŰáéíóöőúüű\s'-]+$/, {
      message: "A vezetéknév csak betűket és szóközöket tartalmazhat.",
    }),

  email: z
    .string()
    .email({ message: "Kérjük, érvényes e-mail címet adjon meg." }),

  phone: z
    .string()
    .regex(/^\+?[0-9\s\-()]{7,20}$/, {
      message: "Kérjük, érvényes telefonszámot adjon meg.",
    })
    .or(z.literal("")),

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

// Vezetéknév: nagybetűvel kezdődik, utána betűk, szóköz, kötőjel és pont jöhet
const lastNameRegex = /^\p{Lu}[\p{L}\s.-]*$/u;
// Keresztnév: nagybetűvel kezdődik, utána betűk, szóköz és pont jöhet (kötőjel NEM)
const firstNameRegex = /^\p{Lu}[\p{L}\s.]*$/u;

const allowedAreaCodes = [
  "1",
  "20",
  "21",
  "22",
  "23",
  "24",
  "25",
  "26",
  "27",
  "28",
  "29",
  "30",
  "31",
  "32",
  "33",
  "34",
  "35",
  "36",
  "37",
  "40",
  "42",
  "44",
  "45",
  "46",
  "47",
  "48",
  "49",
  "50",
  "51",
  "52",
  "53",
  "54",
  "55",
  "56",
  "57",
  "59",
  "60",
  "61",
  "62",
  "63",
  "66",
  "68",
  "69",
  "70",
  "71",
  "72",
  "73",
  "74",
  "75",
  "76",
  "77",
  "78",
  "79",
  "80",
  "82",
  "83",
  "84",
  "85",
  "87",
  "88",
  "89",
  "90",
  "91",
  "92",
  "93",
  "94",
  "95",
  "96",
  "99",
].join("|");

const phoneRegex = new RegExp(`^(\\+36|06)(${allowedAreaCodes})\\d{7}$`);

export const userFormSchema = z.object({
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
