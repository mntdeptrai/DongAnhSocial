<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PasswordOtp;
use App\Mail\SendPasswordOtpMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_error_message_is_correct()
    {
        // Create a user
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
            'phone' => '0901234567',
            'status' => 'active',
        ]);

        // Try to log in with wrong password
        $response = $this->post('/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'email hoặc mật khẩu không chính xác !',
        ]);
    }

    public function test_cannot_send_forgot_password_otp_to_nonexistent_email()
    {
        Mail::fake();

        $response = $this->postJson('/auth/forgot-password/send-otp', [
            'email' => 'nonexistent@example.com',
        ]);

        // Bảo mật: Server luôn trả về 200 với message trung tính để tránh email enumeration
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Nhưng KHÔNG gửi mail vì email không tồn tại
        Mail::assertNotSent(SendPasswordOtpMail::class);
    }

    public function test_can_send_forgot_password_otp_to_existing_email()
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

        $response = $this->postJson('/auth/forgot-password/send-otp', [
            'email' => 'existing@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            // Message trung tính (không tiết lộ email có tồn tại hay không)
        ]);

        $this->assertDatabaseHas('password_otps', [
            'email' => 'existing@example.com',
        ]);

        Mail::assertSent(SendPasswordOtpMail::class, function ($mail) {
            return $mail->hasTo('existing@example.com');
        });
    }

    public function test_cannot_reset_password_with_incorrect_otp()
    {
        // Create user
        $user = User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('oldpassword'),
            'role' => 'user',
            'phone' => '0901234567',
            'status' => 'active',
        ]);

        // Create correct OTP in database
        PasswordOtp::create([
            'email' => 'user@example.com',
            'otp' => '111111',
            'expires_at' => now()->addMinutes(4),
        ]);

        $response = $this->post('/auth/forgot-password/reset', [
            'email' => 'user@example.com',
            'otp' => '222222', // wrong OTP
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('otp');
        $this->assertTrue(Hash::check('oldpassword', $user->fresh()->password));
    }

    public function test_can_reset_password_with_correct_otp()
    {
        // Create user
        $user = User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('oldpassword'),
            'role' => 'user',
            'phone' => '0901234567',
            'status' => 'active',
        ]);

        // Create correct OTP in database
        PasswordOtp::create([
            'email' => 'user@example.com',
            'otp' => '111111',
            'expires_at' => now()->addMinutes(4),
        ]);

        $response = $this->post('/auth/forgot-password/reset', [
            'email' => 'user@example.com',
            'otp' => '111111', // correct OTP
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect('/auth/login');
        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));

        // OTP is marked as used
        $this->assertNotNull(PasswordOtp::where('email', 'user@example.com')->where('otp', '111111')->first()->used_at);
    }
}
