<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Mail\TasksAnalysis;
use Illuminate\Support\Facades\Mail;

class DemoCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendDailyMails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending Daily Mails';

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
     * @return mixed
     */
    public function handle()
    {
        echo("hello there");
        $users= User::pluck('email');
        foreach($users as $user){
            echo($user);
            $analysis = array(
            "countAssigned" => Task::where('delete',1)->where('assignee',$user)->where('status','assigned')->count(),
            "countInProgress" => Task::where('delete',1)->where('assignee',$user)->where('status','in-progress')->count(),
            "countCompleted" => Task::where('delete',1)->where('assignee',$user)->where('status','completed')->count(),
            "countDeleted" => Task::where('delete',1)->where('assignee',$user)->where('status','deleted')->count()
            );
            $email = new TasksAnalysis($analysis);
            Mail::to($user)->send($email);

        }
        
    }
}
