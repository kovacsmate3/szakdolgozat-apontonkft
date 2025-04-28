export type PathObject = {
  [key: string]: string;
};

export const protectedRoutes = [
  "/dashboard",
  //"/timesheet",
  "/road-record",
  "/basic-data",
  "/laws",
  "/admin",
];

export const adminRoutes = ["/admin"];
export const validRoutes = [
  "/home",
  "/references",
  "/capital-equipment",
  "/contact",
  "/login",
  "/dashboard",
  "/road-record",
  "/road-record/monthly-trips",
  "/road-record/refueling",
  "/road-record/route-planning",
  "/basic-data",
  "/basic-data/cars",
  "/basic-data/fuel-prices",
  "/basic-data/partners",
  "/basic-data/sites",
  "/basic-data/stations",
  "/basic-data/travel-purposes",
  "/laws",
  "/laws/construction",
  "/laws/fees",
  "/laws/land-affairs",
  "/laws/land-measurement",
  "/laws/other",
  "/laws/property",
  "/profile",
  "/admin",
  "/admin/users",
];

export const mainRoutes: PathObject = {
  "/dashboard": "/dashboard",
  "/road-record": "/road-record",
  "/basic-data": "/basic-data",
  "/laws": "/laws",
  "/admin": "/admin",
};
