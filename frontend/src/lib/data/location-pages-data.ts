import { publicSpaceTypes as rawTypes } from "../../lib/constants";
export const publicSpaceTypes = Array.from(new Set(rawTypes)).sort();
