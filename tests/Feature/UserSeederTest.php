<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Uji pembuatan akun administrator awal.
 *
 * Seeder harus aman dijalankan berulang kali dan tidak boleh membuat akun
 * dengan email kosong ketika ADMIN_EMAIL tidak diisi di .env.
 */
class UserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_kosong_memakai_alamat_admin_bawaan(): void
    {
        config([
            'app.admin.email' => '',
            'app.admin.password' => 'Rahasia#123',
        ]);

        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@pu.go.id')->first();

        $this->assertNotNull($admin);
        $this->assertSame('admin', $admin->role);
        $this->assertTrue($admin->is_active);
        $this->assertTrue(Hash::check('Rahasia#123', $admin->password));
    }

    public function test_kata_sandi_admin_yang_sudah_ada_tidak_diubah(): void
    {
        $admin = User::create([
            'name' => 'Admin Lama',
            'nip' => '199001012026999999',
            'jabatan' => 'Administrator',
            'email' => 'admin@pu.go.id',
            'password' => Hash::make('Lama#12345'),
            'role' => 'admin',
        ]);

        config([
            'app.admin.email' => 'admin@pu.go.id',
            'app.admin.password' => 'Baru#12345',
        ]);

        $this->seed(UserSeeder::class);

        $this->assertSame(1, User::where('email', 'admin@pu.go.id')->count());
        $this->assertTrue(Hash::check('Lama#12345', $admin->fresh()->password));
    }
}
