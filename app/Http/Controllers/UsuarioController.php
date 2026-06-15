<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Role;
use App\Models\LogAtividade;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::orderBy('name')->get();
        return view('users.index', compact('usuarios'));
    }

    
    public function create()
    {

        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $dados = $request->validate([
            'cpf'                 => 'required|string|max:11|unique:usuarios,cpf',
            'name'                => 'required|string|max:100',
            'data_nascimento'     => 'required|date',
            'telefone'            =>'required|string|max:11',
            'cnh'                 => 'required|string|size:9|unique:usuarios,cnh',
            'validade_cnh'    => 'nullable|date',
            'login'           => 'required|string|email|max:100|unique:usuarios,login',
            'password'        => 'required|string|min:6',
            'role_id'         => 'required|exists:roles,id',
           
        ]);
        
        $dados['password'] = bcrypt($dados['password']);

        // 1. IMPORTANTE: Altere de Veiculo::create para Usuario::create (senão vai dar erro na linha 47!)
        Usuario::create($dados);

        
        LogAtividade::create([
            'usuario_id' => auth()->id(),
            'acao'        => 'Cadastro de Usuário',
            'descricao'       => 'Cadastrou o usuário ' . $dados['name'] . ' (CPF: ' . $dados['cpf'] . ')',
        ]);

        return redirect()->route('users.index')
                         ->with('sucesso', 'Usuário cadastrado com sucesso!');
    }

    

    /**
     * Display the specified resource.
     */
    public function show(Usuario $usuario)
    {
         $usuario->load('contratos');
        return view('users.show', compact('usuario')); 
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Usuario $usuario)
    {
        $roles = Role::all();
        return view('users.edit', compact('usuario','roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Usuario $usuario) // Alterado de 'string $id' para 'Usuario $usuario'
    {
        $dados = $request->validate([
            'cpf'             => 'required|string|max:11|unique:usuarios,cpf,' . $usuario->id,
            'name'            => 'required|string|max:100',
            'data_nascimento' => 'required|date',
            'telefone'        => 'required|string|max:11',
            'cnh'             => 'nullable|string|max:9|unique:usuarios,cnh,' . $usuario->id,
            'validade_cnh'    => 'nullable|date',
            'login'           => 'required|string|email|max:100|unique:usuarios,login,' . $usuario->id,
            'password'        => 'nullable|string|min:6', // Senha opcional na edição
            'role_id'         => 'required|exists:roles,id',
        ]);

        // Se o usuário digitou uma nova senha, criptografa. Se não, mantém a antiga.
        if (!empty($dados['password'])) {
            $dados['password'] = bcrypt($dados['password']);
        } else {
            unset($dados['password']);
        }

        // Atualiza o objeto correto no banco
        $usuario->update($dados);

        // Registra no log de atividades
        LogAtividade::create([
            'usuario_id' => auth()->id(),
            'acao'        => 'Atualização',
            'descricao'        => 'Atualizou os dados do usuário ' . $usuario->name,
        ]);

        return redirect()->route('users.index')
                         ->with('sucesso', 'Usuário atualizado com sucesso!');
    }

    public function destroy(Usuario $usuario)
    {
        $cpf = $usuario->cpf;
        $usuario->delete();

        // Registra no log de atividades
        LogAtividade::create([
            'usuario_id' => auth()->id(),
            'acao'        => 'Remoção',
            'descricao'        => 'Removeu os dados do usuário ' . $usuario->name,
        ]);

        return redirect()->route('users.index')
                         ->with('sucesso', 'Usuário removido com sucesso!');
    }
}
