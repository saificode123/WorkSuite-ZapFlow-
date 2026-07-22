<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleSetting;
use Illuminate\Database\Seeder;

class TravelModuleDefaultDisableSeeder extends Seeder
{
    private const NON_TRAVEL_MODULES = [
        'projects',
        'tasks',
        'leads',
        'deals',
        'contracts',
        'estimates',
        'invoices',
        'expenses',
        'timelogs',
        'orders',
        'knowledgebase',
        'messages',
        'events',
        'notices',
    ];

    public function run(): void
    {
        $nonTravelIds = Module::whereIn('module_name', self::NON_TRAVEL_MODULES)->pluck('id');

        $companies = \App\Models\Company::all();

        foreach ($companies as $company) {
            foreach ($nonTravelIds as $moduleId) {
                ModuleSetting::withoutEvents(function () use ($company, $moduleId) {
                    ModuleSetting::updateOrCreate(
                        [
                            'company_id' => $company->id,
                            'module_id'  => $moduleId,
                        ],
                        ['status' => 0]
                    );
                });
            }
        }

        $this->command?->info('Non-travel modules disabled for all companies. Admin can re-enable via Settings.');
    }
}
