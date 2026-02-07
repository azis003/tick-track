<?php

namespace App\Console\Commands;

use App\Services\TicketService;
use Illuminate\Console\Command;

class AutoCloseTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:auto-close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-close resolved tickets after 3 days without response';

    /**
     * Execute the console command.
     */
    public function handle(TicketService $ticketService): int
    {
        $count = $ticketService->autoCloseExpiredTickets();
        $this->info("Auto-closed {$count} tickets.");
        return Command::SUCCESS;
    }
}
