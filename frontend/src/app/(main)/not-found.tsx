import Link from "next/link";

export default function NotFound() {
  return (
    <div className="text-center items-center my-68">
      <h1 className="text-9xl text-black dark:text-white font-extrabold mb-12">
        404
      </h1>
      <p className="text-3xl text-gray-600 dark:text-gray-400">
        A keresett oldal nem található.
      </p>
      <p className="text-3xl text-gray-600 dark:text-gray-400">
        Lépjen vissza a{" "}
        <Link
          href={"/home"}
          className="text-gray-700 dark:text-gray-300 font-semibold underline hover:text-gray-950 dark:hover:text-gray-50"
        >
          Kezdőlap
        </Link>
        {"ra"}
      </p>
    </div>
  );
}
