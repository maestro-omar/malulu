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
                'is_absent' => false,
                'is_justified' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'tarde',
                'name' => 'Tarde',
                'is_absent' => false,
                'is_justified' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ausente_injustificado',
                'name' => 'Ausente (Injustificado)',
                'is_absent' => true,
                'is_justified' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ausente_justificado',
                'name' => 'Ausente (Justificado)',
                'is_absent' => true,
                'is_justified' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'presente_sin_clases',
                'name' => 'Presente (sin clases)',
                'is_absent' => false,
                'is_justified' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ausente_sin_clases',
                'name' => 'Ausente (sin clases)',
                'is_absent' => true,
                'is_justified' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
