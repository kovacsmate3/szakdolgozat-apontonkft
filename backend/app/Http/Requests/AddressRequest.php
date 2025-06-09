<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddressRequest extends FormRequest
{
    protected function publicSpaceTypes(): array
    {
        return [
            'akna',
            'akna-alsó',
            'akna-felső',
            'alagút',
            'alsórakpart',
            'arborétum',
            'autóút',
            'barakképület',
            'barlang',
            'bejáró',
            'bekötőút',
            'bánya',
            'bányatelep',
            'bástya',
            'bástyája',
            'csárda',
            'csónakházak',
            'domb',
            'dűlő',
            'dűlők',
            'dűlősor',
            'dűlőterület',
            'dűlőút',
            'egyetemváros',
            'egyéb',
            'elágazás',
            'emlékút',
            'erdészház',
            'erdészlak',
            'erdő',
            'erdősor',
            'fasor',
            'fasora',
            'felső',
            'forduló',
            'főmérnökség',
            'főtér',
            'főút',
            'föld',
            'gyár',
            'gyártelep',
            'gyárváros',
            'gyümölcsös',
            'gát',
            'gátsor',
            'gátőrház',
            'határsor',
            'határút',
            'hegy',
            'hegyhát',
            'hegyhát dűlő',
            'hegyhát',
            'köz',
            'hrsz',
            'hrsz.',
            'ház',
            'hídfő',
            'iskola',
            'játszótér',
            'kapu',
            'kastély',
            'kert',
            'kertsor',
            'kerület',
            'kilátó',
            'kioszk',
            'kocsiszín',
            'kolónia',
            'korzó',
            'kultúrpark',
            'kunyhó',
            'kör',
            'körtér',
            'körvasútsor',
            'körzet',
            'körönd',
            'körút',
            'köz',
            'kút',
            'kültelek',
            'lakóház',
            'lakókert',
            'lakónegyed',
            'lakópark',
            'lakótelep',
            'lejtő',
            'lejáró',
            'liget',
            'lépcső',
            'major',
            'malom',
            'menedékház',
            'munkásszálló',
            'mélyút',
            'műút',
            'oldal',
            'orom',
            'park',
            'parkja',
            'parkoló',
            'part',
            'pavilon',
            'piac',
            'pihenő',
            'pince',
            'pincesor',
            'postafiók',
            'puszta',
            'pálya',
            'pályaudvar',
            'rakpart',
            'repülőtér',
            'rész',
            'rét',
            'sarok',
            'sor',
            'sora',
            'sportpálya',
            'sporttelep',
            'stadion',
            'strandfürdő',
            'sugárút',
            'szer',
            'sziget',
            'szivattyútelep',
            'szállás',
            'szállások',
            'szél',
            'szőlő',
            'szőlőhegy',
            'szőlők',
            'sánc',
            'sávház',
            'sétány',
            'tag',
            'tanya',
            'tanyák',
            'telep',
            'temető',
            'tere',
            'tető',
            'turistaház',
            'téli kikötő',
            'tér',
            'tömb',
            'udvar',
            'utak',
            'utca',
            'utcája',
            'vadaskert',
            'vadászház',
            'vasúti megálló',
            'vasúti őrház',
            'vasútsor',
            'vasútállomás',
            'vezetőút',
            'villasor',
            'vágóhíd',
            'vár',
            'várköz',
            'város',
            'vízmű',
            'völgy',
            'zsilip',
            'zug',
            'állat és növ.kert',
            'állomás',
            'árnyék',
            'árok',
            'átjáró',
            'őrház',
            'őrházak',
            'őrházlak',
            'út',
            'útja',
            'útőrház',
            'üdülő',
            'üdülő-part',
            'üdülő-sor',
            'üdülő-telep',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'country' => ['string', 'max:100'],
            'postalcode' => ['integer', 'regex:/^[0-9]{4}$/'],
            'city' => ['string', 'max:100'],
            'road_name' => ['string', 'max:100'],
            'public_space_type'  => [
                'string',
                'max:50',
                Rule::in($this->publicSpaceTypes()),
            ],
            'building_number' => ['string', 'max:50'],
        ];

        // Ha létrehozási kérés, minden mező kötelező
        if ($this->isMethod('post')) {
            $rules = array_map(function ($rule) {
                return array_merge(['required'], $rule);
            }, $rules);
        } else {
            // Frissítésnél csak a megadott mezők szükségesek
            $rules = array_map(function ($rule) {
                return array_merge(['sometimes'], $rule);
            }, $rules);
        }

        // Ha location_id is szerepel a kérésben
        if ($this->has('location_id')) {
            $rules['location_id'] = ['nullable', 'exists:locations,id'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $msgs = [
            'location_id.exists' => 'A megadott helyszín nem létezik.',
            'country.required' => 'Az ország megadása kötelező.',
            'country.string' => 'Az ország csak szöveg formátumú lehet.',
            'country.max' => 'Az ország neve maximum 100 karakter hosszú lehet.',
            'postalcode.required' => 'Az irányítószám megadása kötelező.',
            'postalcode.integer' => 'Az irányítószám csak szám lehet.',
            'postalcode.regex' => 'Az irányítószámnak 4 számjegyből kell állnia.',
            'city.required' => 'A város megadása kötelező.',
            'city.string' => 'A város csak szöveg formátumú lehet.',
            'city.max' => 'A város neve maximum 100 karakter hosszú lehet.',
            'road_name.required' => 'A közterület nevének megadása kötelező.',
            'road_name.string' => 'A közterület neve csak szöveg formátumú lehet.',
            'road_name.max' => 'A közterület neve maximum 100 karakter hosszú lehet.',
            'public_space_type.required' => 'A közterület jellegének megadása kötelező.',
            'public_space_type.string' => 'A közterület jellege csak szöveg formátumú lehet.',
            'public_space_type.max' => 'A közterület jellege maximum 50 karakter hosszú lehet.',
            'building_number.required' => 'A házszám megadása kötelező.',
            'building_number.string' => 'A házszám csak szöveg formátumú lehet.',
            'building_number.max' => 'A házszám maximum 50 karakter hosszú lehet.',
        ];

        $msgs['public_space_type.in'] = 'A közterület jellege nem megfelelő érték.';
        return $msgs;
    }
}
