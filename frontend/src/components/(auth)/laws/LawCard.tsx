import React from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import Link from "next/link";
import { Law } from "@/lib/types";

interface LawCardProps {
  law: Law;
}

export function LawCard({ law }: LawCardProps) {
  return (
    <Card className="flex flex-col">
      {/* Fix magasságú header, title line‐clamp‐pel */}
      <CardHeader className="h-18 overflow-hidden">
        <CardTitle
          className="text-base
                       overflow-hidden
                       [display:-webkit-box]
                       [-webkit-box-orient:vertical]
                       [-webkit-line-clamp:2]"
        >
          {law.title}
        </CardTitle>
      </CardHeader>

      {/* A content marad ugyanígy, innentől minden kártya ugyanott kezdődik */}
      <CardContent className="mt-auto space-y-2">
        <p className="text-sm text-muted-foreground h-5">
          {law.official_ref || "\u00A0"}
        </p>
        <p className="text-xs text-muted-foreground h-4">
          {law.date_of_enactment
            ? `Hatálybalépés: ${law.date_of_enactment}`
            : "\u2013"}
        </p>
        <div className="flex justify-end">
          <Button variant="link" size="sm" asChild>
            <Link href={law.link} target="_blank" rel="noopener noreferrer">
              Megnyitás
            </Link>
          </Button>
        </div>
      </CardContent>
    </Card>
  );
}
