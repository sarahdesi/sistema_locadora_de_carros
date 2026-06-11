<?php

namespace App\Http\Controllers;

use App\Models\LogAtividade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LogAtividadeController extends Controller
{
    public function index()
    {
        Gate::authorize('is-gerente');

        
        $logs = LogAtividade::with('usuario')->latest()->paginate(20);

        return view('logs.index', compact('logs'));
    }
}
