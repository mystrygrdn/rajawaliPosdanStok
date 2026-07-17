<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

// ─────────────────────────────────────────────
// Unit: Model User
// ─────────────────────────────────────────────

it('isAdmin() true hanya untuk role admin', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $owner = User::factory()->create(['role' => 'owner']);
    $dapur = User::factory()->create(['role' => 'dapur']);

    expect($admin->isAdmin())->toBeTrue();
    expect($owner->isAdmin())->toBeFalse();
    expect($dapur->isAdmin())->toBeFalse();
});

it('isOwner() true hanya untuk role owner', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $owner = User::factory()->create(['role' => 'owner']);

    expect($owner->isOwner())->toBeTrue();
    expect($admin->isOwner())->toBeFalse();
});

it('isDapur() true hanya untuk role dapur', function () {
    $dapur = User::factory()->create(['role' => 'dapur']);
    $admin = User::factory()->create(['role' => 'admin']);

    expect($dapur->isDapur())->toBeTrue();
    expect($admin->isDapur())->toBeFalse();
});

it('role_label mengembalikan teks yang benar per role', function () {
    expect(User::factory()->create(['role' => 'admin'])->role_label)->toBe('Administrator');
    expect(User::factory()->create(['role' => 'owner'])->role_label)->toBe('Owner');
    expect(User::factory()->create(['role' => 'dapur'])->role_label)->toBe('Dapur');
});

it('password disimpan dalam bentuk hash', function () {
    $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

    expect($user->password)->not->toBe('rahasia123');
    expect(\Illuminate\Support\Facades\Hash::check('rahasia123', $user->password))->toBeTrue();
});

it('field password tersembunyi saat serialisasi ke array', function () {
    $user = User::factory()->create();
    expect(array_keys($user->toArray()))->not->toContain('password');
});

// ─────────────────────────────────────────────
// Unit: Gates / Authorization
// ─────────────────────────────────────────────

it('gate admin-only hanya lolos untuk admin', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $owner = User::factory()->create(['role' => 'owner']);
    $dapur = User::factory()->create(['role' => 'dapur']);

    expect(Gate::forUser($admin)->allows('admin-only'))->toBeTrue();
    expect(Gate::forUser($owner)->allows('admin-only'))->toBeFalse();
    expect(Gate::forUser($dapur)->allows('admin-only'))->toBeFalse();
});

it('gate view-laporan lolos untuk admin dan owner saja', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $owner = User::factory()->create(['role' => 'owner']);
    $dapur = User::factory()->create(['role' => 'dapur']);

    expect(Gate::forUser($admin)->allows('view-laporan'))->toBeTrue();
    expect(Gate::forUser($owner)->allows('view-laporan'))->toBeTrue();
    expect(Gate::forUser($dapur)->allows('view-laporan'))->toBeFalse();
});

it('gate admin-or-dapur lolos untuk admin, dapur, DAN owner', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $owner = User::factory()->create(['role' => 'owner']);
    $dapur = User::factory()->create(['role' => 'dapur']);

    expect(Gate::forUser($admin)->allows('admin-or-dapur'))->toBeTrue();
    expect(Gate::forUser($owner)->allows('admin-or-dapur'))->toBeTrue();
    expect(Gate::forUser($dapur)->allows('admin-or-dapur'))->toBeTrue();
});

it('gate view-katalog lolos untuk semua role yang valid', function () {
    foreach (['admin', 'owner', 'dapur'] as $role) {
        $user = User::factory()->create(['role' => $role]);
        expect(Gate::forUser($user)->allows('view-katalog'))->toBeTrue();
    }
});

it('gate dapur-access lolos untuk admin dan dapur saja', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $dapur = User::factory()->create(['role' => 'dapur']);
    $owner = User::factory()->create(['role' => 'owner']);

    expect(Gate::forUser($admin)->allows('dapur-access'))->toBeTrue();
    expect(Gate::forUser($dapur)->allows('dapur-access'))->toBeTrue();
    expect(Gate::forUser($owner)->allows('dapur-access'))->toBeFalse();
});