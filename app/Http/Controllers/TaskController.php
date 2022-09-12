<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\DB;
class TaskController extends Controller
{
    public function updateMultipleTasks(Request $request){
        $requestData = null;
        $collection = $request->collection;
        $countCreator = Task::whereIn('id',$collection)->where('createdby',$request->user()->email)->count();
        $countAssignee = Task::whereIn('id',$collection)->where('assignee', $request->user()->email)->count();
        if($countCreator === sizeof($collection) && $countAssignee === sizeof($collection)){
            if($request->assignee !== null){
                $requestData['assignee'] = $request->assignee;
            }
            if($request->duedate !== null){
                $requestData['duedate'] = date('y-m-d H:i:s', strtotime($request->duedate));
            }
            if($request->status != null){
                $requestData['status'] = $request->status;
            }
            //return response($requestData);
        }
        else if($countCreator === sizeof($collection) && $countAssignee !== sizeof($collection)){
            if($request->assignee !== null){
                $requestData['assignee'] = $request->assignee;
            }
            if($request->duedate !== null){
                $requestData['duedate'] = date('y-m-d H:i:s', strtotime($request->duedate));
            }
        }
        else if($countAssignee === sizeof($collection) && $countCreator !== sizeof($collection)){
            if($request->status != null){
                $requestData['status'] = $request->status;
            }
        }
        else {
            return response('You have not selected correct tasks');
        }
        for($i =0; $i<sizeof($collection) ; $i++){
            $task = Task::findOrFail($collection[$i]);

            $notificationTo = null;
            if($task->assignee === $request->user()->email && $task->createdby !== $request->user()->email){
                $notificationTo = $task->createdby;
            }
            else if($task->createdby === $request->user()->email && $task->assignee !== $request->user()->email){
                $notificationTo = $task->assignee;
            }
            $notificationData = array();
            $notificationData['title'] = $task->title;
            $notificationData['byWhom'] = $request->user()->email;

            $task->update($requestData);

            if($notificationTo){
                $data = NotificationController::createNotification("taskUpdated", $notificationData, $notificationTo);
            }
        }
        return response("successfully updated all");

    }
    public function getSpecificColumns(Request $request){
        $collection = $request->collection;
        //$task = Task::whereIn('id',$collection)->select('assignee','createdby')->get();
        $countCreator = Task::whereIn('id',$collection)->where('createdby',$request->user()->email)->count();
        $countAssignee = Task::whereIn('id',$collection)->where('assignee', $request->user()->email)->count();
        $object = array();
        $object['assignee'] = $countAssignee;
        $object['creator'] = $countCreator;
        return response($object);
    }
    
    public function deleteMultipleTasks(Request $request){

        $collection = $request->collection;
        for($x = 0 ; $x<sizeof($collection) ; $x++){
            $task = Task::where('id',$collection[$x] );
            if(is_null($task->first())){
                return response("task not found");
            }
    
            $taskDetails = $task->get();
            $notificationTo = null;
            if($taskDetails[0]->assignee !== $request->user()->email)$notificationTo = $taskDetails[0]->assignee;
            $notificationData = array();
            $notificationData['title'] = $taskDetails[0]->title;
            $notificationData['byWhom'] = $request->user()->email;
    
            $requestData['delete']=0;
            $task->update($requestData);
            if($notificationTo)$data = NotificationController::createNotification("taskDeleted", $notificationData, $notificationTo);
            
        }

        return response("success");

      

    }
    public function getAllTasksStatusBased(Request $request){
        $email = $request->user()->email;
        $tasks = Task::where('delete',1);
        if($request->assignee){
            $tasks = $tasks->where('assignee',$request->assignee);
        }
        if($request->createdby){
            $tasks = $tasks->where('createdby', $request->createdby);
        }
        if($request->status){
            $tasks = $tasks->where('status', $request->status);
        }
        return response($tasks->get());
    }
    public function getAllTasksForPie(Request $request){
        $tasks = Task::where('delete',1)->get();
        $completedCount=0;
        $assignedCount=0;
        $inProgressCount=0;
        $deletedCount=0;
        for($x =0; $x<sizeof($tasks); $x++){
            if(strtolower($tasks[$x]->status) === 'completed' )$completedCount++;
            else if(strtolower($tasks[$x]->status) === 'deleted')$deletedCount++;
            else if(strtolower($tasks[$x]->status) === 'in-progress')$inProgressCount++;
            else if(strtolower($tasks[$x]->status) === 'assigned')$assignedCount++;
        };
        $data = array(
            array(
                "name" => "Completed",
                "y" => $completedCount,
                "color" => "#3498db",
                "attribute" => "Assigned"
            ),
            array(
                "name" => "Assigned",
                "y" => $assignedCount,
                "color" => "#9b59b6",
                "attribute" => "Assigned"
            ),
            array(
                "name" => "In-Progress",
                "y" => $inProgressCount,
                "color" => "#2ecc71",
                "attribute" => "Assigned"
            ),
            array(
                "name" => "Deleted",
                "y" => $deletedCount,
                "color" => "#f1c40f",
                "attribute" => "Assigned"
            )
            );
        $object= array(
            array(
                "name" => "All Tasks",
                "data" => $data
            )
            );
        return response($object);
    }

    public function getTasksMultipleFilters(Request $request){
        $email = $request->user()->email;
        $tasks = Task::where('delete',1)
                ->where(function ($query) use ($email){
                             $query->where('createdby', $email);
                             $query->orWhere('assignee', $email);
                });
        if($request->assignee){
            $tasks = $tasks->where('assignee',$request->assignee);
        }
        if($request->createdby){
            $tasks = $tasks->where('createdby', $request->createdby);
        }
        if($request->status){
            $tasks = $tasks->where('status', $request->status);
        }
        return response($tasks->get());
    }
    public function getAssignedToMe(Request $request){
        $email = $request->user()->email;
        $tasks = Task::where('delete',1)
                ->where(function ($query) use ($email){
                             $query->where('assignee', $email);
                })->get();
        $completedCount=0;
        $assignedCount=0;
        $inProgressCount=0;
        $deletedCount=0;
        for($x =0; $x<sizeof($tasks); $x++){
            if(strtolower($tasks[$x]->status) === 'completed' )$completedCount++;
            else if(strtolower($tasks[$x]->status) === 'deleted')$deletedCount++;
            else if(strtolower($tasks[$x]->status) === 'in-progress')$inProgressCount++;
            else if(strtolower($tasks[$x]->status) === 'assigned')$assignedCount++;
        };
        $data = array(
            array(
                "name" => "Completed",
                "y" => $completedCount,
                "color" => "#3498db",
                "attribute" => "Assigned"
            ),
            array(
                "name" => "Assigned",
                "y" => $assignedCount,
                "color" => "#9b59b6",
                "attribute" => "Assigned"
            ),
            array(
                "name" => "In-Progress",
                "y" => $inProgressCount,
                "color" => "#2ecc71",
                "attribute" => "Assigned"
            ),
            array(
                "name" => "Deleted",
                "y" => $deletedCount,
                "color" => "#f1c40f",
                "attribute" => "Assigned"
            )
            );
        $object= array(
            array(
                "name" => "Assigned-To-Me",
                "data" => $data
            )
            );
        // $object= array();
        // $object["data"] = $data;
        // $object["name"] = "Assigned-To-Me";
        return response($object);
    }
    public function getCreatedByMe(Request $request){
        $email = $request->user()->email;
        $tasks = Task::where('delete',1)
                ->where(function ($query) use ($email){
                             $query->where('createdby', $email);
                })->get();
        $completedCount=0;
        $assignedCount=0;
        $inProgressCount=0;
        $deletedCount=0;
        for($x =0; $x<sizeof($tasks); $x++){
            if(strtolower($tasks[$x]->status) === 'completed' )$completedCount++;
            else if(strtolower($tasks[$x]->status) === 'deleted')$deletedCount++;
            else if(strtolower($tasks[$x]->status) === 'in-progress')$inProgressCount++;
            else if(strtolower($tasks[$x]->status) === 'assigned')$assignedCount++;
        };
        $data = array(
            array(
                "name" => "Completed",
                "y" => $completedCount,
                "color" => "#3498db",
                "attribute" => "CreatedBy"
            ),
            array(
                "name" => "Assigned",
                "y" => $assignedCount,
                "color" => "#9b59b6",
                "attribute" => "CreatedBy"
            ),
            array(
                "name" => "In-Progress",
                "y" => $inProgressCount,
                "color" => "#2ecc71",
                "attribute" => "CreatedBy"
            ),
            array(
                "name" => "Deleted",
                "y" => $deletedCount,
                "color" => "#f1c40f",
                "attribute" => "CreatedBy"
            )
            );
        $object= array(
            array(
                "name" => "Created By Me",
                "data" => $data
            )
            );
        // $object= array();
        // $object["data"] = $data;
        // $object["name"] = "Assigned-To-Me";
        return response($object);
    }
   
    public function allTasks(Request $request){
        if($request->user()->role === 'Normal')return response("You don't have access to this route");
        //$tasks=Task::where([['createdby',$id],['delete',1]])->orWhere([['assignee',$id],['delete',1]])->get();
        $allTasks = Task::where([['delete',1]]);
        if($request->keyword){
            $searchTerm = $request->keyword;
            if($request->searchattribute){
                $allTasks = $allTasks->where($request->searchattribute, $searchTerm);
            }
            else{
            $allTasks = $allTasks->where(function ($query) use ($searchTerm){
                                    $query->where('title', 'LIKE', '%'.$searchTerm.'%');
                                    $query->orWhere('description', 'LIKE', '%'.$searchTerm.'%');
                                    $query->orWhere('assignee', 'LIKE', '%'.$searchTerm.'%');
                                    $query->orwhere('createdby', 'LIKE', '%'.$searchTerm.'%');
                                    $query->orWhere('status', 'LIKE', '%'.$searchTerm.'%');
                                });
            }
        }
        if($request->attribute){
            if(strtolower($request->order) == 'ascending')$allTasks = $allTasks->orderBy($request->attribute, 'asc');
            else if(strtolower($request->order) == 'descending')$allTasks = $allTasks->orderBy($request->attribute, 'desc');
            else $allTasks = $allTasks->orderBy($request->attribute, 'asc');
        }
        $allTasks = $allTasks->get();
        if($request->temp){
            return response($allTasks);
        }
        $size = sizeof($allTasks);
        $final = array();
        $i = $request->startindex;
        $n = $request->noofrecords;
        for ($x = $i; $x<($i+$n) && $x<$size ; $x++){
            $temp = array();
            $temp['id']= $allTasks[$x]->id;
            $temp['title']= $allTasks[$x]->title;
            $temp['description']= $allTasks[$x]->description;
            $temp['assignee']= $allTasks[$x]->assignee;
            $temp['createdby']= $allTasks[$x]->createdby;
            $temp['duedate']= $allTasks[$x]->duedate;
            $temp['status']= $allTasks[$x]->status;
            array_push($final,$temp);
        }
        $object = array();
        $object["data"] = $final;
        $object["length"] = $size;
        return response($object);
        //return response($allTasks);
    }
    public function oneTask(Request $request){
        return Task::where('delete',1)->where('id',$request->id)->get();
    }
    public function getTasks(Request $request){

        $email = $request->user()->email;
        $tasks = null;
        $tasks = Task::where('delete',1)
                ->where(function ($query) use ($email){
                             $query->where('createdby', $email);
                             $query->orWhere('assignee', $email);
                });

        if($request->keyword){
            $searchTerm = $request->keyword;
            if($request->searchattribute){
                $tasks = $tasks->where($request->searchattribute, $searchTerm);
            }
            else{
            $tasks = $tasks->where(function ($query) use ($searchTerm){
                                    $query->where('title', 'LIKE', '%'.$searchTerm.'%');
                                    $query->orWhere('description', 'LIKE', '%'.$searchTerm.'%');
                                    $query->orWhere('assignee', 'LIKE', '%'.$searchTerm.'%');
                                    $query->orwhere('createdby', 'LIKE', '%'.$searchTerm.'%');
                                    $query->orWhere('status', 'LIKE', '%'.$searchTerm.'%');
                                });
                            }
        }

        if($request->attribute){
            if(strtolower($request->order) == 'ascending')$tasks = $tasks->orderBy($request->attribute, 'asc');
            else if(strtolower($request->order) == 'descending')$tasks = $tasks->orderBy($request->attribute, 'desc');
            else $tasks = $tasks->orderBy($request->attribute, 'asc');
        }
        //dd('hi');
        $tasks = $tasks->get();
        //dd('hello');
        if($request->temp){
            return response($tasks);
        }
        $size = sizeof($tasks);
        $final = array();
        $i = $request->startindex;
        $n = $request->noofrecords;
        for ($x = $i; $x<($i+$n) && $x<$size ; $x++){
            $temp = array();
            $temp['id']= $tasks[$x]->id;
            $temp['title']= $tasks[$x]->title;
            $temp['description']= $tasks[$x]->description;
            $temp['assignee']= $tasks[$x]->assignee;
            $temp['createdby']= $tasks[$x]->createdby;
            $temp['duedate']= $tasks[$x]->duedate;
            $temp['status']= $tasks[$x]->status;
            array_push($final,$temp);
        }
        
        $object = array();
        $object["data"] = $final;
        $object["length"] = $size;
        return response($object);
    }

    public function createTasks(Request $request){
        if($request->user()->role === 'Normal'){
            if($request->assignee !== $request->user()->email)return response("Normal user can only create self tasks.");
        }
        if($request->createdby!== $request->user()->email)return response("createdby coloum should be your email only");
        $requestData = $request->all();
        $this->validate($request, [
            'title' => 'required|min:5|max:50',
            'description' => 'required|min:10|max:500',
            'assignee' => 'required',
            'createdby' => 'required',
            'duedate' => 'required',
            'status' => 'required',
        ]);
        $requestData['duedate']=date('y-m-d H:i:s', strtotime($requestData['duedate']));
        if(!$request->delete){

            $requestData['delete'] =1;

        }

        $notificationTo = null;
        if($request->assignee !== $request->user()->email)$notificationTo = $request->assignee;
        $notificationData = array();
        $notificationData['title'] = $request->title;
        $notificationData['byWhom'] = $request->user()->email;


        $task = Task::create($requestData);
        $task->update($requestData);

        if($notificationTo)$data = NotificationController::createNotification("taskCreated", $notificationData, $notificationTo);
        return response("Task Added");
    }

    public function deleteTasks(Request $request){
        $task = Task::where('id',$request->id );
        if(is_null($task->first())){
            return response("task not found");
        }

        $taskDetails = $task->get();
        $notificationTo = null;
        if($taskDetails[0]->assignee !== $request->user()->email)$notificationTo = $taskDetails[0]->assignee;
        $notificationData = array();
        $notificationData['title'] = $taskDetails[0]->title;
        $notificationData['byWhom'] = $request->user()->email;

        $requestData['delete']=0;
        $task->update($requestData);
        if($notificationTo)$data = NotificationController::createNotification("taskDeleted", $notificationData, $notificationTo);
        return response('Task Deleted', 200);
    }

    public function updateTasks(Request $request){
        $id = $request->id;
        $requestData = $request->all();
        $task = Task::findOrFail($id);

        $notificationTo = null;
        if($task->assignee === $request->user()->email && $task->createdby !== $request->user()->email){
            $notificationTo = $task->createdby;
        }
        else if($task->createdby === $request->user()->email && $task->assignee !== $request->user()->email){
            $notificationTo = $task->assignee;
        }
        $notificationData = array();
        $notificationData['title'] = $task->title;
        $notificationData['byWhom'] = $request->user()->email;


        $this->validate($request, [
            'title' => 'min:5|max:50',
            'description' => 'min:10|max:500',
        ]);
        if($request->duedate){
            $requestData['duedate']=date('y-m-d H:i:s', strtotime($requestData['duedate']));
        }
        $task->update($requestData);
        if($notificationTo){
            $data = NotificationController::createNotification("taskUpdated", $notificationData, $notificationTo);
        }
        return response('updated',200);
    }
}
