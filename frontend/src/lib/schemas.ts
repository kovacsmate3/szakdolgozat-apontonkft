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
