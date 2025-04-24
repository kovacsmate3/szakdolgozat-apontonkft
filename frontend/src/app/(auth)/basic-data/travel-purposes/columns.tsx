"use client";

import { TravelPurposeDictionary } from "@/lib/types";
import { ColumnDef } from "@tanstack/react-table";
import { Checkbox } from "@/components/ui/checkbox";
import { DataTableColumnHeader } from "@/components/data-table-column-header";
import { Badge } from "@/components/ui/badge";
import { DataTableRowActions } from "@/components/data-table-row-actions";

interface TravelPurposesColumnsProps {
  onEdit: (travelPurpose: TravelPurposeDictionary) => void;
  onDelete: (travelPurpose: TravelPurposeDictionary) => void;
  isAdmin: boolean;
  userId: number;
}

export const getTravelPurposesColumns = ({
  onEdit,
  onDelete,
  isAdmin,
  userId,
}: TravelPurposesColumnsProps): ColumnDef<TravelPurposeDictionary>[] => [
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
    accessorKey: "travel_purpose",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Utazási cél" />
    ),
    meta: {
      title: "Utazási cél",
    },
  },
  {
    accessorKey: "type",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Típus" />
    ),
    cell: ({ row }) => {
      const type = row.getValue("type");
      return <Badge variant="secondary">{type as string}</Badge>;
    },
    meta: {
      title: "Típus",
    },
  },
  {
    accessorKey: "note",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Megjegyzés" />
    ),
    cell: ({ row }) => {
      const note = row.getValue("note");
      return <span>{note ? (note as string) : "—"}</span>;
    },
    meta: {
      title: "Megjegyzés",
    },
  },
  {
    accessorKey: "is_system",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Rendszerszintű" />
    ),
    cell: ({ row }) => {
      const isSystem = row.getValue("is_system");
      return (
        <Badge variant={isSystem ? "default" : "outline"}>
          {isSystem ? "Igen" : "Nem"}
        </Badge>
      );
    },
    meta: {
      title: "Rendszerszintű",
    },
    enableSorting: true,
  },
  {
    id: "actions",
    enableHiding: false,
    cell: ({ row }) => {
      const travelPurpose = row.original;

      // Jogosultság ellenőrzés a műveleti gombok megjelenítéséhez
      const isOwnRecord = travelPurpose.user_id === userId;
      const canManage = isAdmin || (!travelPurpose.is_system && isOwnRecord);

      // Ha a felhasználó nem kezelheti ezt a rekordot, ne jelenjenek meg a gombok
      if (!canManage) return null;

      return (
        <DataTableRowActions row={row} onEdit={onEdit} onDelete={onDelete} />
      );
    },
  },
];
