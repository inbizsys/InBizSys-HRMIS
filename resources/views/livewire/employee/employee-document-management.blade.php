<div class="p-6 bg-gray-50 min-h-screen">
    {{-- Header Content --}}
    <div class="max-w-7xl mx-auto mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Employee Documents</h1>
            <p class="text-sm text-gray-500 mt-1">Upload and filter all employee-specific documents via dynamic tabs.</p>
        </div>
        @can('create employee document')
        <button wire:click="openUploadModal" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2.5 rounded-xl shadow-sm text-sm transition-all">
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Upload New Documents
        </button>
        @endcan
    </div>

    {{-- Session Flash Alert --}}
    @if (session()->has('message'))
    <div class="max-w-7xl mx-auto mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-medium">
        {{ session('message') }}
    </div>
    @endif

    {{-- TABS SYSTEM --}}
    <div class="max-w-7xl mx-auto mb-6">
        <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-px">
            <button wire:click="changeTab('all')" class="px-4 py-2.5 rounded-t-xl font-semibold text-sm transition-all {{ $activeTab === 'all' ? 'bg-white border-t border-x border-gray-200 text-blue-600 shadow-sm -mb-px' : 'text-gray-500 hover:text-gray-700' }}">
                All Documents
            </button>
            @foreach($allCategories as $cat)
            <button wire:key="tab-{{ $cat->id }}" wire:click="changeTab({{ $cat->id }})" class="px-4 py-2.5 rounded-t-xl font-semibold text-sm transition-all {{ $activeTab == $cat->id ? 'bg-white border-t border-x border-gray-200 text-blue-600 shadow-sm -mb-px' : 'text-gray-500 hover:text-gray-700' }}">
                {{ $cat->name }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Main Table Box --}}
    <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Search Bar --}}
        <div class="p-5 border-b border-gray-100 bg-white">
            <div class="max-w-md relative">
                <input type="text" wire:model.live="search" placeholder="Search by Employee Name..." class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all text-gray-800">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto bg-white">
            <table class="w-full text-left border-collapse bg-white">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wide">Employee</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wide">Category</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wide">Uploaded At</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wide text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 font-medium text-sm text-gray-700 bg-white">
                    @forelse($documents as $doc)
                    <tr wire:key="doc-{{ $doc->id }}" class="hover:bg-gray-50/70 transition-colors">
                        <td class="px-6 py-4 text-gray-900 font-semibold">{{ $doc->employee->first_name }} {{ $doc->employee->last_name }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-md bg-gray-100 text-gray-800 text-xs font-semibold">
                                {{ $doc->category->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-xs">{{ $doc->created_at->format('M d, Y h:i A') }}</td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center space-x-1">
                                {{-- VIEW BUTTON --}}
                                @can('view employee document')
                                <button type="button" wire:click="viewDocument('{{ $doc->file_path }}')" title="View Document" class="px-2 py-1 inline-flex text-xs font-semibold rounded-lg bg-green-100 text-green-800 hover:bg-green-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </button>
                                @endcan

                                <span class="text-gray-200 select-none">|</span>

                                {{-- EDIT BUTTON --}}
                                @can('edit employee document')
                                <button type="button" wire:click="openEditModal({{ $doc->id }})" title="Edit Document" class="px-2 py-1 inline-flex text-xs font-semibold rounded-lg bg-blue-100 text-blue-800 hover:bg-blue-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </button>
                                @endcan

                                <span class="text-gray-200 select-none">|</span>

                                {{-- DELETE BUTTON (Ensured to be type="button") --}}
                                @can('delete employee document')
                                <button type="button" wire:click="confirmDelete({{ $doc->id }}, '{{ addslashes($doc->employee->first_name) }}', '{{ addslashes($doc->category->name) }}')" title="Delete" class="px-2 py-1 inline-flex text-xs font-semibold rounded-lg bg-red-100 text-red-800 hover:bg-red-200">
                                    <svg xmlns="http://www.w3.org/2000/xl" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic bg-white">No documents uploaded under this section.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-gray-50 border-t border-gray-100">{{ $documents->links() }}</div>
    </div>

    {{-- DYNAMIC MULTI-ROW UPLOAD / EDIT MODAL --}}
    @if($showUploadModal)
    <div wire:key="upload-modal-wrapper" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6">
        <div class="fixed inset-0 bg-gray-600/50 backdrop-blur-sm" wire:click="resetForm"></div>
        
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-5xl h-auto max-h-[90vh] flex flex-col p-6 border border-gray-100">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4 flex-shrink-0">
                <h3 class="text-lg font-bold text-gray-900">
                    {{ $isEditMode ? 'Edit Employee Document' : 'Upload Employee Documents' }}
                </h3>
                <button wire:click="resetForm" type="button" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form wire:submit.prevent="saveDocuments" class="flex flex-col flex-1 min-h-0">
                {{-- EMPLOYEE SELECT DROPDOWN --}}
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 relative flex-shrink-0 mb-4" wire:click.outside="$set('showEmpDropdown', false)">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">
                        Select Employee <span class="text-red-500 font-bold">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               wire:model.live="empSearch" 
                               wire:focus="$set('showEmpDropdown', true)"
                               placeholder="🔍 Type name to search & select employee..." 
                               @disabled($isEditMode)
                               class="w-full border border-gray-300 text-gray-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all {{ $isEditMode ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'bg-white' }}" />
                        
                        @if($selectedEmployeeId && !$isEditMode)
                            <button type="button" wire:click="resetForm" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 text-xs">✕</button>
                        @endif

                        @if($showEmpDropdown && !$isEditMode)
                        <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl mt-1 shadow-lg max-h-40 overflow-y-auto divide-y divide-gray-50">
                            @forelse($employees as $emp)
                                <button type="button" 
                                        wire:click="selectEmployee({{ $emp->id }}, '{{ $emp->first_name }} {{ $emp->last_name }}')"
                                        class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors font-medium">
                                    {{ $emp->first_name }} {{ $emp->last_name }}
                                </button>
                            @empty
                                <div class="px-4 py-3 text-xs text-gray-400 italic">No employees found.</div>
                            @endforelse
                        </div>
                        @endif
                    </div>
                    @error('selectedEmployeeId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- DYNAMIC ROWS CONTAINER --}}
                <div class="flex-1 overflow-y-visible pr-2 p-1 mb-4 space-y-4 min-h-[180px]">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide py-1">
                        Attach Categories & Files <span class="text-red-500 font-bold">*</span>
                    </label>

                    @foreach($rows as $index => $row)
                    <div wire:key="row-item-{{ $row['id'] }}" class="bg-gray-50 p-4 rounded-xl border border-gray-200 relative">
                        <div class="flex flex-col sm:flex-row items-stretch gap-4">
                            
                            {{-- CATEGORY DROPDOWN --}}
                            <div class="flex-1 relative" wire:click.outside="$set('rows.{{ $index }}.show_dropdown', false)">
                                <div class="relative">
                                    <input type="text" 
                                           wire:model.live="rows.{{ $index }}.cat_search" 
                                           wire:focus="$set('rows.{{ $index }}.show_dropdown', true)"
                                           @disabled($row['is_required'] || $isEditMode)
                                           placeholder="🔍 Type & select Category..." 
                                           class="w-full border border-gray-300 bg-white text-gray-800 rounded-xl px-3 py-2.5 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none {{ ($row['is_required'] || $isEditMode) ? 'bg-gray-100 text-gray-500 cursor-not-allowed font-semibold' : '' }}" />
                                    
                                    @if($row['is_required'])
                                        <span class="absolute right-3 top-3 text-[10px] bg-red-100 text-red-700 font-bold px-1.5 py-0.5 rounded">Required</span>
                                    @endif

                                    @if($row['show_dropdown'] && !$row['is_required'] && !$isEditMode)
                                    <div class="absolute left-0 right-0 z-50 bg-white border border-gray-200 rounded-xl mt-1 shadow-xl max-h-40 overflow-y-auto divide-y divide-gray-50">
                                        @forelse($this->getCategoriesForRow($index) as $cat)
                                            <button type="button" 
                                                    wire:click="selectCategory({{ $index }}, {{ $cat->id }}, '{{ $cat->name }}')"
                                                    class="w-full text-left px-3 py-2.5 text-xs text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors font-medium">
                                                {{ $cat->name }}
                                            </button>
                                        @empty
                                            <div class="px-3 py-2 text-[11px] text-gray-400 italic">No categories found.</div>
                                        @endforelse
                                    </div>
                                    @endif
                                </div>
                                @error("rows.{$index}.document_category_id") <span class="text-[10px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- FILE INPUT (Added accept attribute to block Word docs inside browser) --}}
                            <div class="flex-1 relative flex flex-col justify-center" wire:key="file-input-{{ $row['id'] }}-{{ $row['document_category_id'] ?: 'empty' }}">
                                <div class="relative flex items-center bg-white border border-gray-300 rounded-xl overflow-hidden p-1">
                                    <input type="file" 
                                           wire:model="rows.{{ $index }}.file" 
                                           accept=".pdf, .jpg, .jpeg, .png" 
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" />
                                    
                                    <div class="flex items-center justify-between w-full pr-4 z-10 pointer-events-none">
                                        <div class="flex items-center w-full">
                                            <span class="bg-blue-50 text-blue-700 font-semibold text-[11px] py-1.5 px-3 rounded-lg mr-2 border border-blue-100 flex-shrink-0">
                                                Choose File
                                            </span>
                                            <span class="text-xs text-gray-600 truncate w-full">
                                                @if(isset($row['file']) && $row['file'])
                                                    {{ $row['file']->getClientOriginalName() }}
                                                @elseif(isset($row['existing_file_path']) && $row['existing_file_path'])
                                                    {{ basename($row['existing_file_path']) }}
                                                @else
                                                    No file chosen
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @error("rows.{$index}.file") <span class="text-[10px] text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- REMOVE ROW BUTTON --}}
                            <div class="w-10 flex items-center justify-center">
                                @if(!$row['is_required'] && !$isEditMode)
                                <button type="button" wire:click="removeRow({{ $index }})" class="p-2.5 text-red-500 hover:bg-red-50 rounded-xl border border-transparent hover:border-red-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach 
                </div>

                {{-- ADD ROW BUTTON --}}
                @if(!$isEditMode)
                <div class="flex-shrink-0 mb-2">
                    <button type="button" wire:click="addRow('', '', false)" class="inline-flex items-center text-xs font-bold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-2 rounded-xl border border-blue-100 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg> Add Optional Document
                    </button>
                </div>
                @endif

                {{-- FOOTER BUTTONS --}}
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100 flex-shrink-0 bg-white">
                    <button type="button" wire:click="resetForm" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-semibold text-sm hover:bg-gray-50 transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition-all">
                        {{ $isEditMode ? 'Update Document' : 'Save All Documents' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- DELETE CONFIRMATION MODAL --}}
    @if($showDeleteModal)
    <div wire:key="delete-modal-wrapper" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-600/50 backdrop-blur-sm" wire:click="$set('showDeleteModal', false)"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 text-center border border-gray-100">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 text-red-600 mb-4">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Document</h3>
            <p class="text-sm text-gray-500 mb-6">Are you sure you want to delete <span class="font-semibold text-gray-900">"{{ $documentNameToDelete }}"</span>?</p>
            <div class="flex items-center justify-center space-x-3 bg-white">
                <button type="button" wire:click="$set('showDeleteModal', false)" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-semibold text-sm hover:bg-gray-50">Cancel</button>
                <button type="button" wire:click="deleteDocument" class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold text-sm">Yes, Delete It</button>
            </div>
        </div>
    </div>
    @endif

    {{-- DOCUMENT VIEWER POPUP MODAL --}}
    @if($showViewModal)
    <div wire:key="view-modal-wrapper" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-600/50 backdrop-blur-sm" wire:click="$set('showViewModal', false)"></div>
        
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-4xl h-[85vh] flex flex-col p-5 border border-gray-100">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3 flex-shrink-0">
                <h3 class="text-sm font-bold text-gray-900 flex items-center">
                    🔍 Document Preview
                </h3>
                <button wire:click="$set('showViewModal', false)" type="button" class="text-gray-500 hover:text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-lg transition-all text-xs font-bold">
                    ✕ Close
                </button>
            </div>

            <div class="flex-1 bg-gray-100 rounded-xl overflow-hidden relative flex items-center justify-center">
                @if($viewingFileType === 'image')
                    <img src="{{ $viewingFileUrl }}" class="max-w-full max-h-full object-contain shadow-md rounded-lg" alt="Document Preview">
                @elseif($viewingFileType === 'pdf')
                    <iframe src="{{ $viewingFileUrl }}" class="w-full h-full border-0 rounded-xl" allow="autoplay"></iframe>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>