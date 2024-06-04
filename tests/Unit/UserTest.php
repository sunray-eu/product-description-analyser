<?php
namespace Tests\Unit;

use Tests\TestCase;
use SunrayEu\ProductDescriptionAnalyser\App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_a_name()
    {
        $user = User::factory()->create(['name' => 'John Doe']);

        $this->assertEquals('John Doe', $user->name);
    }

    /** @test */
    public function it_has_an_email()
    {
        $user = User::factory()->create(['email' => 'john@example.com']);

        $this->assertEquals('john@example.com', $user->email);
    }

    /** @test */
    public function it_has_a_password()
    {
        $user = User::factory()->create(['password' => 'secret']);
        $this->assertTrue(Hash::check('secret', $user->password));
    }
}
