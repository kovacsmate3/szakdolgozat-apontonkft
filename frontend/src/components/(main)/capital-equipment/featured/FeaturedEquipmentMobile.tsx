import { EquipmentProps } from "@/lib/types";
import EquipmentCard from "../EquipmentCard";

const FeaturedEquipmentMobile = ({ items }: EquipmentProps) => {
  return (
    <div className="lg:hidden space-y-6">
      <EquipmentCard {...items[0]} className="max-w-md mx-auto" />

      <div className="text-white bg-black/90 p-5 rounded-lg max-w-md mx-auto">
        <p className="text-base text-justify">
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

      <EquipmentCard {...items[1]} className="max-w-md mx-auto" />
    </div>
  );
};
export default FeaturedEquipmentMobile;
