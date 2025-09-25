<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PermissionGroup;
use App\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Department;
use App\Models\ComplaintCategory;
use App\Models\SeverityLevel;
use App\Models\Complaint;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------
        // 1) Permission Groups (append if not exists)
        // -------------------------
        $userManagementGroup = PermissionGroup::firstOrCreate(['name' => 'User Management']);
        $complaintManagementGroup = PermissionGroup::firstOrCreate(['name' => 'Complaint Management']);

        // -------------------------
        // 2) Permissions (append if not exists)
        // -------------------------
        $permissions = [
            ['name' => 'View Permission Group', 'group_id' => $userManagementGroup->id],
            ['name' => 'Create Permission Group', 'group_id' => $userManagementGroup->id],
            ['name' => 'Edit Permission Group', 'group_id' => $userManagementGroup->id],
            ['name' => 'Delete Permission Group', 'group_id' => $userManagementGroup->id],
            ['name' => 'View Complaint', 'group_id' => $complaintManagementGroup->id],
            ['name' => 'Create Complaint', 'group_id' => $complaintManagementGroup->id],
            ['name' => 'Edit Complaint', 'group_id' => $complaintManagementGroup->id],
            ['name' => 'Delete Complaint', 'group_id' => $complaintManagementGroup->id],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name']],
                ['group_id' => $perm['group_id']]
            );
        }

        // -------------------------
        // 3) Roles (append if not exists)
        // -------------------------
        $roles = ['Admin', 'Student', 'Teacher', 'HOD', 'VP', 'Principal'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // -------------------------
        // 4) Users (append if not exists)
        // -------------------------
        $users = [
            ['name' => 'Admin User', 'email' => 'admin@example.com', 'role' => 'Admin'],
            ['name' => 'John Student', 'email' => 'student@example.com', 'role' => 'Student'],
            ['name' => 'Jane Teacher', 'email' => 'teacher@example.com', 'role' => 'Teacher'],
            ['name' => 'HOD User', 'email' => 'hod@example.com', 'role' => 'HOD'],
            ['name' => 'VP User', 'email' => 'vp@example.com', 'role' => 'VP'],
            ['name' => 'Principal User', 'email' => 'principal@example.com', 'role' => 'Principal'],
        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
            if (!$user->hasRole($u['role'])) {
                $user->assignRole($u['role']);
            }
        }

        // -------------------------
        // 5) Departments (append)
        // -------------------------
        $departments = ['Academic', 'Admin', 'Facilities'];
        foreach ($departments as $dept) {
            Department::firstOrCreate(['name' => $dept]);
        }

        // -------------------------
        // 6) Complaint Categories (append)
        // -------------------------
        $categories = ['Academic', 'Facility', 'Behavioral'];
        foreach ($categories as $cat) {
            ComplaintCategory::firstOrCreate(['name' => $cat]);
        }

        // -------------------------
        // 7) Severity Levels (append)
        // -------------------------
        $levels = ['Low', 'Medium', 'High', 'Urgent'];
        foreach ($levels as $level) {
            SeverityLevel::firstOrCreate(['name' => $level]);
        }

        // -------------------------
        // 8) Sample Complaint (append if not exists)
        // -------------------------
        $student = User::where('email', 'student@example.com')->first();
        $teacher = User::where('email', 'teacher@example.com')->first();
        $facilityDept = Department::where('name', 'Facilities')->first();
        $facilityCategory = ComplaintCategory::where('name', 'Facility')->first();
        $mediumSeverity = SeverityLevel::where('name', 'Medium')->first();

        Complaint::firstOrCreate(
            [
                'title' => 'Classroom AC not working',
                'user_id' => $student->id
            ],
            [
                'description' => 'The AC in classroom 203 is not functioning properly.',
                'category_id' => $facilityCategory->id,
                'severity_id' => $mediumSeverity->id,
                'status' => 'Received',
                'department_id' => $facilityDept->id,
                'assigned_to' => $teacher->id,
                'is_anonymous' => false,
            ]
        );
    }
}
