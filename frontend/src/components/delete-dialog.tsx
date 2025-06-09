"use client";

import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";

interface DeleteDialogProps {
  isOpen: boolean;
  onOpenChange: (open: boolean) => void;
  onConfirm: () => void;
  description?: string;
  title?: string;
  confirmText?: string;
  cancelText?: string;
}

export function DeleteDialog({
  isOpen,
  onOpenChange,
  onConfirm,
  description = "Ez a művelet nem visszavonható.",
  title = "Biztosan törölni szeretnéd?",
  confirmText = "Törlés",
  cancelText = "Mégsem",
}: DeleteDialogProps) {
  const handleOpenChange = (open: boolean) => {
    // If the dialog is closing
    if (!open) {
      // Reset pointer events after a short delay
      setTimeout(() => {
        document.body.style.pointerEvents = "";
      }, 100);
    }

    // Call the original handler
    onOpenChange(open);
  };

  const handleConfirm = () => {
    // Reset pointer events after a short delay
    setTimeout(() => {
      document.body.style.pointerEvents = "";
    }, 100);

    // Call the original confirm handler
    onConfirm();
  };

  return (
    <AlertDialog open={isOpen} onOpenChange={handleOpenChange}>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>{title}</AlertDialogTitle>
          <AlertDialogDescription>{description}</AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>{cancelText}</AlertDialogCancel>
          <AlertDialogAction
            onClick={handleConfirm}
            className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
          >
            {confirmText}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
}
