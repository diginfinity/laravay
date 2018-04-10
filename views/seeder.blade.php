<?php echo '<?php' ?>

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaratrustSeeder extends Seeder
{
    /**
    * Run the database seeds.
    *
    * @return void
    */
    public function run()
    {
        $this->command->info('Truncating User, Role and Permission tables');
        $this->truncateLaratrustTables();

        $config = config('laravay_seeder.role_structure');
        $userPermission = config('laravay_seeder.permission_structure');
        $mapPermission = collect(config('laravay_seeder.permissions_map'));

        foreach ($config as $key => $modules) {

            // Create a new role
            $role = \App\Role::create([
                'name' => $key,
                'display_name' => ucwords(str_replace('_', ' ', $key)),
                'description' => ucwords(str_replace('_', ' ', $key))
            ]);
            $permissions = [];

            $this->command->info('Creating Role '. strtoupper($key));

            // Reading role permission modules
            foreach ($modules as $module => $value) {

                foreach (explode(',', $value) as $p => $perm) {

                    $permissionValue = $mapPermission->get($perm);

                    $permissions[] = \App\Permission::firstOrCreate([
                        'name' => $permissionValue . '-' . $module,
                        'display_name' => ucfirst($permissionValue) . ' ' . ucfirst($module),
                        'description' => ucfirst($permissionValue) . ' ' . ucfirst($module),
                    ])->id;

                    $this->command->info('Creating Permission to '.$permissionValue.' for '. $module);
                }
            }

            // Attach all permissions to the role
            $role->permissions()->sync($permissions);

        }
    }

    /**
    * Truncates all the laravay tables and the users table
    *
    * @return  void
    */
    public function truncateLaratrustTables()
    {
        Schema::disableForeignKeyConstraints();
    @if (Config::get('database.default') == 'pgsql')
        DB::table('permission_role')->truncate();
        DB::table('permission_user')->truncate();
        DB::table('role_user')->truncate();
        $usersTable = (new \App\User)->getTable();
        $rolesTable = (new \App\Role)->getTable();
        $permissionsTable = (new \App\Permission)->getTable();
        DB::statement("TRUNCATE TABLE {$usersTable} CASCADE");
        DB::statement("TRUNCATE TABLE {$rolesTable} CASCADE");
        DB::statement("TRUNCATE TABLE {$permissionsTable} CASCADE");
    @else
        DB::table('permission_role')->truncate();
        DB::table('permission_user')->truncate();
        DB::table('role_user')->truncate();
        \App\User::truncate();
        \App\Role::truncate();
        \App\Permission::truncate();
    @endif
        Schema::enableForeignKeyConstraints();
    }
}