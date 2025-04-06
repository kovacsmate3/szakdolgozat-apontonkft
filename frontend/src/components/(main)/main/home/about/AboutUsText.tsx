import React from "react";

interface AboutUsTextProps {
  textSize?: string;
}

const AboutUsText: React.FC<AboutUsTextProps> = ({ textSize = "" }) => {
  return (
    <div className="mb-6">
      <p
        className={`text-justify mb-4 text-gray-900 dark:text-gray-200 ${textSize}`}
      >
        <strong>
          2003-ban megalakuló, budapesti székhelyű A-Ponton Kft. egy
          földméréssel foglalkozó magyar vállalkozás.
        </strong>{" "}
        Társaságunk alapítása óta az ország egyik meghatározó geodéziai
        vállalkozásává nőtte ki magát. Az utóbbi években kiegyensúlyozott
        működésünknek köszönhetően folyamatos gyarapodásnak indult cégünk, jól
        képzett és felkészült szakemberekkel bővült csapatunk. Törekedve a
        tökéletességre{" "}
        <strong>munkánkat precízen, határidőket betartva végezzük</strong>.
        Hatékonyságunk növelése érdekében{" "}
        <strong>
          nagy hangsúlyt fektetünk a folyamatos technikai és technológiai
          fejlesztésekre
        </strong>
        .
      </p>
    </div>
  );
};
export default AboutUsText;
