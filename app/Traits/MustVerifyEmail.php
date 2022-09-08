<?php
namespace App\Traits;
use App\Notifications\VerifyEmail;
use App\Notifications\ForgetPasswordEmailVerification;
use App\Notifications\EmailWithPassword;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\DB;
trait MustVerifyEmail
{
    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return ! is_null($this->email_verified_at);
    }
    // public function hasVerifiedEmail($useremail){
    //     dd(DB::table('users')->where('email', $useremail)->get());
    // }
/**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }
/**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        //dd($this);
        $this->notify(new VerifyEmail);
    }
    public function sendEmailVerificationForgetPassword(){
        $this->notify(new ForgetPasswordEmailVerification);
    }
    public function sendEmailWithPassword(){
        $this->notify(new EmailWithPassword);
    }
/**
 * 
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }
}