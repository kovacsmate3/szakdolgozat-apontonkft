import { EquipmentProps } from "@/lib/types";
import EquipmentCard from "../EquipmentCard";

const FeaturedEquipmentDesktop = ({ items }: EquipmentProps) => {
  return (
    <div className="hidden lg:flex justify-between items-stretch gap-4">
      <div className="w-1/3">
        <EquipmentCard {...items[0]} className="h-full" />
      </div>

      <div className="w-1/3 bg-black/90 rounded-lg p-5 flex items-center">
        <p className="text-white text-sm xl:text-base text-justify">
          Rendkívül gyors (ezáltal költséghatékony){" "}
          <b>
            3D-s lézerszkennerünkkel részletes, pontos geometriai és képi
            információkat rögzíthetünk épületekről
          </b>
          , műemlékekről. A{" "}
          <span lang="en">
            <b>Trimble</b> RealWorks
          </span>{" "}
          és a <span lang="en">Trimble Perspective</span>{" "}
          <b>
            szoftvereink segítségével végezzük a szkennerrel készült pontfelhők
            feldolgozását
          </b>
          .<br />
          <br />
          <b>A világ első öntanuló mérőállomásának működtetésével</b> bármilyen
          terepen - miképpen a műszer a környezeti feltételekhez automatikusan
          igazodik - <b>pontosan rögzíthetjük a környezetet</b>.{" "}
          <b>Az ehhez tartozó</b>{" "}
          <span lang="en">
            <b>Leica</b> Captivate
          </span>{" "}
          <b>
            szoftver egyetlen húzással kapcsolja össze a különféle szakterületek
            alkalmazásait és legbonyolultabb műveleteit
          </b>
          , továbbá a mért és a tervezett adatokat minden dimenzióban
          megtekinthetővé teszi számunkra.
        </p>
      </div>

      <div className="w-1/3">
        <EquipmentCard {...items[1]} className="h-full" />
      </div>
    </div>
  );
};
export default FeaturedEquipmentDesktop;
