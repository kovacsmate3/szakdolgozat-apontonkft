import { Law, LawCategories } from "@/lib/types";

export async function getLawsByCategory(
  categoryId: number,
  token: string
): Promise<Law[]> {
  const res = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/laws?category_id=${categoryId}`,
    {
      headers: {
        Authorization: `Bearer ${token}`,
        "Content-Type": "application/json",
      },
    }
  );

  if (!res.ok) {
    throw new Error(
      `A(z) ${categoryId} kategóriához tartozó törvények lekérése sikertelen.`
    );
  }

  return res.json();
}

export async function getLandMeasurementLaws(token: string): Promise<Law[]> {
  return getLawsByCategory(LawCategories.LAND_MEASUREMENT, token);
}

export async function getPropertyRegistryLaws(token: string): Promise<Law[]> {
  return getLawsByCategory(LawCategories.PROPERTY_REGISTRY, token);
}

export async function getConstructionLaws(token: string): Promise<Law[]> {
  return getLawsByCategory(LawCategories.CONSTRUCTION, token);
}

export async function getLandAffairsLaws(token: string): Promise<Law[]> {
  return getLawsByCategory(LawCategories.LAND_AFFAIRS, token);
}

export async function getFeesLaws(token: string): Promise<Law[]> {
  return getLawsByCategory(LawCategories.FEES, token);
}

export async function getOtherLaws(token: string): Promise<Law[]> {
  return getLawsByCategory(LawCategories.OTHER_LAWS, token);
}

// Function to get a single law by ID
export async function getLawById(id: number, token: string): Promise<Law> {
  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/laws/${id}`, {
    headers: {
      Authorization: `Bearer ${token}`,
      "Content-Type": "application/json",
    },
  });

  if (!res.ok) {
    throw new Error(`A(z) ${id} azonosítójú törvény lekérése sikertelen.`);
  }

  return res.json();
}
