<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\TenantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = parent::createApplication();
        $app->instance('env', 'testing');
        $app['config']->set([
            'database.default' => 'sqlite',
            'database.connections.sqlite.url' => null,
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => true,
            'cache.default' => 'array',
            'session.driver' => 'array',
            'app.key' => 'base64:'.base64_encode(str_repeat('a', 32)),
            'hashing.bcrypt.rounds' => 4,
        ]);

        return $app;
    }

    public function test_registration_screen_can_be_rendered()
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('name="company_name"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('name="cpf_cnpj"', false);
    }

    /** @dataProvider validCompanyContacts */
    public function test_company_and_administrator_are_registered_with_normalized_contacts($phone, $document, $storedPhone, $storedDocument)
    {
        $this->seed([TenantSeeder::class, PermissionSeeder::class]);

        $response = $this->post(route('register'), $this->registrationData([
            'phone' => $phone,
            'cpf_cnpj' => $document,
            'tenant_id' => Tenant::where('slug', 'tenant-padrao')->value('id'),
        ]));

        $response->assertSessionHasNoErrors()->assertRedirect();

        $tenant = Tenant::where('slug', 'empresa-teste')->firstOrFail();
        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->assertSame('Empresa Teste', $tenant->name);
        $this->assertSame($storedPhone, $tenant->phone);
        $this->assertSame($storedDocument, $tenant->cpf_cnpj);
        $this->assertTrue($tenant->active);
        $this->assertEquals($tenant->id, $user->tenant_id);
        $this->assertEquals($tenant->id, $user->role->tenant_id);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertDatabaseCount('tenants', 2);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('role_permission', 3);

        $location = $response->headers->get('Location');
        $token = basename(parse_url($location, PHP_URL_PATH));
        $handoff = Cache::get('tenant-login-handoff:'.hash('sha256', $token));

        $this->assertStringContainsString('/auth/handoff/', $location);
        $this->assertTrue(URL::hasValidSignature(Request::create($location)));
        $this->assertSame($user->id, $handoff['user_id']);
        $this->assertSame($tenant->slug, $handoff['tenant_slug']);
        $this->assertGuest();
    }

    public function validCompanyContacts()
    {
        return [
            'CPF com pontuação' => ['(11) 99999-9999', '529.982.247-25', '11999999999', '52998224725'],
            'CPF com zero inicial' => ['1133334444', '01234567890', '1133334444', '01234567890'],
            'CNPJ com pontuação e DDI' => ['+55 (11) 99999-9999', '04.252.011/0001-10', '5511999999999', '04252011000110'],
            'CNPJ sem pontuação' => ['11999999999', '04252011000110', '11999999999', '04252011000110'],
            'CNPJ alfanumérico' => ['11999999999', '12.ABC.345/01DE-35', '11999999999', '12ABC34501DE35'],
            'CNPJ alfanumérico em minúsculas' => ['11999999999', '12abc34501de35', '11999999999', '12ABC34501DE35'],
        ];
    }

    /** @dataProvider invalidCompanyContacts */
    public function test_invalid_company_contacts_do_not_create_records($field, $value)
    {
        $this->postJson(route('register'), $this->registrationData([$field => $value]))
            ->assertStatus(422)
            ->assertJsonValidationErrors($field);

        $this->assertDatabaseCount('tenants', 0);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('roles', 0);
        $this->assertDatabaseCount('role_permission', 0);
    }

    public function invalidCompanyContacts()
    {
        return [
            'telefone ausente' => ['phone', null],
            'telefone vazio' => ['phone', ''],
            'telefone sem DDD' => ['phone', '99999-9999'],
            'telefone longo' => ['phone', '1234567890123456'],
            'telefone com letras' => ['phone', '11abc999999999'],
            'telefone como array' => ['phone', ['11999999999']],
            'documento ausente' => ['cpf_cnpj', null],
            'documento vazio' => ['cpf_cnpj', ''],
            'documento curto' => ['cpf_cnpj', '123456'],
            'documento longo' => ['cpf_cnpj', '123456789012345'],
            'CPF com dígito inválido' => ['cpf_cnpj', '529.982.247-24'],
            'CPF com dígitos repetidos' => ['cpf_cnpj', '111.111.111-11'],
            'CNPJ com dígito inválido' => ['cpf_cnpj', '04.252.011/0001-11'],
            'CNPJ zerado' => ['cpf_cnpj', '00.000.000/0000-00'],
            'CNPJ alfanumérico com dígito inválido' => ['cpf_cnpj', '12.ABC.345/01DE-34'],
            'CNPJ com símbolo inválido' => ['cpf_cnpj', '12.ABC.345/01@E-35'],
            'documento como array' => ['cpf_cnpj', ['52998224725']],
        ];
    }

    public function test_company_errors_are_displayed_and_entered_values_are_preserved()
    {
        $this->from(route('register'))
            ->post(route('register'), $this->registrationData([
                'phone' => '123',
                'email' => 'invalid',
            ]))
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['phone', 'email'])
            ->assertSessionHasInput('company_name', 'Empresa Teste')
            ->assertSessionHasInput('phone', '123')
            ->assertSessionHasInput('cpf_cnpj', '529.982.247-25');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Informe um telefone com DDD, contendo de 10 a 15 dígitos.')
            ->assertSee('value="123"', false)
            ->assertSee('value="529.982.247-25"', false)
            ->assertSee('var hasUserErrors = false;', false);
    }

    public function test_existing_tenants_can_remain_without_contact_data()
    {
        $this->seed(TenantSeeder::class);

        $this->assertDatabaseHas('tenants', [
            'slug' => 'tenant-padrao',
            'phone' => null,
            'cpf_cnpj' => null,
        ]);
    }

    private function registrationData(array $overrides = []): array
    {
        return array_merge([
            'company_name' => 'Empresa Teste',
            'phone' => '(11) 99999-9999',
            'cpf_cnpj' => '529.982.247-25',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ], $overrides);
    }
}
