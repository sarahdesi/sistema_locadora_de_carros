@if(session('sucesso'))
    <div class="p-4 mb-4 text-green-800 bg-green-100 rounded-lg">
        {{ session('sucesso') }}
    </div>
@endif

@if(session('erro'))
    <div class="p-4 mb-4 text-red-800 bg-red-100 rounded-lg">
        {{ session('erro') }}
    </div>
@endif