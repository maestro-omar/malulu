<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceStatusesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('attendance_statuses')->insert([
            [
                'code' => 'presente',
                'name' => 'Presente',
                'symbol' => 'P',
                'is_absent' => false,
                'is_justified' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'tarde',
                'name' => 'Tarde',
                'symbol' => 'T',
                'is_absent' => false,
                'is_justified' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ausente_injustificado',
                'name' => 'Ausente (Injustificado)',
                'symbol' => 'A-I',
                'is_absent' => true,
                'is_justified' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ausente_justificado',
                'name' => 'Ausente (Justificado)',
                'symbol' => 'A-J',
                'is_absent' => true,
                'is_justified' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'presente_sin_clases',
                'name' => 'Presente (sin clases)',
                'symbol' => 'P-SC',
                'is_absent' => false,
                'is_justified' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ausente_sin_clases',
                'name' => 'Ausente (sin clases)',
                'symbol' => 'A-SC',
                'is_absent' => true,
                'is_justified' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
