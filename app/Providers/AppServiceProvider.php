<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Usuario;  

class AppServiceProvider extends ServiceProvider
{

        //public function register(): void
        //{
            
        //}
    /*Gerente: Acesso total ao sistema, deve ser capaz desde realizar uma locação, cadastro de 
    veículo ou usuário até gerar relatórios gerenciais de faturamento e descritivos dos veículos*/

    /* Operador: O operador deve ser capaz de realizar cadastro de usuários, veículos e locação 
    (check-in, check-out) além de ter acesso a relatórios dos veículos*/ 

    /* Cliente: Deve ser capaz de realizar o proprio cadastro e solicitar a locação de veículos.*/

    //gate é o sistema de permissões do Laravel.
    public function boot(): void
    {
        //is-staff = operador ou gerente 
        Gate::define('is-staff', function($user){
            return in_array($user->role?->name, ['operador', 'gerente']);
        });

        //is-gerente = gerente apenas
        Gate::define('is-gerente', function($user){
            return $user->role->name === 'gerente';
        });
    }

}