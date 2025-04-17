"use server";

import { Resend } from "resend";
import { z } from "zod";
import { contactFormSchema } from "./schemas";
import AutoReply from "@/components/(main)/contact/mail/AutoReply";
import AdminNotification from "@/components/(main)/contact/mail/AdminNotification";

const resend = new Resend(process.env.RESEND_API_KEY);

export const send = async (
  emailFormData: z.infer<typeof contactFormSchema>
) => {
  try {
    const autoReplyResult = await resend.emails.send({
      from: `A-Ponton Mérnökiroda Kft. <${process.env.RESEND_FROM_EMAIL}>`,
      to: [emailFormData.email],
      subject: "Köszönjük megkeresését",
      react: AutoReply({
        firstName: emailFormData.firstName,
        lastName: emailFormData.lastName,
      }) as React.ReactNode,
    });

    if (autoReplyResult.error) {
      throw autoReplyResult.error;
    }

    const reasonText =
      emailFormData.reason === "quotation"
        ? "Ajánlatkérés"
        : emailFormData.reason === "employment"
          ? "Álláslehetőség"
          : "Egyéb";

    const textVersion = `
    Új űrlapkitöltés érkezett a weboldalról:
          
    Név: ${emailFormData.lastName} ${emailFormData.firstName}
    Email: ${emailFormData.email}
    ${emailFormData.phone ? `Telefonszám: ${emailFormData.phone}` : ""}
    Megkeresés célja: ${reasonText}
          
    Üzenet:
    ${emailFormData.message}
          
    ${emailFormData.file ? `Fájl csatolva: ${emailFormData.file.name}` : ""}
        `;

    const adminEmailOptions: {
      from: string;
      to: string[];
      subject: string;
      react: React.ReactNode;
      text: string;
      attachments?: { filename: string; content: Buffer }[];
    } = {
      from: `Webes űrlap <${process.env.RESEND_FROM_EMAIL}>`,
      to: ["aponton.ks@gmail.com"],
      subject: `Új megkeresés: ${emailFormData.lastName} ${emailFormData.firstName} - ${reasonText}`,
      react: AdminNotification({ emailFormData }) as React.ReactNode,
      text: textVersion,
    };

    if (emailFormData.base64File && emailFormData.fileName) {
      const base64Content =
        emailFormData.base64File.split(";base64,").pop() || "";
      const buffer = Buffer.from(base64Content, "base64");

      adminEmailOptions.attachments = [
        {
          filename: emailFormData.fileName,
          content: buffer,
        },
      ];
    }

    const adminNotifyResult = await resend.emails.send(adminEmailOptions);

    if (adminNotifyResult.error) {
      throw adminNotifyResult.error;
    }

    return { success: true };
  } catch (e) {
    console.error("Email küldési hiba:", e);
    throw e;
  }
};
