<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use  App\Models\User;
use config;
#use App\Usersecond;
class AuthController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'forgetPassword','forgetPassword-EmailVerification','emailVerify']]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */
    public function login(Request $request)
    {

        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        //dd($credentials);
        //dd($token);
        return $this->respondWithToken($token);
    }

     /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }
    public function emailRequestVerification(Request $request)
    {
        //dd($request->user());
        if ( $request->user()->hasVerifiedEmail() ) {
            return response()->json('Email address is already verified.');
        }
    
        $request->user()->sendEmailVerificationNotification();
    
        return response()->json('Email request verification sent to '. Auth::user()->email);
    }
    public function emailVerify(Request $request)
    {
        $this->validate($request, [
        'token' => 'required|string',
        ]);
        //dd($request->token);
        /*
        $tokenParts = explode('.', $request->token);
        //dd($tokenParts);
	    
        $header = base64_decode($tokenParts[0]);
        //dd($tokenParts[1]);
	    $payload = base64_decode($tokenParts[1]);
	    $signature_provided = $tokenParts[2];
        $expiration = json_decode($payload)->exp;
	    $is_token_expired = ($expiration - time()) < 0;
        $base64_url_header = self::base64url_encode($header);
	    $base64_url_payload = self::base64url_encode($payload);
	    $signature = hash_hmac('SHA256', $base64_url_header . "." . $base64_url_payload, config('env_variables.JWT_SECRET'), true);
	    $base64_url_signature = self::base64url_encode($signature);
        $is_signature_valid = ($base64_url_signature === $signature_provided);
	
	    if ($is_token_expired || !$is_signature_valid) {
		    return response()->json('Invalid email verify token', 401);;
	    }
        */
        
        //\Tymon\JWTAuth\Facades\JWTAuth::getToken());
        \Tymon\JWTAuth\Facades\JWTAuth::getToken();
        //dd(\Tymon\JWTAuth\Facades\JWTAuth::parseToken());
        \Tymon\JWTAuth\Facades\JWTAuth::parseToken()->authenticate();
        if ( ! $request->user() ) {
            return response()->json('Invalid token', 401);
        }
      
        if ( $request->user()->hasVerifiedEmail() ) {
            return response()->json('Email address '.$request->user()->getEmailForVerification().' is already verified.');
        }
        $request->user()->markEmailAsVerified();
        return response()->json('Email address '. $request->user()->email.' successfully verified.');
    }
    public function forgetPassword(Request $request)
    {
        #$user = User::find($request->email);
        $user = User::where('email', $request->email)->first();
        if(is_null($user)){
            return response()->json('Your email is not found in my database');
        }
        $user->sendEmailVerificationForgetPassword();
        return response()->json('Email request verification sent to '. $request->email);
    }
    public function forgetPassword_EmailVerification(Request $request){
        $this->validate($request, [
        'token' => 'required|string',
        ]);
        \Tymon\JWTAuth\Facades\JWTAuth::getToken();
        \Tymon\JWTAuth\Facades\JWTAuth::parseToken()->authenticate();
        if ( ! $request->user() ) {
            return response()->json('Invalid token', 401);
        }

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
        $user = $request->user();
        $user->update($requestData);
        return response()->json($user, 200);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /*
    public function showAllAuthors()
    {
        return response()->json(Usersecond::all());
    }

    public function showOneAuthor($id)
    {
        return response()->json(Usersecond::find($id));
    }

    public function create(Request $request)
    {
        $user = Usersecond::create($request->all());

        return response()->json($user, 201);
    }

    public function update($id, Request $request)
    {
        $user = Usersecond::findOrFail($id);
        $user->update($request->all());

        return response()->json($user, 200);
    }

    public function delete($id)
    {
        Usersecond::findOrFail($id)->delete();
        return response('Deleted Successfully', 200);
    }
    */
    /**
  * Request an email verification email to be sent.
  *
  * @param  Request  $request
  * @return Response
  */
  /*
    protected function base64url_encode($str) {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }*/
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user(),
            'expires_in' => auth()->factory()->getTTL() * 60 * 24
        ]);
    }
}