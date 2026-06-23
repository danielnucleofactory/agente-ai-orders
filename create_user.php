<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$id = DB::table('users')->where('email', 'vladimir@raga-x.ai')->value('id');

if (!$id) {
    DB::statement("SELECT setval('users_id_seq', (SELECT MAX(id) FROM users))");
    $id = DB::table('users')->insertGetId([
        'name' => 'Vladimir Ramirez',
        'email' => 'vladimir@raga-x.ai',
        'password' => bcrypt('Raga.2026@'),
        'email_verified_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

DB::table('users')->where('id', $id)->update([
    'password' => bcrypt('Raga.2026@'),
]);

DB::table('model_has_roles')->insertOrIgnore([
    'role_id' => 2,
    'model_type' => 'App\Models\User',
    'model_id' => $id,
]);

$perms = DB::table('permissions')->pluck('id');
foreach($perms as $p) {
    DB::table('model_has_permissions')->insertOrIgnore([
        'permission_id' => $p,
        'model_type' => 'App\Models\User',
        'model_id' => $id,
    ]);
}

echo "Listo. Usuario ID: " . $id . "\n";
