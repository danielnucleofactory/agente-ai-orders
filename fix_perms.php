<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$id = DB::table('users')->where('email','vladimir@raga-x.ai')->value('id');
$perms = DB::table('permissions')->pluck('id');
foreach($perms as $p) {
    DB::table('model_has_permissions')->insertOrIgnore([
        'permission_id' => $p,
        'model_type' => 'App\Models\User',
        'model_id' => $id
    ]);
}
echo "Permisos asignados al usuario ID: " . $id . "\n";
