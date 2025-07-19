<?php
namespace Database\Seeders;


use App\Models\Application;
use App\Models\ApplicationTimeline;
use Illuminate\Database\Seeder;

class TimelineSeeder extends Seeder
{
    public function run()
    {
        $application = Application::first();

        ApplicationTimeline::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'application_id' => $application->id,
            'status' => 'submitted',
            'assigned_to_id' => null,
            'remark' => 'Initial submission by the author',
        ]);

        ApplicationTimeline::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'application_id' => $application->id,
            'status' => 'under review',
            'assigned_to_id' => 1,
            'remark' => 'Assigned to Admin John Doe for initial review',
        ]);

        ApplicationTimeline::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'application_id' => $application->id,
            'status' => 'approved',
            'assigned_to_id' => 1,
            'remark' => 'Approved after thorough review',
        ]);
    }
}
