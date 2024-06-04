<?php
namespace Tests\Unit;

use Tests\TestCase;
use SunrayEu\ProductDescriptionAnalyser\App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_a_name()
    {
        $user = User::factory()->create(['name' => 'John Doe']);

        $this->assertEquals('John Doe', $user->name);
    }

    public function test_it_has_an_email()
    {
        $user = User::factory()->create(['email' => 'john@example.com']);

        $this->assertEquals('john@example.com', $user->email);
    }

    public function test_it_has_a_password()
    {
        $user = User::factory()->create(['password' => 'secret']);
        $this->assertTrue(Hash::check('secret', $user->password));
    }
}
