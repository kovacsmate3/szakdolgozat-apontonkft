<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $authController;

    public function setUp(): void
    {
        parent::setUp();
        $this->authController = new AuthController();
    }

    #[Test]
    public function login_returns_error_for_invalid_credentials()
    {
        // Create a user with known password
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct-password')
        ]);

        // Create a request with wrong password
        $request = Request::create('/api/login', 'POST', [
            'identifier' => 'test@example.com',
            'password' => 'wrong-password'
        ]);

        // Call the login method
        $response = $this->authController->login($request);

        // Assert response status is unauthorized
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        // Assert the response contains the expected error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['status']);
        $this->assertEquals('Érvénytelen jelszó.', $responseData['message']);
    }

    #[Test]
    public function login_returns_error_for_nonexistent_email()
    {
        // Create a request with non-existent email
        $request = Request::create('/api/login', 'POST', [
            'identifier' => 'nonexistent@example.com',
            'password' => 'some-password'
        ]);

        // Call the login method
        $response = $this->authController->login($request);

        // Assert response status is unauthorized
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        // Assert the response contains the expected error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['status']);
        $this->assertEquals(['A megadott email címmel nem található felhasználó.'], $responseData['errors']['identifier']);
    }

    #[Test]
    public function login_returns_token_for_valid_credentials()
    {
        // Create a user with known password
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct-password')
        ]);

        // Create a request with correct credentials
        $request = Request::create('/api/login', 'POST', [
            'identifier' => 'test@example.com',
            'password' => 'correct-password'
        ]);

        // Call the login method
        $response = $this->authController->login($request);

        // Assert response status is OK
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains a token
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['status']);
        $this->assertEquals('Sikeres bejelentkezés.', $responseData['message']);
        $this->assertArrayHasKey('token', $responseData);
    }

    #[Test]
    public function profile_returns_error_for_invalid_token()
    {
        // Mock the Auth facade to return null (unauthenticated user)
        Auth::shouldReceive('user')
            ->once()
            ->andReturn(null);

        // Call the profile method
        $response = $this->authController->profile();

        // Assert response status is unauthorized
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        // Assert the response contains the expected error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['status']);
        $this->assertEquals('A token érvénytelen vagy lejárt. Kérjük, jelentkezz be újra.', $responseData['message']);
    }

    #[Test]
    public function profile_returns_user_data_for_valid_token()
    {
        // Create a user
        $user = User::factory()->create();

        // Mock the Auth facade to return our user
        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        // Call the profile method
        $response = $this->authController->profile();

        // Assert response status is OK
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the user data
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['status']);
        $this->assertEquals('Bejelentkezett felhasználó adatai.', $responseData['message']);
        $this->assertEquals($user->id, $responseData['data']['id']);
    }

    #[Test]
    public function logout_deletes_tokens_and_returns_success()
    {
        // Mock User osztály a tokens->delete metódussal
        $mockUser = Mockery::mock(User::class);
        $mockTokens = Mockery::mock();
        $mockTokens->shouldReceive('delete')->once();

        // A mockolt User objektum tokens() metódusa a mockolt tokens objektumot adja vissza
        $mockUser->shouldReceive('tokens')->once()->andReturn($mockTokens);

        // Auth::user() a mockolt User-t adja vissza
        Auth::shouldReceive('user')->once()->andReturn($mockUser);

        // Hívd meg a logout metódust
        $response = $this->authController->logout();

        // Assert response status is OK
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Assert the response contains the success message
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['status']);
        $this->assertEquals('Sikeres kijelentkezés.', $responseData['message']);
    }

    #[Test]
    public function logout_returns_error_for_invalid_token()
    {
        // Mock the Auth facade to return null (unauthenticated user)
        Auth::shouldReceive('user')
            ->once()
            ->andReturn(null);

        // Call the logout method
        $response = $this->authController->logout();

        // Assert response status is unauthorized
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        // Assert the response contains the expected error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['status']);
        $this->assertEquals('A token érvénytelen vagy lejárt. Kérjük, jelentkezz be újra.', $responseData['message']);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
