<?php

namespace App\Http\Controllers;

use App\Models\Favorites;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $user = User::find($id);
        $additionalInfo = DB::select('select age, phone_number from additional_infos where user_id = ?', [$id]);

        return response(['user' => $user, 'additionalInfo' => $additionalInfo, 'status' => Response::HTTP_OK]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'email' => ['required', 'email'],
            'password' => 'required',
            'address' => 'required',
            'city' => 'required',
            'birthdate' => 'required|date',
        ]);

        $emailExist = User::where('email', $request->email)->first();

        if ($emailExist) {
            return response(['mesage' => 'El correo ingresado ya se encuentra registrado'], 409);
        }
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password =  Hash::make($request->password); // encriptacion de la contraseña
        $user->address = $request->address;
        $user->city = $request->city;
        $user->birthdate = $request->birthdate;

        $user->save();
        $user_id = $user->id;
        DB::insert('INSERT INTO additional_infos (user_id) VALUES (?)', [$user_id]);


        return response()->json(['message' => 'Usuario creado con exito', 'status' => Response::HTTP_CREATED]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        if (Auth::attempt($credentials)) {
            $user = $request->user();
            $token = $user->createToken('token')->plainTextToken;
            $cookie = cookie('cookie_token', $token, 60 * 24);
            return response(["token" => $token, "id" => $user['id']], Response::HTTP_OK)->withoutCookie($cookie);
        } else {
            return response(["message" => "Credenciales inválidas"], Response::HTTP_UNAUTHORIZED);
        }
    }

    public function myFavorites($id)
    {
        $userFavorites = Favorites::where('user_id', $id)->get();
        if (count($userFavorites) == 0) {
            return response(['userFavorites' => $userFavorites, 'message' => 'No tienes favoritos', 'status' => Response::HTTP_OK]);
        }
        return response(['userFavorites' => $userFavorites, Response::HTTP_OK]);
    }

    public function addFavorite(Request $request)
    {
        $userFount = User::find($request->user_id);
        if (!$userFount) {
            return response(["message" => "Usuario no encontrado", 'status' => Response::HTTP_NOT_FOUND]);
        }
        $newFav = new Favorites();
        $newFav->ref_api = $request->ref_api;
        $newFav->user_id = $request->user_id;

        $newFav->save();
        return response(["message" => "Se agrego a sus favoritos", 'status' => Response::HTTP_OK]);
    }

    public function removeFavorite($id)
    {
        $favorite = Favorites::find($id);
        if (!$favorite) {
            return response(["message" => "Favorito no encontrado", 'status' => Response::HTTP_NOT_FOUND]);
        }
        $favorite->delete();
        return  response(["message" => "Se elimino con exito", 'status' => Response::HTTP_OK]);
    }

    public function updateAditionalInfo(Request $request)
    {
        $id = $request->id;
        $age = $request->age;
        $phone_number = $request->phone_number;
        $update = DB::update('UPDATE additional_infos set age = ?, phone_number = ? where user_id = ?', [$age, $phone_number, $id]);

        if ($update == 1) {
            return response(['message' => 'Se actualizo con exito', 'status' => Response::HTTP_OK]);
        } else {
            return response(['message' => 'Sin cambios', 'status' => Response::HTTP_NOT_MODIFIED]);
        }
    }
}
