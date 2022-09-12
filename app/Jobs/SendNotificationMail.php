<?php

namespace App\Jobs;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;

class SendNotificationMail extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $mailData;
    public function __construct($mailData)
    {
        $this->mailData =  $mailData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = new NotificationMail($this->mailData);
        Mail::to($this->mailData['email'])->send($email);
    }
}
