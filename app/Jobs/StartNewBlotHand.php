<?php

namespace App\Jobs;

use App\Models\GameRoom;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Game\BlotGameService;

class StartNewBlotHand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public GameRoom $room
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(BlotGameService $blotGameService): void
    {
        // Start a new hand of Blot
        $blotGameService->startNewHand($this->room);
    }
}