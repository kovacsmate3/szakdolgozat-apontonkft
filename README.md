ELTE Informatikai Kar - Programtervező informatikus BSc - Szoftverfejlesztő (C) szakirány
# Diplomamunka (A-Ponton Kft. weboldala)

## Adatbázis
A megfelelő adatbázis táblák és modellek implementálása az alábbiak szerint (*az alapvető mezők - id, created_at, updated_at - minden táblában szerepelnek*):
### Modellek
*Általános táblák*:
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
  * description [text] - a szerep leírása (*nullable*)
* **Permission** - az egyes szerepekhez kapcsolódó engedély, jogosultság
  * name [varchar(100)] - a jogosultság megnevezése (pl. )
  * category [varchar(100)] - jogosultsági kategória
  * description [text] - a jogosultság részletes leírása (*nullable*)
* **Law** - egy konkrét jogszabály
  * title [varchar(255)] - a jogszabály címe
  * official_ref [varchar(255)] - a jogszabály hivatalos azonosítója
  * date_of_enactment [date] - a jogszabály hatályba lépésének dátuma
  * is_active [tinyint(1)] – jelzi, hogy a jogszabály jelenleg érvényes-e
  * attachments [json] – további fájlok, linkek, vagy dokumentumok csatolására szolgál
* **LawCategory** - a jogszabályok csoportosítására szolgál (minden jogszabály egy adott kategóriához tartozik)
  * name [varchar(100)] - a jogszabálytípus neve
  * description [text] - a jogszabály típusának részletesebb leírása

*Útnyilvántartás megvalósításáért felelős táblák*:
* **Car** - a cég autóit realizálja
  * car_type [varchar(30)] - az autó típusa
  * license_plate [varchar(20)] -a jármű rendszáma
  * manufacturer [varchar(100)] - az autó gyártója (pl. Ford, Toyota)
  * model [varchar(100)] - az autó modell
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

*Munkanyilvántartó funkciók realizálását képező táblák*:
* **Project** - egy vállalkozás által kezelt projektek
  * job_number [varchar(50)] - a projekt munkaszáma
  * project_name [varchar(75)] - a projekt neve (pl. Liberty, Beluga Bay)
  * location [varchar(100)] - fekvés meghatározása (külterület, belterület stb.)
  * parcel_identification_number [varchar(100)] - az adott földterület vagy ingatlan helyrajzi száma
  * deadline [datetime] - a projekt határideje, amely a végső teljesítés dátumát jelöli
  * description [text] - a projekt részletesebb leírása (pl. projekt célja, követelményei)
  * status [varchar(50)] - a projekt aktuális állapota (pl. „folyamatban”, „befejezett”, „elhalasztott”)
* **Task** - a különböző projektekhez tartozó konkrét feladatok
  * name [varchar(200)] - a feladat megnevezése
  * surveying_instrument [varchar(100)] - adott feladathoz szükséges mérőműszer vagy eszköz
  * priority [varchar(50)] - a feladat prioritása (pl. alacsony, közepes, magas)
  * status [varchar(50)] - a feladat aktuális állapota (pl. „folyamatban”, „befejezett”, „várakozó”)
  * description [text] - a feladat részletes leírása (*nullable*)
* **JournalEntry** - a vállalkozás alkalmazottai által végzett munkatevékenységek naplózása
  * work_date [date] - a munka elvégzésének dátuma
  * hours [time] - a munkavégzés időtartama
  * note [text] - opcionális megjegyzés a bejegyzéshez, amely részletezheti a munkafolyamatokat
  * work_type [varchar(50)] - a bejegyzés típusa (pl. „normál munkavégzés”, „szabadság”, „túlóra” stb.)
* **LeaveRequest** - a munkavállalók szabadságkérelmei
  * start_date [date] - a szabadság kezdő dátuma
  * end_date [date] - a szabadság végének dátuma
  * status [varchar(50)] - a kérelem aktuális állapota (pl. „pending”, „approved”, „rejected”)
  * reason [text] - a szabadságkérelem indoklása
  * approved_at [datetime] - az időbélyeg, amikor a kérelmet elbírálták
* **OvertimeRequest** - a dolgozók által elvégzett túlórák bejelentése
  * date [date] - az adott nap, amelyre a túlóra vonatkozik
  * hours [time] - a túlóra időtartama
  * status [varchar(50)] - a bejelentés aktuális állapota (pl. „pending”, „approved”, „rejected”)
  * reason [text] - a túlóra indoklása
  * approved_at [datetime] - az időbélyeg, amikor a kérelmet elbírálták
* **Address** - az összes címhez kapcsolódó információ
  * country [varchar(100)] - az ország neve, ahol a cím található
  * postalcode [int] - az irányítószám
  * city [varchar(100)] -
  * road_name [varchar(100)] - közerület neve
  * public_space_type [varchar(50)] - a közterület típusa (pl. „utca”, „tér”, „sétány”)
  * building_number [int] - az épület száma

### Kapcsolatok
* User `N : 1` Role
  * egy felhasználóhoz csak egy szerepkör tartozhat, de egy szerepkörhöz több felhasználó is rendelhető (role_id)
* User `N : M` Task
  * egy felhasználó több feladathoz is hozzárendelhető, míg egy feladathoz egy vagy több felhasználó is kapcsolódhat
  * a kapcsolótábla tartalmazza a következő mezőket az N : M kapcsolathoz szükséges idegen kulcsokon kívül (UserTask):
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
  * a kapcsolótábla tartalmazza a következő mezőket az N : M kapcsolathoz szükséges idegen kulcsokon kívül (RolePermission):
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
  * egy helyszínen több tankolás is történhet különböző időpontokban, de egy tankolás mindig egy adott helyszínen történik (location_id)
* Location `1 : 1` Address
  * egy helyszín pontosan egy címhez tartozik, és egy cím is kizárólag egy helyszínhez rendelhető (address_id)
* Location `N : M` TravelPurposeDictionary
  * egy helyszínhez több utazási cél is tartozhat, míg egy utazási cél pedig több különböző helyszínhez kapcsolódhat
  * kapcsolótábla tartalmazza az N:M kapcsolathoz szükséges idegen kulcsokat (LocationPurpose)
* Project `1 : N` Task
  * egy projekthez több feladat is tartozhat, de egy feladat kizárólag egy projekthez kapcsolódhat (project_id)
* Project `N : 1` Address
  * egy címhez több projekt is tartozhat, de egy projekt csak egy címhez kapcsolható (address_id)
* Task `1 : N` Task
  * egy feladatnak lehetnek alfeladatai, amelyeket egy másik feladat (szülő) tartalmazhat (parent_id) (*nullable*)
* Task `1 : N` JournalEntry
  * egy feladathoz több naplóbejegyzés is tartozhat, de egy adott bejegyzés pontosan egy feladathoz kapcsolódik (task_id)
* JournalEntry `N : 1` LeaveRequest
  * egy szabadságkérelemhez több naplóbejegyzés is kapcsolódhat, de egy naplóbejegyzéshez legfeljebb egy szabadságkérelem tartozhat. (leaverequest_id)
* JournalEntry `1 : 1` OvertimeRequest
  * egy túlóra bejelentés egy adott napra vonatkozik, emellett egy naplóbejegyzéshez legfeljebb egy túlóra bejelentés kapcsolódhat (overtimerequest_id)
