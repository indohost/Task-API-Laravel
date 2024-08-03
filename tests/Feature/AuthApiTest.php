<?php

namespace Tests\Feature;

use App\Models\User;
use Faker\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    private $endpointRegister = '/api/auth/register';
    private $endpointLogin = '/api/auth/login';

    private function dataUser(): array
    {
        $faker = Factory::create();
        $email = $faker->unique()->safeEmail;

        $dataUser = [
            'name' => 'Test Syarif',
            'email' => $email,
            'password' => 'password123',
            'is_test' => true,
        ];

        return $dataUser;
    }

    /**
     * @test
     * 
     * Given: Data pengguna yang valid.
     * When: Mengirimkan permintaan POST ke endpoint /api/auth/register untuk membuat pengguna.
     * Then: Memastikan bahwa respons memiliki kode status 201 (Created), data pengguna ada di database, dan password telah di-hash.
     */
    public function it_registers_a_user_with_valid_data(): void
    {
        // Given we have valid registration data
        $dataUser = $this->dataUser();

        // When we register a new user
        $responseUser = $this->postJson($this->endpointRegister, $dataUser);

        // Then the response should have a status code of 201
        $responseUser->assertStatus(201);

        // And the user should be in the database
        $this->assertDatabaseHas('users', [
            'name' => $dataUser['name'],
            'email' => $dataUser['email'],
            'is_test' => true,
        ]);

        // And the password should be hashed
        $user = User::where('email', $dataUser['email'])
            ->where('is_test', true)
            ->first();

        $this->assertTrue(Hash::check($dataUser['password'], $user->password));
    }

    /**
     * @test
     * 
     * Given: Data registrasi dengan password yang tidak valid.
     * When: Mengirimkan permintaan POST ke endpoint /api/auth/register dengan data tersebut.
     * Then: Memeriksa bahwa status respons adalah 422 (Unprocessable Entity).
     */
    public function it_fails_to_register_user_with_invalid_data(): void
    {
        // Given we have invalid registration data
        $dataUser = $this->dataUser();
        $dataUser['password'] = null; // Invalid password

        // When we try to register a new user
        $responseUser = $this->postJson($this->endpointRegister, $dataUser);

        // Then the response should have an error status
        $responseUser->assertStatus(422);
    }

    /**
     * @test
     * 
     *Given: Menyiapkan pengguna yang ada dengan kredensial yang valid.
     *When: Mengirimkan permintaan POST ke endpoint /api/auth/login dengan kredensial yang valid.
     *Then: Memeriksa bahwa status respons adalah 200 (OK) dan bahwa respons mengandung token autentikasi.
     */
    public function it_logs_in_a_user_with_valid_credentials(): void
    {
        // Given we have an existing user
        $dataUser = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // When we log in with valid credentials
        $responseUser = $this->postJson($this->endpointLogin, [
            'email' => $dataUser->email,
            'password' => 'password123',
        ]);

        // Then the response should have a success status
        $responseUser->assertStatus(200);

        // And the response should contain a token
        $responseUser->assertJsonStructure([
            "status",
            "message",
            "authorization" => [
                "type",
                "token"
            ],
            "user" => [
                "id",
                "name",
                "email",
                "email_verified_at",
                "photo_path",
                "created_at",
                "updated_at",
                "deleted_at",
            ]
        ]);
    }

    /**
     * @test
     * 
     *Given: Menyiapkan pengguna yang ada dengan kredensial yang valid.
     *When: Mengirimkan permintaan POST ke endpoint /api/auth/login dengan kredensial yang salah.
     *Then: Memeriksa bahwa status respons adalah 401 (Unauthorized).
     */
    public function it_fails_to_log_in_with_invalid_credentials()
    {
        // Given we have an existing user
        $dataUser = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // When we try to log in with invalid credentials
        $response = $this->postJson($this->endpointLogin, [
            'email' => $dataUser->email,
            'password' => 'wrongpassword',
        ]);

        // Then the response should have an error status
        $response->assertStatus(401);
    }
}
