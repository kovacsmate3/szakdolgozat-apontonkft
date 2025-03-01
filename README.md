ELTE Informatikai Kar - Programtervező informatikus BSc - Szoftverfejlesztő (C) szakirány
# Diplomamunka (A-Ponton Kft. weboldala)

## Adatbázis
A megfelelő adatbázis táblák és modellek implementálása az alábbiak szerint (*az alapvető mezők - id, created_at, updated_at - minden táblában szerepelnek*):
### Modellek
* **User**
  -> ez a Laravel alapértelmezett táblája, amihez a következő mezőket kell pluszban felvenni:   
    * firstname [varchar(50)] – a felhasználó keresztneve
    * lastname [varchar(50)] – a felhasználó vezetékneve
    * birthdate [date] – születési dátum
    * phonenumber [varchar(30)] – telefonszám
    * 2FA mezők: two_factor_secret [text], two_factor_recovery_codes [json/text], two_factor_confirmed_at [datetime]
    * password_changed_at [datetime] – jelszó megváltoztatásának ideje
* **Role** - a felhasználó szerepe
  * name [varchar(100)] - a szerep neve (pl. admin, )
  * description [text] - a szerep leírása (nullable)
* **Permission** - az egyes szerepekhez kapcsolódó engedély, jogosultság
  * name [varchar(100)] - a jogosultság megnevezése (pl. )
  * category [varchar(100)] - jogosultsági kategória
  * description [text] - a jogosultság részletes leírása (nullable)
* **Law** - egy konkrét jogszabály
  * title [varchar(255)] - a jogszabály címe
  * official_ref [varchar(255)] - a jogszabály hivatalos azonosítója
  * date_of_enactment [date] - a jogszabály hatályba lépésének dátuma
  * is_active [tinyint(1)] – jelzi, hogy a jogszabály jelenleg érvényes-e
  * attachments [json] – további fájlok, linkek, vagy dokumentumok csatolására szolgál
* **LawCategory** - a jogszabályok csoportosítására szolgál (minden jogszabály egy adott kategóriához tartozik)
  * name [varchar(100)] - a jogszabálytípus neve
  * description [text] - a jogszabály típusának részletesebb leírása
* **Car** - a cég autóit realizálja
  * car_type [varchar(30)] - az autó típusa
  * license_plate [varchar(20)] -a jármű rendszáma
  * manufacturer [varchar(100)] - az autó gyártója (pl. Ford, Toyota)
  * fuel_type [varchar(50)] - az autó üzemanyagtípusa (pl. benzin, dízel, LPG-gáz)
  * standard_consumption [float] - az autó átlagos üzemanyag-fogyasztása (l/100km)
  * capacity [int] - a motor hengerűrtartalma (cm³)
  * fuel_tank_capacity [float] – az üzemanyagtartály kapacitása (l)
* **Trip** - az utazások rögzítéséért felelős
  * start_time [datetime] - az utazás kezdési időpontja
  * end_time [datetime] - az utazás befejezési időpontja
  * planned_distance [float] - az előzetesen tervezett távolság (km)
  * actual_distance [float] - az utazás során ténylegesen megtett távolság (km)
  * start_odometer [int] - a jármű kilométeróra állása az utazás kezdetén
  * end_odometer [int] - a jármű kilométeróra állása az utazás végén
  * planned_duration [time] - az előzetesen tervezett menetidő
  * actual_duration [time] - az utazás tényleges időtartama
* **Location** - különböző helyszínek "megtestesítője"
  * name [varchar(255)] - a helyszín neve vagy elnevezése
  * location_type [enum('partner', 'site', 'station', 'other')] - a helyszín típusa (partner, telephely, töltőállomás, egyéb)
  * is_headquarter [tinyint(1)] - jelzi, hogy a telephely maga a székhely-e
* **TravelPurposeDictionary**  - utazás célja szótár
  * travel_purpose [varchar(100)] - az utazási cél megnevezése (pl. „Üzleti találkozó”, „Kiszállítás”, „Irodai munka” stb.)
  * type [varchar(50)] - az utazási cél típusa (pl. „munka”, „magáncél”, „logisztika” stb.)
  * note [text] - opcionális megjegyzés az utazási célhoz
  * is_system [tinyint(1)] - megmutatja, hogy a bejegyzés rendszer által létrehozott-e, vagy felhasználó által hozzáadott
* **FuelExpense** - üzemanyagköltségek és tankolási események dokumentálása
  * expense_date [datetime] - a tankolás/töltés időpontja
  * amount [float] - a tankolás költsége a megadott pénznemben
  * currency [varchar(10)] - a tranzakció pénzneme (pl. HUF, EUR, USD)
  * fuel_quantity [float] - az üzemanyag mennyisége literben (l)
  * odometer [int] - a tankolás pillanatában rögzített kilométeróra állás
* **FuelPrice** - a különböző üzemanyagfajták aktuális árát tárolása adott időszakokra vonatkozóan (HUF/liter)
  * period [date] - az időszak kezdő dátuma, amelyre az üzemanyagárak érvényesek
  * petrol [float] - a 95-ös benzin ára
  * mixture [float] - az etanol-keverék (E85) ára
  * diesel [float] - a dízel ára
  * lp_gas [float] - LPG gáz ára

### Kapcsolatok
* User `N : 1` Role
  * egy felhasználóhoz csak egy szerepkör tartozhat, de egy szerepkörhöz több felhasználó is rendelhető (role_id)
* User `N : M` Task
  * egy felhasználó több feladathoz is hozzárendelhető, míg egy feladathoz egy vagy több felhasználó is kapcsolódhat
  * a kapcsolótábla tartalmazza a következő mezőket az N : M kapcsolathoz szükséges idegen kulcsokon kívül:
    * assigned_at [datetime] – a felhasználó adott feladathoz rendelésének időpontja
    * role_on_task [string] – a felhasználó szerepe a feladatban
* User `1 : N` JournalEntry
    * egy felhasználó több naplóbejegyzést is rögzíthet, míg egy naplóbejegyzés mindig csak egyetlen felhasználóhoz kapcsolódik (user_id)
* User `1 : N` LeaveRequest
  * egy felhasználó több szabadságkérelmet is benyújthat, ezzel egyúttal egy szabadságkérelem mindig pontosan egy felhasználóhoz tartozik (user_id)
  * egy admin több szabadságkérelmet is jóváhagyhat, ezért az approved_by kapcsolat is 1:N
* User `1 : N` OvertimeRequest
  * egy felhasználó többszöri alkalommal is bejelentheti a ledolgozott túlóráinak számát; egy túlóra bejelentés mindig ahhoz tartozik, aki azt benyújtotta (user_id)
  * egy admin több túlóra bejelentést is elfogadhat, ezért az approved_by kapcsolat is 1:N
* User `1 : N` Car
  * egy felhasználónak több autója is lehet, azonban egy autónak csak egy tulajdonosa lehet (user_id)
* User `1 : N` FuelExpense
  * egy felhasználó több tankolási tranzakciót is rögzíthet, de egy tankolás kizárólag azon felhasználóhoz kapcsolódik, aki az adott nap az autót használta és fizette az üzemanyagot (user_id)
* User `1 : N` Trip
  * egy felhasználó több utazást is rögzíthet, viszont egy utazás kizárólag egy adott felhasználóhoz kapcsolódik (aki az adott utazást végrehajtotta) (user_id)
* Role `N : M` Permission
  * egy szerep több jogosultsággal is rendelkezhet, míg egy jogosultság több szerephez tartozhat
  * a kapcsolótábla tartalmazza a következő mezőket az N : M kapcsolathoz szükséges idegen kulcsokon kívül:
    * is_active [tinyint(1)] – jelzi, hogy aktív-e a jogosultság az adott szerepnél
    * revoked_at [datetime] – időbélyeg, ami meghatározza, hogy mikor vonták vissza a jogosultságot
* LawCategory `1 : N` Law
  * egy adott jogszabály mindig egyetlen kategóriába sorolható, egy kategória viszont több jogszabályt is tartalmazhat (pl. a "Munkaügyi jogszabályok" kategóriába többféle munkaügyi törvény is bekerülhet) (category_id)
* Car `1 : N` FuelExpense
  * egy autóval többször is lehet tankolni, viszont egy tankolás kizárólag egy autóhoz tartozik (car_id)
* Car `1 : N` Trip
  * egy autóhoz több utazás is kapcsolódhat, de egy utazás mindig egy adott autóhoz tartozik (car_id)
* Trip `N : 1` Location
  * egy utazás mindig két helyszín között történik, ezért a tábla két idegen kulcsot tartalmaz (start_location_id, destination_id)
  * egy adott helyszín több utazás kiindulópontja vagy célállomása lehet
* Location `1 : N` FuelExpense
  * egy helyszínen több tankolás is történhet különböző időpontokban, de egy tankolás mindig egy adott helyszínen történik
* Location `1 : 1` Address
  * egy helyszín pontosan egy címhez tartozik, és egy cím is kizárólag egy helyszínhez rendelhető
* Location `N : M` TravelPurposeDictionary
  * egy helyszínhez több utazási cél is tartozhat, míg egy utazási cél pedig több különböző helyszínhez kapcsolódhat
