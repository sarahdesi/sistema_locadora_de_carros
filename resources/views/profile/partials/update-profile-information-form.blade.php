<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        {{-- 1. CAMPO: NOME --}}
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        {{-- 2. CAMPO: EMAIL --}}
        <div>
            <x-input-label for="login" :value="__('Email')" />
            <x-text-input id="login" name="login" type="email" class="mt-1 block w-full" :value="old('login', $user->login)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('login')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- 📞 3. NOVO CAMPO: TELEFONE --}}
        <div>
            <x-input-label for="telefone" :value="__('Telefone')" />
            <x-text-input id="telefone" name="telefone" type="text" class="mt-1 block w-full" :value="old('telefone', $user->telefone)" placeholder="(00) 00000-0000" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('telefone')" />
        </div>

        {{-- 📅 4. NOVO CAMPO: VALIDADE DA CNH (Aparece apenas para clientes) --}}
        @if($user->role_id == 3 || $user->role === 'cliente')
            <div>
                <x-input-label for="validade_cnh" :value="__('Validade da CNH')" />
                <x-text-input id="validade_cnh" name="validade_cnh" type="date" class="mt-1 block w-full" 
                    :value="old('validade_cnh', $user->validade_cnh ? \Carbon\Carbon::parse($user->validade_cnh)->format('Y-m-d') : '')" />
                <x-input-error class="mt-2" :messages="$errors->get('validade_cnh')" />
            </div>
        @endif

        {{-- BOTÃO DE SALVAR --}}
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>