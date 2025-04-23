"use client";

import { FuelPrice } from "@/lib/types";
import { ColumnDef } from "@tanstack/react-table";
import { Checkbox } from "@/components/ui/checkbox";
import { DataTableColumnHeader } from "@/components/data-table-column-header";
import { formatHUF } from "@/lib/functions";
import { DataTableRowActions } from "@/components/data-table-row-actions";

interface FuelPricesColumnsProps {
  onEdit: (fuelPrice: FuelPrice) => void;
  onDelete: (fuelPrice: FuelPrice) => void;
  isAdmin: boolean;
}

export const getFuelPricesColumns = ({
  onEdit,
  onDelete,
  isAdmin,
}: FuelPricesColumnsProps): ColumnDef<FuelPrice>[] => [
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
        disabled={!isAdmin}
      />
    ),
    cell: ({ row }) => (
      <Checkbox
        checked={row.getIsSelected()}
        onCheckedChange={(value) => row.toggleSelected(!!value)}
        aria-label="Sor kiválasztása"
        disabled={!isAdmin}
      />
    ),
    enableSorting: false,
    enableHiding: false,
  },
  {
    accessorKey: "period",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Időszak" />
    ),
    cell: ({ row }) => {
      const period = row.getValue("period") as string;
      const formatted = new Date(period).toLocaleDateString("hu-HU", {
        year: "numeric",
        month: "long",
      });
      return <div>{formatted}</div>;
    },
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
    meta: {
      title: "Időszak",
    },
  },
  {
    accessorKey: "petrol",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Benzin" />
    ),
    cell: ({ row }) => {
      const petrol = row.getValue("petrol");
      return <div>{formatHUF(petrol)}</div>;
    },
    enableSorting: false,
    meta: {
      title: "Benzin",
    },
  },
  {
    accessorKey: "mixture",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Keverék" />
    ),
    cell: ({ row }) => {
      const mixture = parseFloat(row.getValue("mixture"));
      return <div>{formatHUF(mixture)}</div>;
    },
    enableSorting: false,
    meta: {
      title: "Keverék",
    },
  },
  {
    accessorKey: "diesel",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Gázolaj" />
    ),
    cell: ({ row }) => {
      const diesel = parseFloat(row.getValue("diesel"));
      return <div>{formatHUF(diesel)}</div>;
    },
    enableSorting: false,
    meta: {
      title: "Gázolaj",
    },
  },
  {
    accessorKey: "lp_gas",
    header: "LPG autógáz",
    cell: ({ row }) => {
      const lp_gas = parseFloat(row.getValue("lp_gas"));
      return <div>{formatHUF(lp_gas)}</div>;
    },
    enableSorting: false,
    meta: {
      title: "LPG autógáz",
    },
  },
  ...(isAdmin
    ? [
        {
          id: "actions",
          enableHiding: false,
          cell: ({ row }) => (
            <DataTableRowActions
              row={row}
              onEdit={onEdit}
              onDelete={onDelete}
            />
          ),
        } as ColumnDef<FuelPrice>,
      ]
    : []),
];
