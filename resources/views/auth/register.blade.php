@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Cadastro inicial</div>

                <div class="card-body">
                    @if (session('registration_success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('registration_success') }}
                        </div>
                    @endif

                    @if (session('google_registration'))
                        <div class="alert alert-info" role="alert">
                            Conta Google autorizada. Complete os dados da empresa para finalizar o cadastro.
                        </div>
                    @else
                        <div class="mb-4">
                            <a href="{{ route('google.redirect', ['intent' => 'registration']) }}" class="btn btn-outline-danger btn-sm">
                                Cadastrar com Google
                            </a>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div id="registration-step-1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Dados da empresa</h5>
                                <span class="badge badge-primary">Etapa 1 de 2</span>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="company_name" class="col-md-4 col-form-label text-md-right">Nome da empresa</label>

                                <div class="col-md-6">
                                    <input id="company_name" type="text" class="form-control @error('company_name') is-invalid @enderror" name="company_name" value="{{ old('company_name') }}" required maxlength="255" autocomplete="organization" autofocus>

                                    @error('company_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="phone" class="col-md-4 col-form-label text-md-right">Telefone</label>

                                <div class="col-md-6">
                                    <input id="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" required maxlength="25" autocomplete="tel" placeholder="(11) 99999-9999">

                                    @error('phone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="cpf_cnpj" class="col-md-4 col-form-label text-md-right">CPF/CNPJ</label>

                                <div class="col-md-6">
                                    <input id="cpf_cnpj" type="text" class="form-control @error('cpf_cnpj') is-invalid @enderror" name="cpf_cnpj" value="{{ old('cpf_cnpj') }}" required maxlength="18" aria-describedby="cpf-cnpj-help">
                                    <small id="cpf-cnpj-help" class="form-text text-muted">Informe o CPF ou CNPJ, com ou sem pontuação.</small>

                                    @error('cpf_cnpj')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button id="registration-next" type="button" class="btn btn-primary btn-sm text-white">
                                        Avançar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="registration-step-2" class="d-none">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Dados do usuário administrador</h5>
                                <span class="badge badge-primary">Etapa 2 de 2</span>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>

                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', data_get(session('google_registration'), 'name')) }}" required maxlength="255" autocomplete="name">

                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', data_get(session('google_registration'), 'email')) }}" required maxlength="255" autocomplete="email" @if(session('google_registration')) readonly @endif>

                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            @unless(session('google_registration'))
                            <div class="form-group row mb-3">
                                <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required minlength="8" autocomplete="new-password">

                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            @endunless

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button id="registration-back" type="button" class="btn btn-outline-secondary btn-sm mr-2">
                                        Voltar
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-sm text-white">
                                        Realizar cadastro
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var companyInput = document.getElementById('company_name');
        var firstStep = document.getElementById('registration-step-1');
        var companyInputs = firstStep.querySelectorAll('input');
        var secondStep = document.getElementById('registration-step-2');
        var nextButton = document.getElementById('registration-next');
        var backButton = document.getElementById('registration-back');
        var hasUserErrors = @json(
            ! $errors->has('company_name') &&
            ! $errors->has('phone') &&
            ! $errors->has('cpf_cnpj') &&
            ($errors->has('name') || $errors->has('email') || $errors->has('password'))
        );

        function showStep(step) {
            var showFirstStep = step === 1;

            firstStep.classList.toggle('d-none', !showFirstStep);
            secondStep.classList.toggle('d-none', showFirstStep);

            if (showFirstStep) {
                companyInput.focus();
            } else {
                document.getElementById('name').focus();
            }
        }

        nextButton.addEventListener('click', function () {
            for (var index = 0; index < companyInputs.length; index++) {
                if (!companyInputs[index].checkValidity()) {
                    companyInputs[index].reportValidity();
                    return;
                }
            }

            showStep(2);
        });

        backButton.addEventListener('click', function () {
            showStep(1);
        });

        if (hasUserErrors) {
            showStep(2);
        }
    });
</script>
@endpush
