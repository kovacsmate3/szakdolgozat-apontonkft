"use server";

import { Resend } from "resend";
import { z } from "zod";
//import { EmailTemplate } from "@/components/ui/email-template";
import { contactFormSchema } from "./schemas";
import AutoReply from "@/components/(main)/contact/mail/AutoReply";

const resend = new Resend(process.env.RESEND_API_KEY);

export const send = async (
  emailFormData: z.infer<typeof contactFormSchema>
) => {
  try {
    const { error } = await resend.emails.send({
      from: `A-Ponton Mérnökiroda Kft. <${process.env.RESEND_FROM_EMAIL}>`,
      to: [emailFormData.email],
      subject: "Köszönjük megkeresését",
      react: AutoReply({
        firstName: emailFormData.firstName,
        lastName: emailFormData.lastName,
      }) as React.ReactNode,
    });

    if (error) {
      throw error;
    }
  } catch (e) {
    throw e;
  }
};
