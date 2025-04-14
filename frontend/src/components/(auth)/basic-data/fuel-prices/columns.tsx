"use client";

import { FuelPrice } from "@/lib/types";
import { ColumnDef } from "@tanstack/react-table";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { ArrowUpDown, MoreHorizontal, SquarePen, Trash2 } from "lucide-react";

export const columns: ColumnDef<FuelPrice>[] = [
  {
    id: "select",
    header: ({ table }) => (
      <Checkbox
        checked={
          table.getIsAllPageRowsSelected() ||
          (table.getIsSomePageRowsSelected() && "indeterminate")
        }
        onCheckedChange={(value) => table.toggleAllPageRowsSelected(!!value)}
        aria-label="Összes kiválasztása"
      />
    ),
    cell: ({ row }) => (
      <Checkbox
        checked={row.getIsSelected()}
        onCheckedChange={(value) => row.toggleSelected(!!value)}
        aria-label="Sor kiválasztása"
      />
    ),
    enableSorting: false,
    enableHiding: false,
  },
  {
    filterFn: (row, columnId, filterValue) => {
      if (!filterValue) return true;

      const rawValue = row.getValue(columnId) as string;
      const date = new Date(rawValue);
      const formatted = date.toLocaleDateString("hu-HU", {
        year: "numeric",
        month: "long",
      });

      return formatted
        .toLowerCase()
        .includes(String(filterValue).toLowerCase());
    },
    enableColumnFilter: true,
    accessorKey: "period",
    header: ({ column }) => (
      <Button
        variant="ghost"
        onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
      >
        Időszak
        <ArrowUpDown className="ml-2 h-4 w-4" />
      </Button>
    ),
    cell: ({ row }) => {
      const period = row.getValue("period") as string;
      const formatted = new Date(period).toLocaleDateString("hu-HU", {
        year: "numeric",
        month: "long",
      });
      return <div>{formatted}</div>;
    },
  },
  {
    accessorKey: "petrol",
    header: "Benzin",
    cell: ({ row }) => {
      const amount = parseFloat(row.getValue("petrol"));
      const formatted = new Intl.NumberFormat("hu-HU", {
        style: "decimal",
        maximumFractionDigits: 0,
        minimumFractionDigits: 0,
      }).format(amount);
      return <div>{formatted} Ft</div>;
    },
  },
  {
    accessorKey: "mixture",
    header: "Keverék",
    cell: ({ row }) => {
      const amount = parseFloat(row.getValue("mixture"));
      const formatted = new Intl.NumberFormat("hu-HU", {
        style: "decimal",
        maximumFractionDigits: 0,
        minimumFractionDigits: 0,
      }).format(amount);
      return <div>{formatted} Ft</div>;
    },
  },
  {
    accessorKey: "diesel",
    header: "Dízel",
    cell: ({ row }) => {
      const amount = parseFloat(row.getValue("diesel"));
      const formatted = new Intl.NumberFormat("hu-HU", {
        style: "decimal",
        maximumFractionDigits: 0,
        minimumFractionDigits: 0,
      }).format(amount);
      return <div>{formatted} Ft</div>;
    },
  },
  {
    accessorKey: "lp_gas",
    header: "LPG autógáz",
    cell: ({ row }) => {
      const amount = parseFloat(row.getValue("lp_gas"));
      const formatted = new Intl.NumberFormat("hu-HU", {
        style: "decimal",
        maximumFractionDigits: 0,
        minimumFractionDigits: 0,
      }).format(amount);
      return <div>{formatted} Ft</div>;
    },
  },
  {
    id: "actions",
    enableHiding: false,
    cell: () => {
      return (
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <Button variant="ghost" className="h-8 w-8 p-0">
              <span className="sr-only">Menü megnyitása</span>
              <MoreHorizontal className="h-4 w-4" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
            <DropdownMenuLabel>Műveletek</DropdownMenuLabel>
            <DropdownMenuItem>
              <SquarePen className="text-muted-foreground" />
              <span>Szerkesztés</span>
            </DropdownMenuItem>
            <DropdownMenuItem>
              <Trash2 className="text-muted-foreground" />
              <span>Törlés</span>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      );
    },
  },
];
