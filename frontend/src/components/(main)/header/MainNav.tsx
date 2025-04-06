import Link from "next/link";

const MainNav = () => {
  return (
    <>
      <nav className="flex items-center gap-4 md:text-lg lg:text-xl">
        <Link href="/home">Kezdőlap</Link>
        <Link href="/references">Referenciáink</Link>
        <Link href="/capital-equipment">Munkaeszközeink</Link>
        <Link href="/contact">Kapcsolat</Link>
      </nav>
    </>
  );
};
export default MainNav;
