<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ManageAccess extends Component
{
    public string $activeTab = 'user_overrides'; 

    // TAB 1: Role Permissions Matrix
    public ?int $selectedRoleId = null;
    public array $roleSelectedPermissions = [];

    // TAB 2: User Direct Access Overrides
    public ?int $selectedUserId = null;
    public string $userName = "No User Selected";
    public string $userDesignation = "N/A";
    public string $system_role = "No Role Assigned";
    public string $searchUser = '';
    public array $userSelectedPermissions = [];
    public array $userInheritedPermissions = [];
    public bool $showDropdown = false;

    public function mount(): void
    {
        $firstRole = Role::where('name', '!=', 'Super Admin')->first();
        if ($firstRole) {
            $this->selectRole($firstRole->id);
        }

        $firstUser = User::with(['employee', 'roles'])->withoutRole('Super Admin')->first();
        if ($firstUser) {
            $this->selectUser($firstUser->id);
        }
    }

    // =========================================================================
    // TAB 1: ROLE MATRIX LOGIC
    // =========================================================================
    public function selectRole(int $roleId): void
    {
        $this->selectedRoleId = $roleId;
        $role = Role::findOrFail($roleId);
        $this->roleSelectedPermissions = $role->permissions->pluck('name')->toArray();
    }

    public function saveRolePermissions(): void
    {
        if (!$this->selectedRoleId) return;

        $role = Role::findOrFail($this->selectedRoleId);
        $role->syncPermissions($this->roleSelectedPermissions);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        session()->flash('success', "Permissions for role '{$role->name}' updated successfully.");
    }

    // =========================================================================
    // TAB 2: USER DIRECT OVERRIDES LOGIC
    // =========================================================================
    public function openDropdown(): void
    {
        $this->showDropdown = true;
    }

    public function selectUser(int $userId): void
    {
        $this->selectedUserId = $userId;
        $user = User::with(['employee', 'roles', 'permissions'])->find($userId);

        if ($user) {
            $this->userName = $user->employee
                ? trim($user->employee->first_name . ' ' . $user->employee->last_name)
                : $user->name;

            $this->userDesignation = $user->employee?->job_position ?? 'Employee';
            $this->searchUser = $this->userName;
            $this->showDropdown = false;

            $role = $user->roles->first();
            $this->system_role = $role ? $role->name : 'No Role Assigned';

            
            $this->userInheritedPermissions = $user->getPermissionsViaRoles()->pluck('name')->toArray();
            $this->userSelectedPermissions = $user->getDirectPermissions()->pluck('name')->toArray();
        }
    }

    public function saveUserDirectPermissions(): void
    {
        if (!$this->selectedUserId) return;

        $user = User::findOrFail($this->selectedUserId);
        $user->syncPermissions($this->userSelectedPermissions);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        session()->flash('success', "Direct permissions for '{$this->userName}' updated successfully.");
    }

    // =========================================================================
    // RENDER PIPELINE
    // =========================================================================
    public function render()
    {
        $roles = Role::where('name', '!=', 'Super Admin')->get();

        $users = User::with('employee')->withoutRole('Super Admin')
            ->when(!empty($this->searchUser) && $this->searchUser !== $this->userName, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->searchUser . '%')
                      ->orWhere('email', 'like', '%' . $this->searchUser . '%');
                });
            })
            ->take(10)
            ->get();

       
        $groupedPermissions = Permission::all()->map(function ($permission) {
            $words = explode(' ', trim($permission->name));
            array_shift($words);
            $moduleName = strtoupper(implode(' ', $words) ?: 'GENERAL');

            return [
                'name'         => $permission->name,
                'module'       => $moduleName,
                'is_inherited' => in_array($permission->name, $this->userInheritedPermissions),
                'is_direct'    => in_array($permission->name, $this->userSelectedPermissions),
            ];
        })->groupBy('module');

        return view('livewire.settings.manage-access', [
            'roles' => $roles,
            'users' => $users,
            'groupedPermissions' => $groupedPermissions,
        ]);
    }
}