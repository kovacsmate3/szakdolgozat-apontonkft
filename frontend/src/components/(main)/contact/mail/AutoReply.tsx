import * as React from "react";

interface AutoReplyProps {
  firstName: string;
  lastName: string;
}

export const AutoReply: React.FC<Readonly<AutoReplyProps>> = ({
  firstName,
  lastName,
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
          fontSize: "28px",
          fontWeight: "bold",
          textTransform: "uppercase",
          fontFamily: "Arial, Helvetica, sans-serif",
        }}
      >
        A-Ponton Mérnökiroda Kft.
      </h2>
    </div>

    <div style={{ marginBottom: "30px" }}>
      <h1
        style={{
          color: "#000000",
          fontSize: "22px",
          fontWeight: "bold",
          marginBottom: "15px",
          fontFamily: "Arial, Helvetica, sans-serif",
        }}
      >
        Köszönjük megkeresését!
      </h1>

      <p
        style={{
          fontSize: "16px",
          lineHeight: "1.6",
          marginBottom: "15px",
          textAlign: "justify",
          fontFamily: "Arial, Helvetica, sans-serif",
        }}
      >
        Tisztelt {lastName} {firstName}!
      </p>

      <p
        style={{
          fontSize: "16px",
          lineHeight: "1.6",
          marginBottom: "15px",
          textAlign: "justify",
          fontFamily: "Arial, Helvetica, sans-serif",
        }}
      >
        Ezúton szeretnénk tájékoztatni, hogy megkeresését sikeresen rögzítettük
        rendszerünkben. Kollégáink a lehető leghamarabb feldolgozzák kérését és
        felveszik Önnel a kapcsolatot.
      </p>

      <p
        style={{
          fontSize: "16px",
          lineHeight: "1.6",
          marginBottom: "15px",
          textAlign: "justify",
          fontFamily: "Arial, Helvetica, sans-serif",
        }}
      >
        Általános ügyintézési időnk 1–2 munkanap, ezen időn belül személyes
        választ fog kapni tőlünk.
      </p>
    </div>

    <div
      style={{
        backgroundColor: "#f3f3f3",
        padding: "15px",
        borderRadius: "5px",
        marginBottom: "30px",
        fontFamily: "Arial, Helvetica, sans-serif",
      }}
    >
      <h2
        style={{
          color: "#000000",
          fontSize: "18px",
          fontWeight: "bold",
          marginBottom: "10px",
          fontFamily: "Arial, Helvetica, sans-serif",
        }}
      >
        Kapcsolat
      </h2>
      <p
        style={{
          fontSize: "14px",
          marginBottom: "5px",
          fontFamily: "Arial, Helvetica, sans-serif",
        }}
      >
        <strong>Telefon:</strong> +36 20 927 0324
      </p>
      <p
        style={{
          fontSize: "14px",
          marginBottom: "5px",
          fontFamily: "Arial, Helvetica, sans-serif",
        }}
      >
        <strong>Email:</strong>{" "}
        <a
          href="mailto:aponton@t-online.hu"
          style={{
            color: "#000000",
            textDecoration: "underline",
            fontFamily: "Arial, Helvetica, sans-serif",
          }}
        >
          aponton@t-online.hu
        </a>
      </p>
      <p
        style={{ fontSize: "14px", fontFamily: "Arial, Helvetica, sans-serif" }}
      >
        <strong>Cím:</strong> 1151 Budapest, Esthajnal utca 3.
      </p>
    </div>

    <div
      style={{
        fontSize: "13px",
        color: "#666666",
        borderTop: "1px solid #dddddd",
        paddingTop: "20px",
        textAlign: "center",
        fontFamily: "Arial, Helvetica, sans-serif",
      }}
    >
      <p
        style={{
          marginBottom: "10px",
          fontFamily: "Arial, Helvetica, sans-serif",
        }}
      >
        © {new Date().getFullYear()} A-Ponton Mérnökiroda Kft. Minden jog
        fenntartva.
      </p>
      <p
        style={{ fontSize: "12px", fontFamily: "Arial, Helvetica, sans-serif" }}
      >
        Ez az e-mail automatikusan lett kiküldve, kérjük, ne válaszoljon rá.
      </p>
    </div>
  </div>
);

export default AutoReply;
