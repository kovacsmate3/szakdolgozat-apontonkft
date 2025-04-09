import * as React from "react";

export const EmailTemplate: React.FC<{ firstName: string }> = ({
  firstName,
}) => (
  <div>
    <h1>Üdvözöljük, {firstName}!</h1>
  </div>
);
