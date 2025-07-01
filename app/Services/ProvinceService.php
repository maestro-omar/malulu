<?php

namespace App\Services;

use App\Models\Catalogs\Province;

class ProvinceService
{
    public function getProvinces()
    {
        return Province::all()->map(function ($province) {
            return [
                'id' => $province->id,
                'code' => $province->code,
                'name' => $province->name,
                'order' => $province->order,
                'logo1' => $province->logo1,
                'logo2' => $province->logo2,
                'title' => $province->title,
                'subtitle' => $province->subtitle,
                'link' => $province->link,
            ];
        });
    }

    public function createProvince(array $data)
    {
        return Province::create($data);
    }

    public function updateProvince(Province $province, array $data)
    {
        return $province->update($data);
    }

    public function deleteProvince(Province $province)
    {
        // Add logic if you want to prevent deletion under certain conditions
        return $province->delete();
    }
} 