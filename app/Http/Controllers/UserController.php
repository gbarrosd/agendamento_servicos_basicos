<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       $usuario = User::all();

       return UserResource::collection($usuario);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validacao_dados = $request->validate([
            'name' => 'required|max:255|string',
            'email' => 'required|max:255|string|unique:users',
            'password' => 'required|string|min:4|confirmed',
            ],
            [   
            'email.unique' => 'O email digitado já existe',
            'password.confirmed' => 'As senhas digitadas não conferem',
            'password.min' => 'A senha deve conter no minimo 4 caracteres',
            ]   
        );
        $validacao_dados['password'] = bcrypt($validacao_dados['password']);

        $usuario = User::create($validacao_dados);

        return new UserResource($usuario);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $usuario)
    {
        return new UserResource($usuario);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $usuario)
    {
        
        $validacao_dados = $request->validate([
            'name' => 'max:255|string',
            'email' => 'max:255|string|unique:users',
            'password' => 'string|min:4|confirmed',
            ],
            [   
            'email.unique' => 'O email digitado já existe',
            'password.confirmed' => 'As senhas digitadas não conferem',
            'password.min' => 'A senha deve conter no minimo 4 caracteres',
            ]   
        );

        if (isset($validacao_dados['password'])) {
            $validacao_dados['password'] = bcrypt($validacao_dados['password']);
        }
        
        $usuario->update($validacao_dados);

        return new UserResource($usuario);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $usuario)
    {
        if($usuario->delete()){

            return response()->json(['message' => 'Usuário deletado com sucesso!',
                                     'usuario' => new UserResource($usuario)]);
        } else {
            return response()->json(['message' => 'Erro ao excluir usuário!']);
        }
    }
}
