import { Search } from "lucide-react";

import { Label } from "@/components/ui/label";
import {
  SidebarGroup,
  SidebarGroupContent,
  SidebarInput,
} from "@/components/ui/sidebar";

export interface SearchFormProps extends React.ComponentProps<"form"> {
  /** called on each change with the current query */
  onSearch?: (query: string) => void;
}

export function SearchForm({ onSearch, ...props }: SearchFormProps) {
  return (
    <form {...props} onSubmit={(e) => e.preventDefault()}>
      <SidebarGroup className="py-0">
        <SidebarGroupContent className="relative">
          <Label htmlFor="search" className="sr-only">
            Keresés
          </Label>
          <SidebarInput
            id="search"
            placeholder="Keresés..."
            className="pl-8"
            onChange={(e) => onSearch?.(e.currentTarget.value)}
          />
          <Search className="pointer-events-none absolute top-1/2 left-2 size-4 -translate-y-1/2 opacity-50 select-none" />
        </SidebarGroupContent>
      </SidebarGroup>
    </form>
  );
}
