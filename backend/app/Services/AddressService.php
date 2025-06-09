<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

class AddressService
{
    /**
     * Cím létrehozása helyszínhez.
     *
     * @param array $addressData
     * @param \App\Models\Location $location
     * @return \App\Models\Address
     */
    public function createAddressForLocation(array $addressData, Location $location)
    {
        $addressData['location_id'] = $location->id;
        return Address::create($addressData);
    }

    /**
     * Cím frissítése helyszínhez.
     *
     * @param array $addressData
     * @param \App\Models\Location $location
     * @return \App\Models\Address
     */
    public function updateAddressForLocation(array $addressData, Location $location)
    {
        // Ha van már címe a helyszínnek, frissítjük
        if ($location->address) {
            $location->address->update($addressData);
            return $location->address->fresh();
        }

        // Ha még nincs címe, létrehozunk egyet
        return $this->createAddressForLocation($addressData, $location);
    }

    /**
     * Helyszín létrehozása címmel együtt.
     *
     * @param array $locationData
     * @param array $addressData
     * @return \App\Models\Location
     */
    public function createLocationWithAddress(array $locationData, array $addressData)
    {
        return DB::transaction(function () use ($locationData, $addressData) {
            // Helyszín létrehozása
            $location = Location::create($locationData);

            // Cím létrehozása a helyszínhez
            $this->createAddressForLocation($addressData, $location);

            // Helyszín visszaadása címmel együtt
            $location->load('address');
            return $location;
        });
    }

    /**
     * Helyszín frissítése címmel együtt.
     *
     * @param \App\Models\Location $location
     * @param array $locationData
     * @param array|null $addressData
     * @return \App\Models\Location
     */
    public function updateLocationWithAddress(Location $location, array $locationData, ?array $addressData = null)
    {
        return DB::transaction(function () use ($location, $locationData, $addressData) {
            // Helyszín frissítése
            $location->update($locationData);

            // Ha van címadat, frissítjük vagy létrehozzuk a címet
            if ($addressData) {
                $this->updateAddressForLocation($addressData, $location);
            }

            // Helyszín visszaadása friss adatokkal
            $location->load('address');
            return $location;
        });
    }
}
