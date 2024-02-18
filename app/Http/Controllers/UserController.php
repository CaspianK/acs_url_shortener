<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Вход пользователя",
     *     operationId="login",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные пользователя",
     *         @OA\JsonContent(
     *             required={"username","password"},
     *             @OA\Property(property="username", type="string", example="user_name1"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный вход",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="1|abcdefghijk123456789"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The password you have provided is incorrect."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="password",
     *                     type="array",
     *                     @OA\Items(type="string", example="The password you have provided is incorrect.")
     *                 ),
     *             ),
     *         ),
     *     ),
     *     security={{"apiAuth": {}}}
     * )
     * @throws ValidationException
     */
    public function login(Request $request): Response
    {
        $request->validate([
            'username' => 'required|string|exists:users',
            'password' => 'required'
        ]);

        $user = User::whereUsername($request->username)->first();

        $abilities = [];

        if ($user->hasRole('super')) {
            $abilities[] = 'create:user';
        }

        if ($user->hasRole('user')) {
            $abilities[] = 'create:url';
        }

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'The password you have provided is incorrect.'
            ]);
        }

        $token = $user->createToken('user', $abilities)->plainTextToken;

        return response([
            'token' => $token
        ], 200);
    }
}
