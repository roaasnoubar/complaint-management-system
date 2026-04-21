<?php

namespace Database\Seeders;

use App\Models\Authority;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authorities = Authority::all();

        $departments = [
            ['Municipality' => ['Infrastructure', 'Sanitation', 'Urban Planning']],
            ['Health Department' => ['Hospitals', 'Clinics', 'Emergency Services']],
            ['Education Authority' => ['Schools', 'Universities', 'Training']],
            ['Public Works' => ['Roads', 'Buildings', 'Utilities']],
            ['Police Department' => ['Patrol', 'Investigations', 'Traffic']],
        ];

        foreach ($departments as $deptGroup) {
            foreach ($deptGroup as $authorityName => $deptNames) {
                $authority = $authorities->firstWhere('name', $authorityName);
                if ($authority) {
                    foreach ($deptNames as $deptName) {
                        Department::updateOrCreate(
                            [
                                'authority_id' => $authority->id,
                                'name' => $deptName,
                            ],
                            [
                                'description' => "Department of {$deptName} under {$authorityName}",
                                'is_active' => true,
                            ]
                        );
                    }
                }
            }
        }
    }
}
