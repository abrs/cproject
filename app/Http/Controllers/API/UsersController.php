<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Image;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\User;

class UsersController extends Controller
{

    #--------------------CRUD--------------------------------

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        #get all users of the system.
        return response()->json([
            'users' => User::all()
        ], 200);
    }

    #----------------------------------------------------

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'full_name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed',
            'image' => 'image|file|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        /**
         * store an image while signing up a new user ""or"" don't post the image 
         * then update the user with existing images using api\posted-images as a get
        */
        if($requestingImage = $request->has('image')) {
            $uploadedImage = $this->uploadImage($request);
        }

        $user = new User([

            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'image' => $requestingImage ? $uploadedImage['image_url'] : null,

        ]);

        $user->save();

        return response()->json([
            'message' => 'Successfully created user!',
            'uploaded_image' => $requestingImage ? $uploadedImage : '',
        ], 201);
    }

    #----------------------------------------------------

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return response()->json(['user' => $user], 200);
    }

    #----------------------------------------------------

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $validator = \Validator::make($request->all(), [
            'full_name' => 'string',
            'email' => 'string|email|unique:users',
            'password' => 'string|confirmed',
            // 'image' => 'image|file|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $user->full_name = $request->has('full_name') ? $request->full_name : $user->full_name;
        $user->email = $request->has('email') ? $request->email : $user->email;
        $user->password = $request->has('password') ? $request->password : bcrypt($user->password);
        $user->image = $request->has('image') ? $request->image : $user->image;
        $user->save();

        return response()->json([
            'message' => 'user updated successfully!'
        ], 200);
    }

    #----------------------------------------------------

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $oldUser = $user;
        $user->delete();

        return response()->json([
                'oldUser'    => $oldUser,
                'message'    => "user deleted successfully."
        ], 200);
    }

    #----------------------Other-functions-----------------------------

    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function login(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $credentials = request(['email', 'password']);

        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);

        $user = $request->user();

        $tokenResult = $user->createToken('Personal Access Token');

        $token = $tokenResult->token;

        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);

        $token->save();

        return response()->json([

            'access_token' => $tokenResult->accessToken,
            'token_type'   => 'Bearer',
            'expires_at'   => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()

        ]);
    }
  
    #----------------------------------------------------

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
  
    #----------------------------------------------------

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    #----------------------------------------------------

    /**
     * upload an image to use it as a user image later while updating the user
     */
    public function uploadImage(Request $request) {

        $validator = \Validator::make($request->all(), [
            'image' => 'required|image:jpeg,png,jpg,gif,svg|file|max:2048'
        ]);

        if ($validator->fails()) {
            return $request->json($validator->messages()->first(), 500);
        }
        
        $uploadFolder = 'users';

        $image = $request->file('image');

        $image_uploaded_path = $image->store($uploadFolder, 'public');

        $uploadedImageResponse = [
            "image_name" => basename($image_uploaded_path),
            "image_url" => \Storage::disk('public')->url($image_uploaded_path),
            "mime" => $image->getClientMimeType()
        ];

        Image::create([
            'image_name' =>$uploadedImageResponse['image_name'],
            'image_url' =>$uploadedImageResponse['image_url'],
            'mime' =>$uploadedImageResponse['mime'],
        ]);

        return $uploadedImageResponse;
    
    }

}
