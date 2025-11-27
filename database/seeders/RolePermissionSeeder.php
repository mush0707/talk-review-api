<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'tags.read',

            'proposals.read.own',
            'proposals.read.any',
            'proposals.create',
            'proposals.status.change',

            'reviews.read.own_proposal',
            'reviews.read.any',
            'reviews.upsert',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        $speaker = Role::firstOrCreate(['name' => 'speaker']);
        $reviewer = Role::firstOrCreate(['name' => 'reviewer']);
        $admin = Role::firstOrCreate(['name' => 'admin']);

        $speaker->syncPermissions([
            'tags.read',
            'proposals.read.own',
            'proposals.create',
            'reviews.read.own_proposal',
        ]);

        $reviewer->syncPermissions([
            'tags.read',
            'proposals.read.any',
            'reviews.read.any',
            'reviews.upsert',
        ]);

        $admin->syncPermissions([
            'tags.read',
            'proposals.read.any',
            'proposals.status.change',
            'reviews.read.any',
        ]);
    }
}
