<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Task;
use App\Models\Notification;
use App\Jobs\SendNotificationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class NotificationController extends Controller
{   
    public function createNotification($type,$notificationData, $notificationTo){
        $mailData= array();
        $notification = array();
        $notification['email'] = $notificationTo;
        switch($type){ 
            case "taskUpdated":
                $notification['message'] = "task {$notificationData['title']} is updated by {$notificationData['byWhom']}";
                $mailData['subject'] = "Task Updated";
                break;
            case "taskCreated":
                $notification['message']= "task {$notificationData['title']} is created by {$notificationData['byWhom']} and assigned it to you";
                $mailData['subject'] = "Task Created";   
                break;
            case "taskDeleted":
                $notification['message']= "task {$notificationData['title']} is deleted by {$notificationData['byWhom']}";
                $mailData['subject'] = "Task Deleted";
                break;
        }
        $pushercreate = new \Pusher\Pusher(
            env("PUSHER_API_KEY"),
            env("PUSHER_API_SECRET"),
            env("PUSHER_API_ID"),
            array('cluster' => 'ap2')
          );
        $pushercreate->trigger($notificationTo, 'my-event', array('message' => $notification['message']));
        $final = Notification::create($notification);

        $mailData['email'] = $notificationTo;
        $mailData['message'] = $notification['message'];
        $emailJobs = new SendNotificationMail($mailData);
        dispatch($emailJobs);
       

    }
    public function deleteNotification(Request $request){
        Notification::findOrFail($request->id)->delete();
        return response('Deleted Successfully', 200);
    }
    public function clearNotification(Request $request){
        $email = $request->user()->email;
        Notification::where('email', $email)->delete();
    }
    public function getNotification(Request $request){
        $email = $request->user()->email;
        $notification = Notification::where('email',$email)->select('message','id')->get();
        return response($notification);
    }
}
