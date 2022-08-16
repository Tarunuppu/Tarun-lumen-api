<?php
namespace App\Traits;
use App\Notifications\VerifyEmail;
use App\Notifications\ForgetPasswordEmailVerification;
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
/**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }
}