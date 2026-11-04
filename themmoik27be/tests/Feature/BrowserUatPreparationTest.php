<?php

namespace Tests\Feature;

use App\Models\KhachHang;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Support\IsolatedApiTestCase;

class BrowserUatPreparationTest extends IsolatedApiTestCase
{
    public function test_export_disposable_fixture_for_browser(): void
    {
        $directory = getenv('SOFEP_UAT_DIR');
        if (!$directory) {
            $this->markTestSkipped('Optional browser fixture export requires SOFEP_UAT_DIR.');
        }
        $directory = realpath($directory);
        $this->assertNotFalse($directory);
        $this->assertStringStartsWith('sofep-uat-', basename($directory));
        $database = $directory.DIRECTORY_SEPARATOR.'uat.sqlite';
        $this->assertFileDoesNotExist($database);
        $password = bin2hex(random_bytes(16));
        User::create(['name' => 'UAT Admin', 'email' => 'admin@sofep.example.test', 'password' => Hash::make($password)]);
        foreach (['customer', 'seller'] as $role) {
            KhachHang::create(['ho_va_ten' => 'UAT '.$role, 'email' => $role.'@sofep.example.test', 'mat_khau' => Hash::make($password),
                'role' => $role, 'is_kich_hoat' => 1, 'is_khoa' => 0]);
        }
        DB::statement('VACUUM INTO '.DB::connection()->getPdo()->quote($database));
        file_put_contents($directory.DIRECTORY_SEPARATOR.'session.json', json_encode([
            'purpose' => 'SOFEP disposable browser fixture', 'database' => $database, 'password' => $password,
        ], JSON_THROW_ON_ERROR));
        $this->assertFileExists($database);
    }
}
