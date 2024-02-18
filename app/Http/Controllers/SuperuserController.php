<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SuperuserController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/users/create",
     *     tags={"Users"},
     *     summary="Создание нового пользователя",
     *     operationId="createUser",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные пользователя",
     *         @OA\JsonContent(
     *             required={"name", "username", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Пользователь успешно создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="2|abcdefghijk123456789"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="У пользователя нет прав на создание нового пользователя",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You are not authorized to create a new user"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Validation errors in your request"
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="The email has already been taken."),
     *                 ),
     *             ),
     *         ),
     *     )
     * )
     */
    public function createUser(Request $request): Response
    {
        /** @var User $user */
        $user = Auth()->user();

        if (!$user->tokenCan('create:user')) {
            return response([
                'message' => 'You are not authorized to create a new user'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $role = Role::whereTitle('user')->first();

        $user->roles()->attach($role);

        $token = $user->createToken('user', ['url:create'])->plainTextToken;

        return response([
            'token' => $token
        ], 201);
    }
}
