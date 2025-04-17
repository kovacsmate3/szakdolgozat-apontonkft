"use client";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Textarea } from "@/components/ui/textarea";
import { Checkbox } from "@/components/ui/checkbox";
import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import {
  Form,
  FormControl,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { CheckCircle, AlertCircle, Loader2 } from "lucide-react";

import { z } from "zod";
import { contactFormSchema } from "@/lib/schemas";
import { send } from "@/lib/email";
import { useState } from "react";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";

function fileToBase64(file: File): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result as string);
    reader.onerror = reject;
    reader.readAsDataURL(file);
  });
}

export default function ContactForm() {
  const [submitStatus, setSubmitStatus] = useState({
    loading: false,
    success: false,
    error: false,
    message: "",
  });

  const form = useForm<z.infer<typeof contactFormSchema>>({
    resolver: zodResolver(contactFormSchema),
    defaultValues: {
      firstName: "",
      lastName: "",
      email: "",
      phone: "",
      reason: "quotation",
      message: "",
      robot: false,
      file: undefined,
    },
  });

  const watchedFile = form.watch("file");

  async function onSubmit(values: z.infer<typeof contactFormSchema>) {
    let base64File: string | undefined;
    if (values.file) {
      base64File = await fileToBase64(values.file);
    }

    try {
      setSubmitStatus({
        loading: true,
        success: false,
        error: false,
        message: "",
      });

      await send({
        ...values,
        base64File,
        fileName: values.file?.name,
      });

      setSubmitStatus({
        loading: false,
        success: true,
        error: false,
        message:
          "Köszönjük!\n Üzenetét sikeresen elküldtük. Hamarosan felvesszük Önnel a kapcsolatot.",
      });

      form.reset();
    } catch {
      setSubmitStatus({
        loading: false,
        success: false,
        error: true,
        message:
          "Sajnáljuk, de az üzenet küldése sikertelen volt. Kérjük, próbálja újra később.",
      });
    }
  }

  return (
    <Card className="w-full max-w-2xl mx-auto">
      <CardHeader>
        <CardTitle>Kapcsolatfelvétel</CardTitle>
        <CardDescription>
          Kérjük, töltse ki az alábbi űrlapot, és hamarosan felvesszük Önnel a
          kapcsolatot.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <AlertDialog open={submitStatus.success}>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle className="flex items-center">
                <CheckCircle className="h-5 w-5 text-green-500 mr-2" />
                Sikeres küldés!
              </AlertDialogTitle>
              <AlertDialogDescription>
                {submitStatus.message}
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogAction
                onClick={() =>
                  setSubmitStatus((prev) => ({ ...prev, success: false }))
                }
              >
                Rendben
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>

        <AlertDialog open={submitStatus.error}>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle className="flex items-center">
                <AlertCircle className="h-5 w-5 text-red-500 mr-2" />
                Hiba történt!
              </AlertDialogTitle>
              <AlertDialogDescription>
                {submitStatus.message}
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogAction
                onClick={() =>
                  setSubmitStatus((prev) => ({ ...prev, error: false }))
                }
              >
                Rendben
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>

        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <FormField
                  control={form.control}
                  name="lastName"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel htmlFor="lastName">Vezetéknév</FormLabel>
                      <FormControl>
                        <Input
                          id="lastName"
                          type="text"
                          placeholder="Az Ön vezetékneve..."
                          {...field}
                        />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>
              <div className="space-y-2">
                <FormField
                  control={form.control}
                  name="firstName"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel htmlFor="firstName">Keresztnév</FormLabel>
                      <FormControl>
                        <Input
                          id="firstName"
                          type="text"
                          placeholder="Az Ön keresztneve..."
                          {...field}
                        />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <FormField
                  control={form.control}
                  name="email"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel htmlFor="email">E-mail</FormLabel>
                      <FormControl>
                        <Input
                          id="email"
                          type="email"
                          placeholder="Az Ön érvényes e-mail címe..."
                          {...field}
                        />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>
              <div className="space-y-2">
                <FormField
                  control={form.control}
                  name="phone"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel htmlFor="phone">Telefonszám</FormLabel>
                      <FormControl>
                        <Input
                          id="phone"
                          type="tel"
                          placeholder="Az Ön telefonszáma..."
                          {...field}
                        />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>
            </div>

            <div className="space-y-2">
              <FormField
                control={form.control}
                name="reason"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel htmlFor="reason">Megkeresés célja</FormLabel>
                    <FormControl>
                      <RadioGroup
                        onValueChange={field.onChange}
                        defaultValue={field.value}
                        className="flex flex-col space-y-1"
                      >
                        <FormItem className="flex items-center space-x-2 space-y-0">
                          <FormControl className="cursor-pointer">
                            <RadioGroupItem value="quotation" id="quotation" />
                          </FormControl>
                          <FormLabel
                            htmlFor="quotation"
                            className="font-normal"
                          >
                            Ajánlatkérés
                          </FormLabel>
                        </FormItem>

                        <FormItem className="flex items-center space-x-2 space-y-0">
                          <FormControl className="cursor-pointer">
                            <RadioGroupItem
                              value="employment"
                              id="employment"
                            />
                          </FormControl>
                          <FormLabel
                            htmlFor="employment"
                            className="font-normal"
                          >
                            Állás lehetőség
                          </FormLabel>
                        </FormItem>

                        <FormItem className="flex items-center space-x-2 space-y-0">
                          <FormControl className="cursor-pointer">
                            <RadioGroupItem value="other" id="other" />
                          </FormControl>
                          <FormLabel htmlFor="other" className="font-normal">
                            Egyéb
                          </FormLabel>
                        </FormItem>
                      </RadioGroup>
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            <div className="space-y-2">
              <FormField
                control={form.control}
                name="message"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel htmlFor="message">Üzenet</FormLabel>
                    <FormControl>
                      <Textarea
                        id="message"
                        placeholder="Kérjük, részletezze megkeresésének okát..."
                        className="min-h-[120px]"
                        {...field}
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            <div className="space-y-2">
              <FormField
                control={form.control}
                name="file"
                render={({ field: { onChange, ref, name } }) => (
                  <FormItem>
                    <FormLabel htmlFor="file">Fájl feltöltése</FormLabel>
                    <FormControl>
                      <Input
                        id="file"
                        type="file"
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                        name={name}
                        ref={ref}
                        onChange={(e) => onChange(e.target.files?.[0])}
                        className="cursor-pointer"
                      />
                    </FormControl>
                    {watchedFile && (
                      <FormDescription className="text-sm text-muted-foreground mt-1">
                        Kiválasztott fájl: {watchedFile.name}
                      </FormDescription>
                    )}
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            <div className="flex items-center space-x-2 space-y-2">
              <FormField
                control={form.control}
                name="robot"
                render={({ field }) => (
                  <FormItem className="flex items-center space-x-2">
                    <FormControl>
                      <Checkbox
                        id="robot"
                        checked={field.value}
                        onCheckedChange={field.onChange}
                        className="cursor-pointer"
                      />
                    </FormControl>
                    <FormLabel
                      htmlFor="robot"
                      className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                    >
                      Nem vagyok robot
                    </FormLabel>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>
            <Button
              type="submit"
              className="ml-auto cursor-pointer"
              disabled={submitStatus.loading}
            >
              {submitStatus.loading ? (
                <>
                  <Loader2 className="mr-2 h-4 w-4 animate-spin" /> Küldés
                  folyamatban...
                </>
              ) : (
                "Küldés"
              )}
            </Button>
          </form>
        </Form>
      </CardContent>
    </Card>
  );
}
