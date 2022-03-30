<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use Illuminate\Http\Request;
use App\Http\Resources\AgendamentoResource;

class AgendamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $agendamento = Agendamento::all();

        return AgendamentoResource::collection($agendamento);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        // obtem a data de hoje para comparar com a do agendamento
        $hoje = date("d-m-Y");
        $request->merge(['hoje' => $hoje]);

        // obtem o usuário logado que vai realizar o agendamento
        $usuario = $request->user()->id;
        $request->merge(['user_id' => $usuario]);
        
        // valida as informações vindas do request
        $validacao_dados = $request->validate([
            'nome' => 'required|max:255',
            'data_agendamento' => 'required|date|after:hoje',
            'nome_rua' => 'required|max:255',
            'num_rua' => 'required',
            'user_id' => 'required',
        ]);

        // cria um nome agendamento
        $agendamento = Agendamento::create($validacao_dados);

        //retorna um json do agendamento criado
        return new AgendamentoResource($agendamento);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Agendamento  $agendamento
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $usuario = $usuario = $request->user()->id;
        $agendados = Agendamento::where('user_id', $usuario)->where('id', $id)->get();

        if (count($agendados) <= 1) {
            return new AgendamentoResource($agendados);
        } else {
            return AgendamentoResource::collection($agendados);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Agendamento  $agendamento
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Agendamento $agendamento)
    {
        $usuario = $request->user()->id;
        $request->merge(['user_id' => $usuario]);

        $validacao_dados = $request->validate([
            'nome' => 'max:255',
            'data_agendamento' => 'date|after:today',
            'nome_rua' => 'max:255',
            'num_rua' => 'string',
            'user_id' => 'required',
        ]);

        $agendamento->with('user_id');
        if($agendamento->user_id != (string)$usuario){
            return response()->json(['message' => 'Erro ao tentar atualizar!'], 403);
        }

        $agendamento->update($validacao_dados);

        return new AgendamentoResource($agendamento); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Agendamento  $agendamento
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Agendamento $agendamento)
    {
        $usuario = $request->user()->id;
        $agendamento->with('user_id');
        if($agendamento->user_id != (string)$usuario){
            return response()->json(['message' => 'Erro ao tentar deletar!'], 403);
        }

        $agendamento->delete();

        return response()->json(['message' => 'Usuário deletado com sucesso!',
                                'usuario' => new AgendamentoResource($agendamento)]); 
    }
}
