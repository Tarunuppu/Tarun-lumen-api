<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    
    public function showAllAuthors()
    {
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