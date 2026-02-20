<?php

namespace App\Jobs;

use App\Services\SourceIngestionProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSourceIngestion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $ingestionId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $ingestionId)
    {
        $this->ingestionId = $ingestionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SourceIngestionProcessor $processor)
    {
        $processor->process($this->ingestionId);
    }
}
