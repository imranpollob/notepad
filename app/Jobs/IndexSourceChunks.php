<?php

namespace App\Jobs;

use App\Services\SourceIndexingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IndexSourceChunks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $sourceId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $sourceId)
    {
        $this->sourceId = $sourceId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SourceIndexingService $service)
    {
        $service->indexSource($this->sourceId);
    }
}
