<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeFeature extends Command
{
    protected $signature = 'make:feature {name : Nome singular da funcionalidade, por exemplo Produto}';

    protected $description = 'Cria os arquivos de backend e frontend para um CRUD';

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle()
    {
        $name = Str::studly(Str::singular($this->argument('name')));

        if ($name === '') {
            $this->error('Informe um nome válido para a funcionalidade.');
            return 1;
        }

        $replacements = [
            '{{ class }}' => $name,
            '{{ classes }}' => Str::studly(Str::plural($name)),
            '{{ variable }}' => Str::camel($name),
            '{{ variables }}' => Str::camel(Str::plural($name)),
            '{{ label }}' => Str::lower(Str::snake($name, ' ')),
            '{{ labels }}' => Str::lower(Str::snake(Str::plural($name), ' ')),
            '{{ route }}' => Str::kebab(Str::plural($name)),
            '{{ routeVariable }}' => Str::camel(Str::plural($name)).'Routes',
        ];

        $files = [
            app_path("Repositories/{$name}Repository.php") => 'repository.stub',
            app_path("Services/{$name}Service.php") => 'service.stub',
            app_path("Http/Controllers/{$name}Controller.php") => 'controller.stub',
            app_path("Http/Requests/Store{$name}Request.php") => 'store-request.stub',
            app_path("Http/Requests/Update{$name}Request.php") => 'update-request.stub',
            resource_path('js/pages/'.Str::studly(Str::plural($name)).'.vue') => 'index-page.stub',
            resource_path("js/pages/Create{$name}.vue") => 'create-page.stub',
            resource_path("js/pages/Edit{$name}.vue") => 'edit-page.stub',
            resource_path('js/router/'.Str::kebab(Str::plural($name)).'.js') => 'routes.stub',
        ];

        $existing = array_filter(array_keys($files), function ($path) {
            return $this->files->exists($path);
        });

        if ($existing) {
            $this->error('A funcionalidade não foi criada porque estes arquivos já existem:');
            foreach ($existing as $path) {
                $this->line(' - '.str_replace(base_path().DIRECTORY_SEPARATOR, '', $path));
            }
            return 1;
        }

        $routerIndexPath = resource_path('js/router/index.js');
        $routerIndex = $this->buildRouterIndex(
            $routerIndexPath,
            $replacements['{{ route }}'],
            $replacements['{{ routeVariable }}']
        );

        if ($routerIndex === null) {
            return 1;
        }

        $apiRoutesPath = base_path('routes/api.php');
        $apiRoutes = $this->buildApiRoutes(
            $apiRoutesPath,
            $name,
            $replacements['{{ route }}']
        );

        if ($apiRoutes === null) {
            return 1;
        }

        foreach ($files as $path => $stub) {
            $this->files->ensureDirectoryExists(dirname($path));
            $contents = $this->files->get(__DIR__."/stubs/{$stub}");
            $this->files->put($path, str_replace(array_keys($replacements), array_values($replacements), $contents));
            $this->info('Criado: '.str_replace(base_path().DIRECTORY_SEPARATOR, '', $path));
        }

        $this->files->put($routerIndexPath, $routerIndex);
        $this->info('Atualizado: resources/js/router/index.js');

        $this->files->put($apiRoutesPath, $apiRoutes);
        $this->info('Atualizado: routes/api.php');

        $this->newLine();
        $this->comment("Feature {$name} criada com sucesso.");

        return 0;
    }

    private function buildRouterIndex($path, $route, $routeVariable)
    {
        if (! $this->files->exists($path)) {
            $this->error('NÃ£o foi possÃ­vel registrar as rotas: resources/js/router/index.js nÃ£o existe.');
            return null;
        }

        $contents = $this->files->get($path);
        $import = "import {$routeVariable} from './{$route}';";

        if (Str::contains($contents, $import) || Str::contains($contents, "...{$routeVariable}")) {
            $this->error("As rotas de frontend para {$route} jÃ¡ estÃ£o registradas.");
            return null;
        }

        $lastImportPosition = strrpos($contents, "import ");
        $lastImportEnd = $lastImportPosition === false
            ? false
            : strpos($contents, ';', $lastImportPosition);

        if ($lastImportEnd === false) {
            $this->error('NÃ£o foi possÃ­vel localizar os imports em resources/js/router/index.js.');
            return null;
        }

        $contents = substr_replace($contents, PHP_EOL.$import, $lastImportEnd + 1, 0);

        if (! preg_match('/(routes\s*:\s*\[)([\s\S]*?)(\n\s*\])/', $contents, $matches, PREG_OFFSET_CAPTURE)) {
            $this->error('NÃ£o foi possÃ­vel localizar a lista de rotas em resources/js/router/index.js.');
            return null;
        }

        $routes = rtrim($matches[2][0]);
        $routes = rtrim($routes, ',').','.PHP_EOL."        ...{$routeVariable}";
        $start = $matches[2][1];

        return substr_replace($contents, $routes, $start, strlen($matches[2][0]));
    }

    private function buildApiRoutes($path, $name, $route)
    {
        if (! $this->files->exists($path)) {
            $this->error('NÃ£o foi possÃ­vel registrar as rotas: routes/api.php nÃ£o existe.');
            return null;
        }

        $contents = $this->files->get($path);
        $controllerImport = "use App\\Http\\Controllers\\{$name}Controller;";
        $routePrefix = "Route::prefix('{$route}')->group(function () {";

        if (Str::contains($contents, $controllerImport) || Str::contains($contents, $routePrefix)) {
            $this->error("As rotas de API para {$route} jÃ¡ estÃ£o registradas.");
            return null;
        }

        $routeFacadeImport = 'use Illuminate\\Support\\Facades\\Route;';
        $routeFacadePosition = strpos($contents, $routeFacadeImport);

        if ($routeFacadePosition === false) {
            $this->error('NÃ£o foi possÃ­vel localizar os imports em routes/api.php.');
            return null;
        }

        $contents = substr_replace(
            $contents,
            $controllerImport.PHP_EOL,
            $routeFacadePosition,
            0
        );

        $middlewareEnd = strrpos($contents, '});');

        if ($middlewareEnd === false) {
            $this->error('NÃ£o foi possÃ­vel localizar o grupo auth:sanctum em routes/api.php.');
            return null;
        }

        $routes = implode(PHP_EOL, [
            "    Route::prefix('{$route}')->group(function () {",
            "        Route::get('/list', [{$name}Controller::class, 'index'])->name('{$route}.index');",
            "        Route::post('/store', [{$name}Controller::class, 'store'])->name('{$route}.store');",
            "        Route::get('/find/{id}', [{$name}Controller::class, 'show'])->whereNumber('id')->name('{$route}.show');",
            "        Route::put('/update/{id}', [{$name}Controller::class, 'update'])->whereNumber('id')->name('{$route}.update');",
            "        Route::delete('/delete/{id}', [{$name}Controller::class, 'destroy'])->whereNumber('id')->name('{$route}.delete');",
            '    });',
        ]);

        return substr_replace(
            $contents,
            $routes.PHP_EOL,
            $middlewareEnd,
            0
        );
    }
}
