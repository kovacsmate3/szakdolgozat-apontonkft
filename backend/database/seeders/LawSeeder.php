<?php

namespace Database\Seeders;

use App\Models\Law;
use App\Models\LawCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LawSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allLawsData = [
            'Földmérés' => [
                [
                    'title' => 'A földmérési és térképészeti tevékenységről szóló 2012. évi XLVI. törvény (Fttv)',
                    'official_ref' => '2012. évi XLVI. törvény',
                    'date_of_enactment' => '2012-01-01',
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2012-46-00-00',
                ],
                [
                    'title' => 'Az ingatlan-nyilvántartási célú földmérési és térképészeti tevékenység részletes szabályairól szóló 8/2018. (VI. 29.) AM rendelet',
                    'official_ref' => '8/2018. (VI. 29.) AM rendelet',
                    'date_of_enactment' => '2018-07-14',
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2018-8-20-7R',
                ],
                [
                    'title' => '8/2018 AM rendelet mellékletei',
                    'official_ref' => 'Mellékletek',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://www.foldhivatal.hu/content/view/273/197/',
                ],
                [
                    'title' => '15/2013. (III. 11.) VM rendelet (állami alapadatok, térképi adatbázisok)',
                    'official_ref' => '15/2013. (III. 11.) VM rendelet',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2013-15-20-2Y',
                ],
                [
                    'title' => 'DAT1. szabályzat',
                    'official_ref' => 'DAT1',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://drive.google.com/file/d/1tiAQ0r4OA0z5AX74XhDarHbHunqTqCcD/view',
                ],
                [
                    'title' => 'DAT1. szabályzat – M1 (állami ingatlan-nyilvántartási térképi adatbázis adattáblái)',
                    'official_ref' => 'DAT1 – M1',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://drive.google.com/file/d/1pfzhPOvDFH8sfrJ4LQnjvteILmqbSV48/view',
                ],
                [
                    'title' => 'DAT1. szabályzat jelkulcsi elemei (DAT1-M2.)',
                    'official_ref' => 'DAT1-M2.',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://drive.google.com/file/d/1sCZR8rJ2W6_ZOhLZwAxjW_5d1rYM8NgS/view',
                ],
                [
                    'title' => 'M.2-2021 Mérnökgeodéziai tervezési segédlet',
                    'official_ref' => 'M.2-2021',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://ggt.hu/files/fap/2021/M.2.-2021.pdf',
                ],
                [
                    'title' => 'Szakmai útmutató a digitális tervezési alaptérképek készítéséhez',
                    'official_ref' => 'FAP_108_2022_GGT',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://ggt.hu/files/fap/2022/FAP_108_2022_GGT.pdf',
                ],
                [
                    'title' => 'Az önálló ingatlanok helyrajziszámozásáról és az alrészletek megjelöléséről szóló 44/2006. (VI. 13.) FVM rendelet',
                    'official_ref' => '44/2006. (VI. 13.) FVM rendelet',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2006-44-20-82',
                ],
                [
                    'title' => '52/2014. (IV. 29.) VM rendelet (földmérő igazolvány, földmérési szakfelügyelő)',
                    'official_ref' => '52/2014. (IV. 29.) VM rendelet',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2014-52-20-2Y',
                ],
                [
                    'title' => '327/2015. (XI. 10.) Korm. rendelet (GD-T)',
                    'official_ref' => '327/2015. (XI. 10.) Korm. rendelet',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2015-327-20-22',
                ],
                [
                    'title' => '19/2013. (III. 21.) VM rendelet (földmérési szakképzettség)',
                    'official_ref' => '19/2013. (III. 21.) VM rendelet',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2013-19-20-2Y',
                ],
                [
                    'title' => 'Általános térképészeti és földmérési szabályzat – melléklet a 4/1980. (I. 25.) MÉM rendelethez',
                    'official_ref' => '4/1980. (I. 25.) MÉM',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://drive.google.com/file/d/1BN7T1fs8hvVVlMdgplNleB-hpCgTAaVf/view',
                ],
            ],
            'Ingatlan-nyilvántartás' => [
                [
                    'title' => 'Az ingatlan-nyilvántartásról szóló 2021. évi C. törvény (Inytv)',
                    'official_ref' => '2021. évi C. törvény',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2021-100-00-00',
                ],
                [
                    'title' => 'A 2021. évi C. törvény végrehajtásáról szóló 179/2023. (V. 15.) kormányrendelet (Inytv Vhr)',
                    'official_ref' => '179/2023. (V. 15.) Korm. rendelet',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2023-179-20-22',
                ],
                [
                    'title' => 'Az ingatlan-nyilvántartásról szóló 1997. évi CXLI. törvény',
                    'official_ref' => '1997. évi CXLI. törvény',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/1997-141-00-00',
                ],
                [
                    'title' => 'Az ingatlan-nyilvántartásról szóló 1997. évi CXLI. törvény végrehajtásáról szóló 109/1999. (XII. 29.) FVM rendelet',
                    'official_ref' => '109/1999. (XII. 29.) FVM rendelet',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/1999-109-20-82',
                ],
            ],
            'Építésügy' => [
                [
                    'title' => 'A magyar építészetről szóló 2023. évi C. törvény',
                    'official_ref' => '2023. évi C. törvény',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2023-100-00-00',
                ],
                [
                    'title' => '280/2024. (IX. 30.) Korm. rendelet (TÉKA)',
                    'official_ref' => '280/2024. (IX. 30.) Korm. rendelet',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2024-280-20-22',
                ],
                [
                    'title' => '281/2024. (IX. 30.) Korm. rendelet (építésügyi hatósági eljárások)',
                    'official_ref' => '281/2024. (IX. 30.) Korm. rendelet',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2024-281-20-22',
                ],
                [
                    'title' => 'A társasházakról szóló 2003. évi CXXXIII. törvény',
                    'official_ref' => '2003. évi CXXXIII. törvény',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2003-133-00-00',
                ],
                [
                    'title' => 'A lakások és helyiségek bérletére, valamint az elidegenítésükre vonatkozó egyes szabályokról szóló 1993. évi LXXVIII. törvény',
                    'official_ref' => '1993. évi LXXVIII. törvény',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/1993-78-00-00',
                ],
                [
                    'title' => '322/2024. (XI. 6.) Korm. rendelet (digitális szolgáltatások, címkezelés)',
                    'official_ref' => '322/2024. (XI. 6.) Korm. rendelet',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2024-322-20-22#SZ122',
                ],
                [
                    'title' => 'Az épített környezet alakításáról és védelméről szóló 1997. évi LXXVIII. törvény',
                    'official_ref' => '1997. évi LXXVIII. törvény',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/1997-78-00-00',
                ],
                [
                    'title' => '253/1997. (XII. 20.) Korm. rendelet (OTÉK)',
                    'official_ref' => '253/1997. (XII. 20.) Korm. rendelet',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/1997-253-20-22',
                ],
                [
                    'title' => '312/2012. (XI. 8.) Korm. rendelet (építésügyi és építésfelügyeleti eljárások)',
                    'official_ref' => '312/2012. (XI. 8.) Korm. rendelet',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2012-312-20-22',
                ],
            ],
            'Földügy' => [
                [
                    'title' => 'A termőföld védelméről szóló 2007. évi CXXIX. törvény',
                    'official_ref' => '2007. évi CXXIX. törvény',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2007-129-00-00',
                ],
                [
                    'title' => 'A mező és erdőgazdasági földek forgalmáról szóló 2013. évi CXXII. törvény',
                    'official_ref' => '2013. évi CXXII. törvény',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2013-122-00-00',
                ],
                [
                    'title' => '47/2017. (IX. 29.) FM rendelet (földminősítés)',
                    'official_ref' => '47/2017. (IX. 29.) FM rendelet',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2017-47-20-11',
                ],
                [
                    'title' => '38/2014. (II. 24.) Korm. rendelet (földművesek, nyilvántartás)',
                    'official_ref' => '38/2014. (II. 24.) Korm. rendelet',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2014-38-20-22',
                ],
            ],
            'Eljárási díjak' => [
                [
                    'title' => 'Az ingatlan-nyilvántartási, telekalakítási, földmérési és térképészeti tevékenységgel kapcsolatos eljárások díjairól szóló 1/2024. (I. 30.) KTM rendelet',
                    'official_ref' => '1/2024. (I. 30.) KTM rendelet',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2024-1-20-9D',
                ],
            ],
            'További jogszabályok' => [
                [
                    'title' => 'Magyarország Alaptörvénye',
                    'official_ref' => 'Alaptörvény',
                    'date_of_enactment' => '2012-01-01', // pl. 2012.01.01.
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2011-4301-02-00',
                ],
                [
                    'title' => 'A Polgári Törvénykönyvről szóló 2013. évi V. törvény (Ptk)',
                    'official_ref' => '2013. évi V. törvény',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2013-5-00-00',
                ],
                [
                    'title' => 'A szomszédjogok és a tulajdonjog korlátainak különös szabályairól szóló 2013. évi CLXXIV. törvény',
                    'official_ref' => '2013. évi CLXXIV. törvény',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/2013-174-00-00',
                ],
                [
                    'title' => 'A mérésügyről szóló 1991. évi XLV. törvény',
                    'official_ref' => '1991. évi XLV. törvény',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/1991-45-00-00',
                ],
                [
                    'title' => '127/1991. (X. 9.) Korm. rendelet (mérésügy végrehajtás)',
                    'official_ref' => '127/1991. (X. 9.) Korm. rendelet',
                    'date_of_enactment' => null,
                    'is_active' => true,
                    'link' => 'https://njt.hu/jogszabaly/1991-127-20-22',
                ],
            ],
        ];

        foreach ($allLawsData as $categoryName => $laws) {
            $category = LawCategory::where('name', $categoryName)->first();
            foreach ($laws as $lawData) {
                $lawData['category_id'] = $category->id;
                Law::create($lawData);
            }
        }
    }
}
