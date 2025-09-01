<?php

namespace App\Jobs;

use App\Data\PilotReportData;
use App\Models\PilotReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class FetchPilotReports implements ShouldQueue
{
    use Queueable;

    protected Collection|array $reports;

    /**
     * Create a new job instance.
     *
     * @throws ConnectionException
     */
    public function __construct()
    {
        $this->reports = Http::get(config('services.aviationweather.airep_url'))->json()['features'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->reports = collect($this->reports ?? [])->map(fn (array $report
        ): PilotReportData => PilotReportData::from(array_merge(
            ['id' => $report['id']],
            $report['properties']
        )));

        $this->reports->each(fn (PilotReportData $report
        ) => PilotReport::updateOrCreate(['external_id' => $report->external_id], [
            'location' => $report->location,
            'aircraft' => $report->aircraft,
            'altitude' => $report->altitude,
            'sky' => $report->sky,
            'turbulence' => $report->turbulence,
            'icing' => $report->icing,
            'visibility' => $report->visibility,
            'temperature' => $report->temperature,
            'wind' => $report->wind,
            'urgent' => $report->urgent,
            'raw' => $report->raw,
            'reported_at' => $report->reported_at,
        ]));
    }
}
