<?php

namespace App\Http\Controllers\User;

use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Exception;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    use ResponseTrait;
    
    #[OA\Post(
        path: "/api/user/store",
        summary: "Create a new user",
        tags: ["Users"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "John Doe"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "john@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "secret123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "User created successfully"
            ),
            new OA\Response(
                response: 500,
                description: "Something went wrong"
            )
        ]
    )]
    public function storeUser(Request $request){

        try{
            $validate = $request->validate([
                "name"=>"required",
                "email"=>"required|email|unique:users,email",
                "password"=>"required|min:8|max:12"
            ]);

            $validate["password"] = Hash::make($validate['password']);
            
            $user = User::create($validate);

            return $this->successResponse($user,'User created successfully',201);

        }catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Validation failed',
                422
            );
        }catch(Exception $e){
            return $this->errorResponse([],'Something went wrong');
        }
    }
    
    
    #[OA\Post(
        path: '/api/user/login',
        summary: 'Login user',
        tags: ['Users', 'Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'rohit@mail.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'rohit123'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User logged in successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: true
                        ),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'token',
                                    type: 'string',
                                    example: '1|example-token'
                                ),
                                new OA\Property(
                                    property: 'token_type',
                                    type: 'string',
                                    example: 'Bearer'
                                ),
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Login successful'
                        ),
                        new OA\Property(
                            property: 'status',
                            type: 'integer',
                            example: 200
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid email or password'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation failed'
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error'
            ),
        ]
    )]
    
    public function login(Request $request){
        try{
            $validation=Validator::make($request->all(),[
                "email"=>"required|email",
                "password"=>"required",
            ]);

            if($validation->fails()){
                return $this->validationErrorResponse($validation->errors(),'Validation failed',422);
            }

            $user = User::where('email',$request->email)->first();
            if(!$user){
                return $this->errorResponse([],'User not found',404);
            }

            if(!Hash::check($request->password,$user->password)){
                return $this->errorResponse([],'Invalid credentials',401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            return $this->successResponse([
                'token' => $token,
                'token_type' => 'Bearer'],
                'Login successful',200);

        }catch(Exeception $e){
            return $this->errorResponse([],'failed to login please try again later',500);
        }
    }

    #[OA\GET(
        path: '/api/user/profile',
        summary: 'Get user profile',
        tags: ['Users', 'Auth'],
        security: [['sanctumToken' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User profile fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: true
                        ),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'id',
                                    type: 'integer',
                                    example: 1
                                ),
                                new OA\Property(
                                    property: 'name',
                                    type: 'string',
                                    example: 'John Doe'
                                ),
                                new OA\Property(
                                    property: 'email',
                                    type: 'string',
                                    format: 'email',
                                    example: 'user@example.com'
                                ),
                                new OA\Property(
                                    property: 'created_at',
                                    type: 'string',
                                    format: 'date-time',
                                    example: '2023-01-01T00:00:00.000000Z'
                                ),
                                new OA\Property(
                                    property: 'updated_at',
                                    type: 'string',
                                    format: 'date-time',
                                    example: '2023-01-01T00:00:00.000000Z'
                                ),
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Profile fetched successfully'
                        ),
                        new OA\Property(
                            property: 'status',
                            type: 'integer',
                            example: 200
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error'
            ),
        ]
    )]
    
    public function profile(){
        try{
            $user = Auth::user();
            return $this->successResponse($user,"Profile fetched successfully",200);
        }catch(Exeception $e){
            return $this->errorResponse([],'failed to get profile please try again later',500);
        }
    }

    #[OA\POST(
        path: '/api/user/logout',
        summary: 'User logout',
        tags: ['Users', 'Auth'],
        security: [['sanctumToken' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User logged out successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: true
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            example: []
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Logout successfully'
                        ),
                        new OA\Property(
                            property: 'status',
                            type: 'integer',
                            example: 200
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error'
            ),
        ]
    )]
    public function logout(){
        try{
            $user = Auth::user();
            $user->currentAccessToken()->delete();
            return $this->successResponse([],'Logout successfully',200);
        }catch(Exception $e){
            return $this->errorResponse([],'failed to logout please try again later',500);
        }
    }

}
