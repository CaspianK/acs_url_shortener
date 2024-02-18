<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="Link",
 *     type="object",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="url", type="string", example="https://example.com"),
 *     @OA\Property(property="short_token", type="string", example="abc123"),
 *     @OA\Property(property="is_private", type="boolean", example="true"),
 *     @OA\Property(property="private_token", type="string", example="abc123")
 * )
 */

class LinkController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/links",
     *     tags={"Links"},
     *     summary="Список ссылок пользователя",
     *     operationId="getLinks",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Успешное получение списка ссылок",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="links",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Link")
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Пользователь не авторизован",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated."),
     *         ),
     *     )
     * )
     */
    public function index(): Response
    {
        /** @var User $user */
        $user = Auth()->user();

        $links = $user->links;

        return response([
            'links' => $links
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/links",
     *     tags={"Links"},
     *     summary="Создание новой ссылки",
     *     operationId="storeLink",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные ссылки",
     *         @OA\JsonContent(
     *             required={"url"},
     *             @OA\Property(property="url", type="string", example="https://example.com"),
     *             @OA\Property(property="short_token", type="string", example="abc123", description="Короткий токен ссылки. Создается автоматически если не передан. Является уникальным для пользователя."),
     *             @OA\Property(property="is_private", type="boolean", example=false, description="Флаг приватности. По умолчанию false."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Ссылка успешно создана",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="link",
     *                 ref="#/components/schemas/Link"
     *             ),
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
     *                     property="url",
     *                     type="array",
     *                     @OA\Items(type="string", example="The url field is required."),
     *                 ),
     *                 @OA\Property(
     *                     property="short_token",
     *                     type="array",
     *                     @OA\Items(type="string", example="The short token has already been taken."),
     *                 ),
     *             ),
     *         ),
     *     )
     * )
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'url' => 'required|string|max:255',
            'short_token' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('links')->where(function ($query) {
                    return $query->where('user_id', Auth()->user()->getAuthIdentifier());
                }),
            ],
            'is_private' => 'nullable|boolean'
        ]);

        $link = Link::create([
            'user_id' => Auth()->user()->getAuthIdentifier(),
            'url' => $request->url,
            'short_token' => $request->short_token ?? Str::random(5),
            'is_private' => $request->is_private ?? false
        ]);

        if ($link->is_private) {
            $link->update([
                'private_token' => Str::random(10)
            ]);
        }

        return response([
            'link' => $link
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/links/{link}",
     *     tags={"Links"},
     *     summary="Возвращает информацию о ссылке пользователя",
     *     operationId="showLink",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="link",
     *         in="path",
     *         required=true,
     *         description="ID ссылки",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное получение информации о ссылке",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                  property="link",
     *                  ref="#/components/schemas/Link"
     *              ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="У пользователя нет прав на просмотр этого контента. Пользователь может просматривать только свои ссылки.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have permissions to view this content.")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ссылка не найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not Found.")
     *         ),
     *     )
     * )
     */
    public function show(Link $link): Response
    {
        if ($link->user_id !== Auth()->user()->getAuthIdentifier()) {
            return response(['message' => 'You do not have permissions to view this content.'], 403);
        }

        return response([
            'link' => $link
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/{user}/{token}",
     *     tags={"Links"},
     *     summary="Переадресация",
     *     operationId="redirect",
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID пользователя",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true,
     *         description="Короткий токен ссылки",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="private_token",
     *         in="query",
     *         required=false,
     *         description="Токен для приватных ссылок",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешная переадресация",
     *         @OA\JsonContent(
     *             @OA\Property(property="url", type="string", example="https://example.com"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Запрет доступа к содержимому",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have permissions to view this content."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ссылка не найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model."),
     *         ),
     *     )
     * )
     */
    public function redirect(User $user, String $token, Request $request): Response
    {
        $link = Link::whereShortToken($token)->firstOrFail();

        if ($link->is_private) {
            $privateToken = $request->query('private_token');

            if ($privateToken !== $link->private_token) {
                return response(['message' => 'You do not have permissions to view this content.'], 403);
            }
        }

        return response([
            'url' => $link->url
        ]);
    }
}
