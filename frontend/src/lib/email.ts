"use server";

import { Resend } from "resend";
import { z } from "zod";
//import { EmailTemplate } from "@/components/ui/email-template";
import { contactFormSchema } from "./schemas";

const resend = new Resend(process.env.RESEND_API_KEY);

export const send = async (
  emailFormData: z.infer<typeof contactFormSchema>
) => {
  try {
    // TODO: Add this emailFormData to some database

    const { error } = await resend.emails.send({
      from: `A-Ponton Mérnökiroda Kft. <${process.env.RESEND_FROM_EMAIL}>`,
      to: [emailFormData.email],
      subject: "Welcome",
      html: `<div><h1>Üdvözöljük, ${emailFormData.firstName}!</h1></div>`,
    });

    if (error) {
      throw error;
    }
  } catch (e) {
    throw e;
  }
};
