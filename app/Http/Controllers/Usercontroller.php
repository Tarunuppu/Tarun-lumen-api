<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{   
    public function sizeofdatabase(Request $request){
        $allRecords = DB::table('users')->get();
        return response(sizeof($allRecords));
    }
    public function getpartofusers(Request $request){
        $allRecords = DB::table('users')->get();
        $size = sizeof($allRecords);
        //dd(sizeof($allRecords));
        $final = array();
        $i = $request->startindex;
        $n = $request->noofrecords;
        for ($x = $i; $x<($i+$n) && $x<$size ; $x++){
            $temp = array();
            $temp['id']= $allRecords[$x]->id;
            $temp['name']= $allRecords[$x]->name;
            $temp['email']= $allRecords[$x]->email;
            $temp['role']= $allRecords[$x]->role;
            $temp['created_by']= $allRecords[$x]->created_by;
            array_push($final,$temp);
        }
        
        return response($final);
        //$final=json_encode($final);
        //dd($final[0]['id']);
        //return array_slice($allRecords->get(),$request->startindex,$request->noofrecords);
    }
    public function showAllAuthors(Request $request)
    {
        //dd("hi");
        //dd($request);
        $finalRecords = DB::table('users');
        if($request->name){
            $finalRecords = $finalRecords->where('name', $request->name);
        }
        if($request->email){
            $finalRecords = $finalRecords->where('email', $request->email);
        }
        if($request->role){
            switch(strtolower($request->role)){
                case 'admin':
                    $finalRecords = $finalRecords->where('role','admin');
                    break;
                case 'normal':
                    $finalRecords = $finalRecords->where('role','normal');
                    break;
                default:
                    break;
            }
              
        }
        if($request->created_by){
            $finalRecords = $finalRecords->where('created_by', $request->created_by);
        }
        if($request->deleted_by){
            $finalRecords = $finalRecords->where('deleted_by', $request->deleted_by);
        }
        if($request->deleted){
            switch(strtolower($request->deleted)){
                case 'yes':
                    $finalRecords = $finalRecords->where('deleted', 'yes');
                    break;
                case 'no':
                    $finalRecords = $finalRecords->where('deleted', 'no');
                    break;
                default:
                    break;
            }
        }
        if($request->attribute){
            if(strtolower($request->order) == 'ascending')$finalRecords = $finalRecords->orderBy($request->attribute, 'asc');
            else if(strtolower($request->order) == 'descending')$finalRecords = $finalRecords->orderBy($request->attribute, 'desc');
            else $finalRecords = $finalRecords->orderBy($request->attribute, 'asc');
        }
        if($request->noofrecords){
            $finalRecords = $finalRecords->get();
            $size = sizeof($finalRecords);
            //dd(sizeof($allRecords));
            $final = array();
            $i = $request->startindex;
            $n = $request->noofrecords;
            for ($x = $i; $x<($i+$n) && $x<$size ; $x++){
                $temp = array();
                $temp['id']= $finalRecords[$x]->id;
                $temp['name']= $finalRecords[$x]->name;
                $temp['email']= $finalRecords[$x]->email;
                $temp['role']= $finalRecords[$x]->role;
                $temp['created_by']= $finalRecords[$x]->created_by;
                array_push($final,$temp);
            }
            $object = array();
            $object["data"] = $final;
            $object["length"] = $size;
            return response($object);
        }
        $final = $finalRecords->get();
        $object = array();
        $object["data"] = $final;
        $object["length"] = sizeof($final);
        return response($object);
        //return response()->json(User::all());
    }

    public function showOneAuthor($id)
    {
        return response()->json(User::find($id));
    }

    public function create(Request $request)
    {
        #$author = User::create($request->all());

        #return response()->json($author, 201);
        $requestData = $request->all();
        $this->validate($request, [
            'name' => 'bail|required|min:5|max:50',
            'email' => 'bail|required|email|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',              // must be at least 10 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ],
        ]);
        if (!$request->role || strtoupper($request->role) != 'ADMIN') {
            $requestData['role'] = 'Normal';
        } else {
            $requestData['role'] = 'Admin';
        }
        if (!$request->created_by) {
            $requestData['created_by'] = NULL;
        }
        if (!$request->deleted_by) {
            $requestData['deleted_by'] = NULL;
        }
        if(!$request->role){
            $opts = ["cost" => 15, "salt" => "saltrandom080820221116"];
            $requestData['password'] = password_hash($requestData['password'], PASSWORD_BCRYPT, $opts);
        }
        $user = User::create($requestData);
        $user->update($requestData);

        //$userinstance = User::where('email', $request->email)->first();
        if($request->role){
            $user->sendEmailWithPassword();
            $opts = ["cost" => 15, "salt" => "saltrandom080820221116"];
            $requestData['password'] = password_hash($requestData['password'], PASSWORD_BCRYPT, $opts);
            $user->update($requestData);
            return response('registered, successfully sent');
        }
        //return response('registered, successfully sent');
        return response()->json($user, 201);

    }

    public function update(Request $request)
    {
        #$author = User::findOrFail($id);
        #$author->update($request->all());

        #return response()->json($author, 200);
        $id=$request->user()->id;
        //dd($user);
        //dd($request);
        $requestData = $request->all();
        $user = User::findOrFail($id);
        $this->validate($request, [
            'name' => 'min:5|max:50',
        ]);
        if ($request['email'] && $request['email'] != $user->email) {
            $this->validate($request, [
                'email' => 'bail|email|unique:users',
            ]);
        }
        unset($requestData['password']);
        unset($requestData['role']);
        // Console.log($user);
        $user->update($requestData);
        //return response()->json($user, 200);
        return response('updated',200);
    }

    public function delete(Request $request)
    {
        //dd($request->user()->id);
        //dd($request->user()->role === 'Normal');
        // if($request->user()->role=== 'Normal'){
        //     User::findOrFail($request->user()->id)->delete();
        //     return response('Deleted Successfully', 200);
        // }
        // else {
        //     // dd($request);
        //     User::findOrFail($request->id)->delete();
        //     return response('Deleted Successfully', 200);
        // }
        User::findOrFail($request->user()->id)->delete();
        return response('Deleted Successfully', 200);
    }
    public function deleteuser(Request $request){
        //dd(is_null($request->id));
        if($request->id){
            User::findOrFail($request->id)->delete();
            return response('Deleted Successfully', 200);
        }
        else{
            //return response()->json(User::all());
            return response(User::all());
        }
    }
    public function passwordChange(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
            'newpassword' => 'required|string'
        ]);

        $credentials = $request->only(['email', 'password']);
        if (!Auth::attempt($credentials)){
           return response('Invalid current password',200); 
        }
        $this->validate($request, [
            'newpassword' => [
                'required',
                'string',
                'min:8',             // must be at least 10 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ],
        ]);
        $opts = ["cost" => 15, "salt" => "saltrandom080820221116"];
        $requestData['password'] = password_hash($request->newpassword, PASSWORD_BCRYPT, $opts);
        #$requestData['password'] = $request->password;
        $user = User::findOrFail($request->user()->id);
        $user->update($requestData);
        //return response()->json($user, 200);
        return response("password updated",200);
    }
}