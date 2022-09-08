<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use App\Traits\MustVerifyEmail;

class Task extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory, Notifiable, MustVerifyEmail;
    public $timestamps = false;
    protected $fillable = [
        'title', 'description','status', 'assignee', 'createdby', 'duedate', 'delete',
    ];
    protected $hidden = [
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [
        ];
    }
    protected static function boot()
    {
        parent::boot();
    
        static::saved(function ($model) {
        if( $model->isDirty('email') ) {
            $model->setAttribute('email_verified_at', null);
            $model->sendEmailVerificationNotification();
        }
        });
    }
}
