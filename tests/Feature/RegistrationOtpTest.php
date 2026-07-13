<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PasswordOtp;
use App\Mail\SendRegisterOtpMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_send_otp_to_existing_email()
    {
        Mail::fake();

        // Create an existing user
        User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
            'phone' => '0901234567',
            'status' => 'active',
        ]);

        $response = $this->postJson('/auth/register/send-otp', [
            'email' => 'existing@example.com',
        ]);

        // Bảo mật: Trả về 200 với message trung tính, không tiết lộ email tồn tại
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Nhưng KHÔNG gửi mail vì email đã tồn tại (user đã có tài khoản)
        Mail::assertNotSent(SendRegisterOtpMail::class);
    }

    public function test_can_send_otp_to_new_email()
    {
        Mail::fake();

        $response = $this->postJson('/auth/register/send-otp', [
            'email' => 'newuser@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            // Message trung tính theo chuẩn anti-enumeration
        ]);

        // Assert OTP was created in database
        $this->assertDatabaseHas('password_otps', [
            'email' => 'newuser@example.com',
        ]);

        Mail::assertSent(SendRegisterOtpMail::class, function ($mail) {
            return $mail->hasTo('newuser@example.com');
        });
    }

    public function test_cannot_register_without_otp()
    {
        $response = $this->post('/auth/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'phone' => '0901234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ]);

        $response->assertSessionHasErrors('otp');
    }

    public function test_cannot_register_with_incorrect_otp()
    {
        // Store an OTP
        PasswordOtp::create([
            'email' => 'newuser@example.com',
            'otp' => '123456',
            'expires_at' => now()->addMinutes(4),
        ]);

        $response = $this->post('/auth/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'phone' => '0901234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'otp' => '654321', // wrong OTP
        ]);

        $response->assertSessionHasErrors('otp');
    }

    public function test_can_register_with_correct_otp()
    {
        // Store correct OTP
        PasswordOtp::create([
            'email' => 'newuser@example.com',
            'otp' => '123456',
            'expires_at' => now()->addMinutes(4),
        ]);

        $response = $this->post('/auth/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'phone' => '0901234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'otp' => '123456', // correct OTP
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);
    }
}
