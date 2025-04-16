"use client";

import { UserData } from "@/lib/types";
import { ColumnDef } from "@tanstack/react-table";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { MoreHorizontal, SquarePen, Trash2 } from "lucide-react";
import { MdContentCopy } from "react-icons/md";
import { DataTableColumnHeader } from "@/components/data-table-column-header";
import { Badge } from "@/components/ui/badge";
import { format, parseISO } from "date-fns";

export const columns: ColumnDef<UserData>[] = [
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
  },
  {
    accessorKey: "lastname",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Vezetéknév" />
    ),
    meta: {
      title: "Vezetéknév",
    },
  },
  {
    accessorKey: "firstname",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Keresztnév" />
    ),
    meta: {
      title: "Keresztnév",
    },
  },
  {
    accessorKey: "birthdate",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Születési dátum" />
    ),
    cell: ({ row }) => {
      const birthdate = row.getValue("birthdate") as string;
      const formatted = format(parseISO(birthdate), "yyyy-MM-dd");
      return <span>{formatted}</span>;
    },
    enableSorting: false,
    meta: {
      title: "Születési dátum",
    },
  },
  {
    accessorKey: "email",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="E-mail cím" />
    ),
    cell: ({ row }) => <div className="lowercase">{row.getValue("email")}</div>,
    meta: {
      title: "E-mail cím",
    },
  },
  {
    accessorKey: "phonenumber",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Telefonszám" />
    ),
    enableSorting: false,
    meta: {
      title: "Telefonszám",
    },
  },
  {
    accessorKey: "role",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Szerepkör" />
    ),
    cell: ({ row }) => {
      const role = row.original.role;
      return <Badge variant="secondary">{role?.title ?? "—"}</Badge>;
    },
    enableSorting: false,
    meta: {
      title: "Szerepkör",
    },
  },
  {
    id: "actions",
    enableHiding: false,
    cell: ({ row }) => {
      const user = row.original;
      return (
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <Button variant="ghost" className="h-8 w-8 p-0">
              <span className="sr-only">Menü megnyitása</span>
              <MoreHorizontal className="h-4 w-4" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
            <DropdownMenuLabel>Felhasználói műveletek</DropdownMenuLabel>
            <DropdownMenuItem
              onClick={() => navigator.clipboard.writeText(user.email)}
            >
              <MdContentCopy className="text-muted-foreground" />
              <span>Email másolása</span>
            </DropdownMenuItem>
            <DropdownMenuSeparator />
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
