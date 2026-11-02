<div class="w-[98%] xl:w-[96%] mx-auto py-4 md:py-6 space-y-6">

    {{-- Success Alert Feedback Notification --}}
    @if (session()->has('success'))
    <div class="flex items-center p-4 text-emerald-800 bg-emerald-50 rounded-xl border border-emerald-100 shadow-sm" role="alert">
        <div>
            <strong class="block font-bold text-sm">Changes Saved!</strong>
            <span class="text-xs opacity-90">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    {{-- ======================================================================= --}}
    {{-- TOP TAB NAVIGATION BAR --}}
    {{-- ======================================================================= --}}
    <div class="flex border-b border-slate-200 bg-white p-2 rounded-xl shadow-xs gap-2">
        <button wire:click="$set('activeTab', 'matrix')" 
                class="flex-1 sm:flex-none px-4 py-2.5 rounded-lg font-semibold text-sm transition-all cursor-pointer {{ $activeTab === 'matrix' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            Role Permissions Matrix
        </button>

        <button wire:click="$set('activeTab', 'user_overrides')" 
                class="flex-1 sm:flex-none px-4 py-2.5 rounded-lg font-semibold text-sm transition-all cursor-pointer {{ $activeTab === 'user_overrides' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            User Access Override
        </button>
    </div>

    {{-- ======================================================================= --}}
    {{-- TAB 1: ROLE PERMISSIONS MATRIX --}}
    {{-- ======================================================================= --}}
    @if($activeTab === 'matrix')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <div class="lg:col-span-4 xl:col-span-3 bg-white border border-slate-100 rounded-2xl shadow-sm p-4 space-y-2">
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider pl-2 mb-2">Select Role</span>
            @foreach($roles as $role)
            <button type="button" wire:click="selectRole({{ $role->id }})"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wide transition-all cursor-pointer text-left border
                    {{ $selectedRoleId === $role->id ? 'bg-blue-600 text-white shadow-md border-blue-600' : 'bg-white text-slate-600 border-slate-100 hover:bg-slate-50 hover:border-slate-200' }}">
                <span>{{ $role->name }}</span>
                <span class="text-xs font-mono">→</span>
            </button>
            @endforeach
        </div>

        <div class="lg:col-span-8 xl:col-span-9 bg-white border border-slate-100 rounded-2xl shadow-sm p-6 space-y-6">
            <div class="flex justify-between items-center pb-4 border-b border-slate-100">
                <div>
                    <h5 class="text-base font-bold text-slate-800">
                        Permissions for <span class="text-blue-600">{{ $roles->firstWhere('id', $selectedRoleId)?->name ?? 'Role' }}</span>
                    </h5>
                    <p class="text-slate-400 text-xs mt-0.5">Toggle permissions to assign or revoke for this specific role.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[11px] font-bold text-slate-500 bg-slate-50 border border-slate-100 px-3 py-1.5 rounded-lg">
                        {{ count($roleSelectedPermissions) }} assigned
                    </span>
                    <button wire:click="saveRolePermissions" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm cursor-pointer transition-all">
                        Save Changes
                    </button>
                </div>
            </div>

            <div class="space-y-6 max-h-[600px] overflow-y-auto pr-2">
                @foreach($groupedPermissions as $moduleName => $permissionsList)
                <div class="space-y-3">
                    <h6 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">{{ $moduleName }}</h6>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($permissionsList as $permission)
                        <label class="border rounded-xl p-3 flex items-center gap-3 cursor-pointer select-none transition-all
                                      {{ in_array($permission['name'], $roleSelectedPermissions) ? 'bg-blue-50/40 border-blue-200' : 'bg-white border-slate-200/70 hover:border-slate-300' }}">
                            <input type="checkbox" wire:model="roleSelectedPermissions" value="{{ $permission['name'] }}"
                                   class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500">
                            <span class="text-xs font-semibold text-slate-700 capitalize">{{ $permission['name'] }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
    @endif

    {{-- ======================================================================= --}}
    {{-- TAB 2: USER ACCESS OVERRIDES --}}
    {{-- ======================================================================= --}}
    @if($activeTab === 'user_overrides')

    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-5 md:p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">

            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white font-bold shadow-xs bg-gradient-to-br from-blue-700 to-blue-500 text-xl font-mono shrink-0">
                    {{ collect(explode(' ', $userName))->map(fn($n) => mb_substr($n, 0, 1))->join('') }}
                </div>
                <div>
                    <h4 class="text-xl font-bold text-slate-800 tracking-tight">{{ $userName }}</h4>
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 mt-1">
                        <span class="bg-slate-50 text-slate-600 border border-slate-200 px-2.5 py-1 rounded-md font-medium">
                            Role: <strong class="text-slate-800">{{ $system_role }}</strong>
                        </span>
                        <span class="bg-slate-50 text-slate-600 border border-slate-200 px-2.5 py-1 rounded-md font-medium">
                            Designation: {{ $userDesignation }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="w-full sm:w-72 relative" wire:click.away="$set('showDropdown', false)">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 tracking-wide">Find Employee:</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="searchUser" wire:focus="openDropdown" placeholder="Search by name..." autocomplete="off"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium text-slate-700">
                </div>

                @if($showDropdown && count($users) > 0)
                <div class="absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-xl shadow-xl z-50 divide-y divide-slate-50 max-h-40 overflow-y-auto">
                    @foreach($users as $user)
                    <button type="button" wire:click="selectUser({{ $user->id }})"
                            class="w-full text-left px-4 py-2.5 hover:bg-blue-50 transition-colors flex flex-col border-l-4 {{ $selectedUserId == $user->id ? 'bg-blue-50 border-l-blue-600' : 'border-l-transparent' }}">
                        <span class="text-xs font-bold text-slate-700">{{ $user->name }}</span>
                        <span class="text-[10px] text-slate-400">{{ $user->email }}</span>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

        </div>
    </div>

    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-6 space-y-6">
        <div class="flex justify-between items-center pb-4 border-b border-slate-100">
            <div>
                <h5 class="text-base font-bold text-slate-800">
                    Direct Permissions for <span class="text-blue-600">{{ $userName }}</span>
                </h5>
                <p class="text-slate-400 text-xs mt-0.5">Assign permissions directly to this user (overriding roles).</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-[11px] font-bold text-slate-500 bg-slate-50 border border-slate-100 px-3 py-1.5 rounded-lg">
                    {{ count($userSelectedPermissions) }} direct assigned
                </span>
                <button wire:click="saveUserDirectPermissions" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm cursor-pointer transition-all disabled:opacity-40"
                        {{ !$selectedUserId ? 'disabled' : '' }}>
                    Save Changes
                </button>
            </div>
        </div>

        <div class="space-y-6 max-h-[600px] overflow-y-auto pr-2">
            @foreach($groupedPermissions as $moduleName => $permissionsList)
            <div class="space-y-3">
                <h6 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">{{ $moduleName }}</h6>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-3.5">
                    @foreach($permissionsList as $permission)
                        <div class="flex items-start gap-3 p-3 rounded-xl border transition-all
                                    {{ $permission['is_inherited'] ? 'bg-slate-50/70 border-slate-200/60 opacity-90' : ($permission['is_direct'] ? 'bg-blue-50/40 border-blue-200' : 'bg-white border-slate-200/80 hover:border-slate-300') }}">
                            
                            {{-- Checkbox --}}
                            <div class="flex items-center h-5">
                                @if($permission['is_inherited'])
                                    <input type="checkbox" checked disabled 
                                           class="w-4 h-4 rounded text-slate-400 border-slate-300 bg-slate-200 cursor-not-allowed">
                                @else
                                    <input type="checkbox" wire:model="userSelectedPermissions" value="{{ $permission['name'] }}"
                                           class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 cursor-pointer"
                                           {{ !$selectedUserId ? 'disabled' : '' }}>
                                @endif
                            </div>

                            {{-- Title & Subtitle --}}
                            <div class="flex flex-col">
                                <span class="text-xs font-semibold {{ $permission['is_inherited'] ? 'text-slate-700' : 'text-slate-800' }} capitalize">
                                    {{ $permission['name'] }}
                                </span>
                                @if($permission['is_inherited'])
                                    <span class="text-[10px] font-semibold text-emerald-600 mt-0.5 tracking-tight">
                                        Inherited from role
                                    </span>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @endif

</div>