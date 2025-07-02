<?php

namespace App\Services;

use App\Models\Catalogs\Province;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
        try {
            $validated = $this->validateProvinceData($data, $province);

            return $province->update($validated);

            return response()->json([
                'message' => 'Provincia actualizada',
                'data' => $province
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error en algún campo',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteProvince(Province $province)
    {
        // Add logic if you want to prevent deletion under certain conditions
        return $province->delete();
    }



    /**
     * Validate user data
     */
    public function validateProvinceData(array $data, ?Province $province = null)
    {
        $rules = [
            'code' => 'required|string|unique:provinces,code,' . $province->id,
            'name' => 'required|string',
            'order' => 'nullable|integer',
            'title' => 'nullable|string',
            'subtitle' => 'nullable|string',
            'link' => 'nullable|string',
        ];

        $messages = [];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
