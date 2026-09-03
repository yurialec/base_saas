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

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div id="registration-step-1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Dados da empresa</h5>
                                <span class="badge badge-primary">Etapa 1 de 2</span>
                            </div>

                            <div class="form-group row">
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

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button id="registration-next" type="button" class="btn btn-primary">
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

                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>

                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name">

                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email">

                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
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

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button id="registration-back" type="button" class="btn btn-outline-secondary mr-2">
                                        Voltar
                                    </button>
                                    <button type="submit" class="btn btn-primary">
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
        var secondStep = document.getElementById('registration-step-2');
        var nextButton = document.getElementById('registration-next');
        var backButton = document.getElementById('registration-back');
        var hasUserErrors = @json(
            ! $errors->has('company_name') &&
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
            if (companyInput.checkValidity()) {
                showStep(2);
                return;
            }

            companyInput.reportValidity();
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
