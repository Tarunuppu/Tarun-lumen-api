<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    
    public function showAllAuthors(Request $request)
    {
        //dd("hi");
        //dd($request);
        if($request->role){
            //dd('hi');
            switch(strtolower($request->role)){
                case 'admin':
                    return response()->json(DB::table('users')->where('role', 'admin')->get());
                    break;
                case 'normal':
                    //dd('hello');
                    return response()->json(DB::table('users')->where('role', 'normal')->get());
                    break;
                default:
                    break;
            }
              
        }
        if($request->created_by){
            return response()->json(DB::table('users')->where('created_by', $request->created_by)->get());
        }
        if($request->deleted_by){
            return response()->json(DB::table('users')->where('deleted_by', $request->deleted_by)->get());
        }
        if($request->deleted){
            switch(strtolower($request->deleted)){
                case 'yes':
                    return response()->json(DB::table('users')->where('deleted', 'yes')->get());
                    break;
                case 'no':
                    return response()->json(DB::table('users')->where('deleted', 'no')->get());
                    break;
                default:
                    break;
            }
        }
        //dd("hi");
        if($request->attribute){
            switch(strtolower($request->attribute)){
                case 'id':
                    if(strtolower($request->order)=='asc')return response()->json(DB::table('users')->orderBy('id', 'asc')->get());
                    else if(strtolower($request->order)=='desc')return response()->json(DB::table('users')->orderBy('id', 'desc')->get());
                    else return response()->json(DB::table('users')->orderBy('id', 'asc')->get());
                    break;
                case 'name':
                    if(strtolower($request->order)=='asc')return response()->json(DB::table('users')->orderBy('name', 'asc')->get());
                    else if(strtolower($request->order)=='desc')return response()->json(DB::table('users')->orderBy('name', 'desc')->get());
                    else return response()->json(DB::table('users')->orderBy('name', 'asc')->get());
                    break;
                case 'email':
                    if(strtolower($request->order)=='asc')return response()->json(DB::table('users')->orderBy('email', 'asc')->get());
                    else if(strtolower($request->order)=='desc')return response()->json(DB::table('users')->orderBy('email', 'desc')->get());
                    else return response()->json(DB::table('users')->orderBy('email', 'asc')->get());
                    break;
                case 'created_at':
                    if(strtolower($request->order)=='asc')return response()->json(DB::table('users')->orderBy('created_at', 'asc')->get());
                    else if(strtolower($request->order)=='desc')return response()->json(DB::table('users')->orderBy('created_at', 'desc')->get());
                    else return response()->json(DB::table('users')->orderBy('created_at', 'asc')->get());
                    break;
                case 'created_by':
                    if(strtolower($request->order)=='asc')return response()->json(DB::table('users')->orderBy('created_by', 'asc')->get());
                    else if(strtolower($request->order)=='desc')return response()->json(DB::table('users')->orderBy('created_by', 'desc')->get());
                    else return response()->json(DB::table('users')->orderBy('created_by', 'asc')->get());
                    break;
                case 'deleted_by':
                    if(strtolower($request->order)=='asc')return response()->json(DB::table('users')->orderBy('deleted_by', 'asc')->get());
                    else if(strtolower($request->order)=='desc')return response()->json(DB::table('users')->orderBy('deleted_by', 'desc')->get());
                    else return response()->json(DB::table('users')->orderBy('deleted_by', 'asc')->get());
                    break;
                case 'role':
                    if(strtolower($request->order)=='admin')return response()->json(DB::table('users')->orderBy('role', 'asc')->get());
                    else if(strtolower($request->order)=='normal')return response()->json(DB::table('users')->orderBy('role', 'desc')->get());
                    else return response()->json(DB::table('users')->orderBy('role', 'asc')->get());
                    break;
                case 'deleted':
                    if(strtolower($request->order)=='yes')return response()->json(DB::table('users')->orderBy('deleted', 'desc')->get());
                    else if(strtolower($request->order)=='no')return response()->json(DB::table('users')->orderBy('deleted', 'asc')->get());
                    else return response()->json(DB::table('users')->orderBy('deleted', 'desc')->get());
                    break;
                
            }
        }
        return response()->json(User::all());
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
        $opts = ["cost" => 15, "salt" => "saltrandom080820221116"];
        $requestData['password'] = password_hash($requestData['password'], PASSWORD_BCRYPT, $opts);
        // return $requestData;
        //echo($requestData);
        $user = User::create($requestData);
        //echo("hello");
        if (!$request->created_by) {
            $requestData['created_by'] = $user->id;
        }
        $user->update($requestData);
        return response()->json($user, 201);

    }

    public function update($id, Request $request)
    {
        #$author = User::findOrFail($id);
        #$author->update($request->all());

        #return response()->json($author, 200);
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
        return response()->json($user, 200);
    }

    public function delete($id)
    {
        User::findOrFail($id)->delete();
        return response('Deleted Successfully', 200);
    }
    public function passwordChange($id, Request $request)
    {
        $this->validate($request, [
            'password' => [
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
        $requestData['password'] = password_hash($request->password, PASSWORD_BCRYPT, $opts);
        #$requestData['password'] = $request->password;
        $user = User::findOrFail($id);
        $user->update($requestData);
        return response()->json($user, 200);
    }
}