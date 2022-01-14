<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateUsersTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kooomo:generateUsersTokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new plain tokens for each user in database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->table(
            ['id', 'plain token'],
            User::all()->map(function($user) {
                $token = $user->createToken('testing');
                return [
                    $user->id,
                    $token->plainTextToken,
                ];
            })->toArray(),
        );
        return 0;
    }
}
