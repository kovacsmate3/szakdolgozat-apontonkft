import * as React from "react";
import { contactFormSchema } from "@/lib/schemas";
import { z } from "zod";

interface AdminNotificationProps {
  emailFormData: z.infer<typeof contactFormSchema>;
}

const AdminNotification: React.FC<Readonly<AdminNotificationProps>> = ({
  emailFormData,
}) => (
  <div
    style={{
      fontFamily: "Arial, Helvetica, sans-serif",
      color: "#000000",
      maxWidth: "600px",
      margin: "0 auto",
      padding: "20px",
      backgroundColor: "#ffffff",
    }}
  >
    <div style={{ textAlign: "center", marginBottom: "30px" }}>
      <h2
        style={{
          color: "#000000",
          fontSize: "24px",
          fontWeight: "bold",
          textTransform: "uppercase",
        }}
      >
        Újabb megkeresés érkezett a weboldalról
      </h2>
    </div>

    <div style={{ marginBottom: "30px" }}>
      <p style={{ fontSize: "16px", lineHeight: "1.5", marginBottom: "12px" }}>
        <strong>Név:</strong> {emailFormData.lastName} {emailFormData.firstName}
      </p>
      <p style={{ fontSize: "16px", lineHeight: "1.5", marginBottom: "12px" }}>
        <strong>Email:</strong> {emailFormData.email}
      </p>
      {emailFormData.phone && (
        <p
          style={{ fontSize: "16px", lineHeight: "1.5", marginBottom: "12px" }}
        >
          <strong>Telefonszám:</strong> {emailFormData.phone}
        </p>
      )}
      <p style={{ fontSize: "16px", lineHeight: "1.5", marginBottom: "12px" }}>
        <strong>Megkeresés célja:</strong>{" "}
        {emailFormData.reason === "quotation"
          ? "Ajánlatkérés"
          : emailFormData.reason === "employment"
            ? "Álláslehetőség"
            : "Egyéb"}
      </p>

      <div
        style={{
          fontSize: "16px",
          lineHeight: "1.5",
          marginBottom: "12px",
          border: "1px solid #dddddd",
          padding: "15px",
          borderRadius: "5px",
          backgroundColor: "#f9f9f9",
        }}
      >
        <strong>Üzenet:</strong>
        <div style={{ whiteSpace: "pre-wrap", marginTop: "10px" }}>
          {emailFormData.message}
        </div>
      </div>

      {emailFormData.file && (
        <p
          style={{ fontSize: "16px", lineHeight: "1.6", marginBottom: "15px" }}
        >
          <strong>Fájl csatolva:</strong> {emailFormData.file.name} (
          {Math.round(emailFormData.file.size / 1024)} KB)
        </p>
      )}
    </div>

    <div
      style={{
        fontSize: "13px",
        color: "#666666",
        borderTop: "1px solid #dddddd",
        paddingTop: "20px",
        textAlign: "center",
      }}
    >
      <p>
        Ez az e-mail automatikusan lett kiküldve a weboldal kapcsolatfelvételi
        űrlapján keresztül.
      </p>
    </div>
  </div>
);

export default AdminNotification;
