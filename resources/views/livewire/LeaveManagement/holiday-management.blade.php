<div class="p-6 max-w-8xl mx-auto space-y-6">

    {{-- System Feedback Alerts (Hidden on Print) --}}
    @if (session()->has('holidayActionFeedback'))
        <div class="print:hidden p-4 text-sm text-green-800 rounded-2xl bg-green-50 border border-green-200">
            {{ session('holidayActionFeedback') }}
        </div>
    @endif
    @if (session()->has('holidayActionError'))
        <div class="print:hidden p-4 text-sm text-red-800 rounded-2xl bg-red-50 border border-red-200">
            {{ session('holidayActionError') }}
        </div>
    @endif

    {{-- Top Action Header --}}
    <div
        class="print:hidden flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-5 rounded-2xl shadow-sm border border-gray-200 gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900 tracking-tight">Public Holidays & Work Calendar</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manage operational schedules, custom shifts, and government statutory
                holidays.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Table vs Calendar vs Annual View Switcher --}}
            <div class="inline-flex bg-gray-100 p-1 rounded-xl border border-gray-200">
                <button type="button" wire:click="setViewTab('table')"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $activeViewTab === 'table' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Table View
                </button>
                <button type="button" wire:click="setViewTab('calendar')"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $activeViewTab === 'calendar' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Monthly Calendar
                </button>
                <button type="button" wire:click="setViewTab('annual')"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $activeViewTab === 'annual' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Annual View (12 Months)
                </button>
            </div>

            <button type="button" wire:click="openConfigModal"
                class="bg-white hover:bg-gray-50 text-gray-700 font-semibold py-2 px-3.5 rounded-xl border border-gray-300 shadow-sm text-xs transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                </svg>
                Calendar Settings
            </button>

            <button type="button" wire:click="triggerCreationModal"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-xl shadow-sm text-xs transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Holidays
            </button>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 1: STANDARD TABLE VIEW                                                --}}
    {{-- ========================================================================= --}}
    @if ($activeViewTab === 'table')
        <div class="print:hidden space-y-5">
            {{-- Filter Bar --}}
            <div
                class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Year Filter --}}
                <div class="relative w-full">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Filter by
                        Year</label>
                    <button type="button" wire:click="$toggle('isYearDropdownOpen')"
                        class="w-full text-left border border-gray-300 rounded-xl p-2 text-xs bg-white text-gray-800 flex justify-between items-center shadow-sm">
                        <span class="font-medium">{{ $filterSelectedYear ?: 'All Years' }}</span>
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    @if ($isYearDropdownOpen)
                        <div
                            class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg py-1 max-h-60 overflow-y-auto">
                            <div wire:click="selectYearFilter('')"
                                class="cursor-pointer px-4 py-2 text-xs hover:bg-indigo-50">All Years</div>
                            <div wire:click="selectYearFilter('2025')"
                                class="cursor-pointer px-4 py-2 text-xs hover:bg-indigo-50">2025</div>
                            <div wire:click="selectYearFilter('2026')"
                                class="cursor-pointer px-4 py-2 text-xs hover:bg-indigo-50">2026</div>
                            <div wire:click="selectYearFilter('2027')"
                                class="cursor-pointer px-4 py-2 text-xs hover:bg-indigo-50">2027</div>
                        </div>
                    @endif
                </div>

                {{-- Month Filter --}}
                <div class="relative w-full">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Filter by
                        Month</label>
                    <button type="button" wire:click="$toggle('isMonthDropdownOpen')"
                        class="w-full text-left border border-gray-300 rounded-xl p-2 text-xs bg-white text-gray-800 flex justify-between items-center shadow-sm">
                        <span class="font-medium">{{ $monthsMappingList[$filterSelectedMonth] }}</span>
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    @if ($isMonthDropdownOpen)
                        <div
                            class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg py-1 max-h-60 overflow-y-auto">
                            @foreach ($monthsMappingList as $code => $name)
                                <div wire:click="selectMonthFilter('{{ $code }}')"
                                    class="cursor-pointer px-4 py-2 text-xs hover:bg-indigo-50">{{ $name }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Type Filter --}}
                <div class="relative w-full">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Filter by
                        Type</label>
                    <button type="button" wire:click="$toggle('isTypeDropdownOpen')"
                        class="w-full text-left border border-gray-300 rounded-xl p-2 text-xs bg-white text-gray-800 flex justify-between items-center shadow-sm">
                        <span class="font-medium">{{ $filterSelectedType ?: 'All Types' }}</span>
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    @if ($isTypeDropdownOpen)
                        <div
                            class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg py-1">
                            <div wire:click="selectTypeFilter('')"
                                class="cursor-pointer px-4 py-2 text-xs hover:bg-indigo-50">All Types</div>
                            <div wire:click="selectTypeFilter('Full Day')"
                                class="cursor-pointer px-4 py-2 text-xs hover:bg-indigo-50">Full Day</div>
                            <div wire:click="selectTypeFilter('Half Day')"
                                class="cursor-pointer px-4 py-2 text-xs hover:bg-indigo-50">Half Day</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Table Container --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left">
                        <thead class="bg-gray-50/75">
                            <tr>
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Target Date</th>
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Type Scope</th>
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Descriptive Label</th>
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Holiday Category</th>
                                <th
                                    class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm">
                            @forelse($registeredHolidaysDataset as $holiday)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap">
                                        {{ $holiday->holiday_date }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $holiday->holiday_type === 'Half Day' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                            {{ $holiday->holiday_type }}
                                            {{ $holiday->holiday_shift ? '(' . $holiday->holiday_shift . ')' : '' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">
                                        {{ $holiday->description }}
                                        @if ($holiday->is_historical_date)
                                            <span class="ml-2 text-xs text-red-500 font-medium italic">(Past
                                                Record)</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($holiday->category)
                                            <span
                                                class="px-3 py-1 rounded-full text-xs font-bold text-white shadow-sm inline-flex items-center gap-1.5"
                                                style="background-color: {{ $holiday->category->color_code }};">
                                                <span class="w-1.5 h-1.5 rounded-full bg-white/80"></span>
                                                {{ $holiday->category->name }}
                                            </span>
                                        @else
                                            <span
                                                class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                                -
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-1.5 whitespace-nowrap">
                                        {{-- View Modal Trigger --}}
                                        <button type="button" wire:click="triggerViewModal({{ $holiday->id }})"
                                            class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition"
                                            title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </button>

                                        {{-- Modify Trigger (Only displayed for future dates) --}}
                                        @if (!$holiday->is_historical_date)
                                            <button type="button"
                                                wire:click="triggerModificationModal({{ $holiday->id }})"
                                                class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                                                title="Modify Record">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                            </button>
                                        @endif

                                        {{-- Delete Trigger --}}
                                        <button type="button"
                                            wire:click="triggerDeleteConfirmationModal({{ $holiday->id }}, '{{ addslashes($holiday->description) }}')"
                                            class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                            title="Delete Record">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">No public
                                        holidays found for the selected filter parameters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 bg-gray-50/75 border-t border-gray-200">
                    {{ $registeredHolidaysDataset->links() }}
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 2: GOVERNMENT STYLE WALL CALENDAR VIEW (PRINTABLE)                    --}}
    {{-- ========================================================================= --}}
    @if ($activeViewTab === 'calendar')
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 space-y-6">

            {{-- Navigation Header --}}
            <div class="flex justify-between items-center border-b pb-4">
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="previousCalendarMonth"
                        class="p-2 border rounded-xl hover:bg-gray-50 text-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <h3 class="text-lg font-bold text-gray-900">{{ $calendarViewMonthName }} {{ $calendarViewYear }}
                    </h3>
                    <button type="button" wire:click="nextCalendarMonth"
                        class="p-2 border rounded-xl hover:bg-gray-50 text-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="printMonthlyCalendar()"
                        class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-semibold rounded-xl shadow-sm inline-flex items-center gap-1.5 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                        </svg>
                        Print Calendar
                    </button>
                </div>
            </div>

            {{-- 7-Day Matrix Grid (printable part) --}}
            <div id="printable-month-content" data-month="{{ $calendarViewMonthName }}"
                data-year="{{ $calendarViewYear }}" data-org="{{ $organisationName }}">

                <div class="mc-wrap border border-gray-300 rounded-xl overflow-hidden">
                    <div
                        class="mc-dow grid grid-cols-7 bg-gray-100 text-center font-bold text-xs uppercase tracking-wider py-2.5 border-b border-gray-300">
                        <span class="mc-sun text-rose-600">Sunday</span>
                        <span class="text-gray-700">Monday</span>
                        <span class="text-gray-700">Tuesday</span>
                        <span class="text-gray-700">Wednesday</span>
                        <span class="text-gray-700">Thursday</span>
                        <span class="text-gray-700">Friday</span>
                        <span class="mc-sat text-indigo-600">Saturday</span>
                    </div>

                    <div class="divide-y divide-gray-200">
                        @foreach ($governmentCalendarMatrix as $week)
                            <div class="mc-week grid grid-cols-7 divide-x divide-gray-200 min-h-[95px]">
                                @foreach ($week as $day)
                                    <div
                                        class="mc-cell {{ !$day['is_current_month'] ? 'mc-out' : '' }} {{ $day['has_holiday'] && $day['is_current_month'] ? 'mc-holcell' : '' }}
                                    p-2 relative flex flex-col justify-between transition {{ $day['is_current_month'] ? 'bg-white' : 'bg-gray-50/70 text-gray-300' }} {{ $day['has_holiday'] ? 'bg-rose-50/50' : '' }}">
                                        <div class="flex justify-between items-start">
                                            <span
                                                class="mc-num {{ $day['is_sunday'] ? 'mc-sun' : '' }} text-xs font-bold {{ $day['is_sunday'] ? 'text-rose-600' : ($day['is_current_month'] ? 'text-gray-800' : 'text-gray-400') }}">
                                                {{ $day['day_number'] }}
                                            </span>
                                            @if ($day['is_today'])
                                                <span
                                                    class="mc-today text-[9px] font-black uppercase tracking-tighter bg-indigo-600 text-white px-1.5 py-0.5 rounded-md">Today</span>
                                            @endif
                                        </div>

                                        @if ($day['has_holiday'])
                                            <div class="mt-1">
                                                {{-- Apply 'break-words' Tailwind class for proper text wrapping --}}
                                                <span
                                                    class="mc-hol-title block text-[10px] font-bold text-white px-1.5 py-0.5 rounded leading-tight shadow-sm break-words"
                                                    style="background-color: {{ $day['color_code'] }};">
                                                    {{ $day['holiday_title'] }}
                                                </span>
                                                <span
                                                    class="mc-hol-meta text-[9px] font-semibold uppercase tracking-tight block mt-0.5"
                                                    style="color: {{ $day['color_code'] }};">
                                                    {{ $day['category_name'] }}
                                                    {{ $day['holiday_shift'] ? '• ' . $day['holiday_shift'] : '' }}
                                                </span>
                                            </div>
                                        @else
                                            <div></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
    @script
        <script>
            window.printMonthlyCalendar = function() {
                const source = document.getElementById('printable-month-content');
                if (!source) {
                    alert('Monthly calendar view is not open.');
                    return;
                }

                const esc = s => String(s ?? '').replace(/[&<>"]/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;'
                } [c]));

                const month = esc(source.dataset.month);
                const year = esc(source.dataset.year);
                const org = esc(source.dataset.org);
                const orgCss = String(source.dataset.org ?? '').replace(/["\\\n\r]/g, '');
                const rows = Math.max(source.querySelectorAll('.mc-week').length, 4);
                const gap = 1.4;
                const gridH = 146;

                const css = `
            @page {
                size: A4 landscape;
                margin: 8mm 8mm 12mm 8mm;

                @bottom-left {
                    content: "Generated from ${orgCss} Management System";
                    font-size: 8pt;
                    color: #6b7280;
                }
                @bottom-right {
                    content: "Page " counter(page) " of " counter(pages);
                    font-size: 8pt;
                    color: #6b7280;
                }
            }

            * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            html, body { margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; color: #111827; background: #fff; }

            .mc-head { background: #0f172a; color: #fff; text-align: center; border-radius: 2mm;
                       padding: 3mm 4mm; margin-bottom: 3mm; }
            .mc-head h1 { font-size: 16pt; margin: 0; letter-spacing: .12em; text-transform: uppercase; font-weight: 800; color: #f8fafc; }
            .mc-head h2 { font-size: 12pt; margin: 1mm 0 0; color: #94a3b8; font-weight: 600; letter-spacing: .06em; }

            .mc-wrap { border: 0 !important; border-radius: 0 !important; overflow: visible !important; }

            .mc-dow { display: grid; grid-template-columns: repeat(7, 1fr); gap: ${gap}mm; margin-bottom: ${gap}mm;
                      padding: 0 !important; border: 0 !important; background: none !important; }
            .mc-dow span { background: #1e293b; color: #fff; text-align: center; border-radius: 1.2mm;
                           font-size: 9pt; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
                           line-height: 7.5mm; }
            .mc-dow .mc-sun { background: #be123c; color: #fff; }
            .mc-dow .mc-sat { background: #3730a3; color: #fff; }

            .mc-wrap > div:last-child { display: flex; flex-direction: column; gap: ${gap}mm; }
            .mc-week { display: grid; grid-template-columns: repeat(7, 1fr); gap: ${gap}mm;
                       height: calc((${gridH}mm - ${(rows - 1) * gap}mm) / ${rows});
                       min-height: 0 !important; border: 0 !important; }

            .mc-cell { display: flex; flex-direction: column; justify-content: space-between;
                       padding: 2mm 2.2mm; overflow: hidden; min-width: 0; min-height: 0; background: #ffffff;
                       border: 0.3mm solid #cbd5e1; border-radius: 1.5mm; }
            .mc-cell:nth-child(1) { background: #fff5f5; }
            .mc-cell:nth-child(7) { background: #f8f9ff; }

            .mc-cell.mc-holcell { background: #fff1f2; border-color: #fda4af; border-top: 1mm solid #e11d48; }
            .mc-cell.mc-out { background: #f8fafc; border-color: #e2e8f0; opacity: 0.7; }

            .mc-num { font-size: 14pt; font-weight: 700; color: #1e293b; line-height: 1; }
            .mc-num.mc-sun { color: #e11d48; }
            .mc-holcell .mc-num { color: #9f1239; }
            .mc-out .mc-num { color: #94a3b8; font-weight: 500; }
            .mc-today { display: none; }

            /* Holiday chip: text sudu, muluwen penna, wrap wenawa */
            .mc-hol-title { display: block; width: 100%; font-size: 8pt; font-weight: 700; color: #ffffff !important;
                            border-radius: 0.8mm; padding: 0.8mm 1.2mm; line-height: 1.25; margin-top: 1mm;
                            white-space: normal; overflow-wrap: anywhere; word-break: break-word; }
            .mc-hol-meta  { display: block; font-size: 6.5pt; font-weight: 700; text-transform: uppercase;
                            letter-spacing: .02em; margin-top: 0.5mm; white-space: normal; overflow-wrap: anywhere; }
            .mc-out .mc-hol-title, .mc-out .mc-hol-meta { display: none; }
        `;

                document.getElementById('month-print-frame')?.remove();

                const iframe = document.createElement('iframe');
                iframe.id = 'month-print-frame';
                iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';
                document.body.appendChild(iframe);

                const doc = iframe.contentWindow.document;
                doc.open();
                doc.write(`<!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>${org} - ${month} ${year}</title>
                <style>${css}</style>

                <script>
                    window.PagedConfig = {
                        auto: true,
                        after: function () {
                            setTimeout(function () {
                                window.focus();
                                window.print();
                            }, 500);
                        }
                    };
                <\/script>
                <script src="https://unpkg.com/pagedjs/dist/paged.polyfill.js"
                        onerror="window.print()"><\/script>
            </head>
            <body>
                <div class="mc-head">
                    <h1>${org} - Public Holidays Calendar</h1>
                    <h2>${month} ${year}</h2>
                </div>
                ${source.innerHTML}
            </body>
            </html>`);
                doc.close();

                setTimeout(() => iframe.remove(), 180000);
            };
        </script>
    @endscript

    {{-- ========================================================================= --}}
    {{-- TAB 3: ANNUAL YEARLY VIEW (12 MONTHS GRID + FOOTER LIST)                  --}}
    {{-- ========================================================================= --}}
    @if ($activeViewTab === 'annual')
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 space-y-6">

            {{-- Year Navigation & Print Button --}}
            <div class="flex justify-between items-center border-b pb-4">
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="previousAnnualYear"
                        class="p-2 border rounded-xl hover:bg-gray-50 text-gray-700 text-xs font-bold">← Previous
                        Year</button>
                    <h3 class="text-xl font-black text-gray-900">Calendar for Year {{ $annualViewYear }}
                        ({{ $organisationName }})</h3>
                    <button type="button" wire:click="nextAnnualYear"
                        class="p-2 border rounded-xl hover:bg-gray-50 text-gray-700 text-xs font-bold">Next Year
                        →</button>
                </div>

                <button type="button" onclick="printAnnualCalendar()"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm inline-flex items-center gap-1.5 transition">
                    Print Calendar
                </button>
            </div>

            <div id="printable-annual-content" data-year="{{ $annualViewYear }}"
                data-org="{{ $organisationName }}">

                {{-- 12 Months Grid --}}
                <div class="ac-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($annualCalendarMonths as $mDataset)
                        <div class="ac-month border border-blue-400 rounded-lg p-2.5 bg-white shadow-sm space-y-1.5">
                            <h4
                                class="ac-month-title text-center font-bold text-blue-800 text-xs border-b border-blue-200 pb-1">
                                {{ $mDataset['month_name'] }}</h4>

                            <div
                                class="ac-row ac-dow grid grid-cols-7 text-center font-bold text-[10px] text-gray-700 uppercase">
                                <span
                                    class="ac-sun text-rose-600">Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span
                                    class="ac-sat text-indigo-600">Sa</span>
                            </div>

                            <div class="space-y-0.5 text-xs">
                                @foreach ($mDataset['weeks'] as $week)
                                    <div class="ac-row grid grid-cols-7 text-center">
                                        @foreach ($week as $day)
                                            <div class="ac-day py-0.5 text-[10px] font-medium
                                                {{ !$day['is_current_month'] ? 'ac-out text-gray-300' : ($day['is_sunday'] ? 'ac-sun text-rose-600 font-bold' : 'text-gray-800') }}
                                                {{ $day['is_current_month'] && $day['has_holiday'] ? 'ac-hol font-bold rounded' : '' }}
                                                {{ $day['is_today'] ? 'bg-indigo-600 text-white rounded' : '' }}"
                                                @if ($day['is_current_month'] && $day['has_holiday']) style="background-color: {{ $day['color_code'] }}; color: #ffffff;" @endif>
                                                {{ $day['day_number'] }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="ac-foot mt-6 pt-4 border-t border-gray-300 space-y-3">
                    <h3 class="text-xs font-black text-gray-900 uppercase tracking-wider">Holiday Categories for
                        {{ $annualViewYear }}:</h3>
                    <div class="ac-hols grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2 text-xs">
                        @forelse($annualCategories as $cat)
                            <div
                                class="ac-hol-item flex items-center gap-2.5 p-2 rounded-xl bg-gray-50 border border-gray-200">
                                <span class="w-3 h-3 rounded-full inline-block flex-shrink-0 shadow-sm"
                                    style="background-color: {{ $cat->color_code }};"></span>
                                <span class="font-bold text-gray-800 text-xs">{{ $cat->name }}</span>
                            </div>
                        @empty
                            <p class="text-gray-400 italic text-xs">No holiday categories recorded for this year yet.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    @script
        <script>
            window.printAnnualCalendar = function() {
                const source = document.getElementById('printable-annual-content');
                if (!source) {
                    alert('Annual view is not open.');
                    return;
                }

                const esc = s => String(s ?? '').replace(/[&<>"]/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;'
                } [c]));

                const year = esc(source.dataset.year);
                const org = esc(source.dataset.org);
                const orgCss = String(source.dataset.org ?? '').replace(/["\\\n\r]/g, '');

                const css = `
            @media print {
                @page {
                    size: A4 landscape;
                    margin: 8mm 8mm 10mm 8mm; /* Adjusted bottom margin to 10mm to optimize page spacing */
                }
                body {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            }

            * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            html, body { margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; color: #111827; background: #fff; }

            .ac-head { text-align: center; border-bottom: 0.4mm solid #111827; padding-bottom: 2mm; margin-bottom: 3mm; }
            .ac-head h1 { font-size: 14pt; margin: 0; letter-spacing: .1em; text-transform: uppercase; }
            .ac-head p  { font-size: 7.5pt; margin: 0.5mm 0 0; color: #4b5563; }

            .ac-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 3mm; }
            
            /* Increased month container dimensions and padding */
            .ac-month { border: 0.3mm solid #60a5fa; border-radius: 1.5mm; padding: 2.5mm 2.5mm; break-inside: avoid; page-break-inside: avoid; }
            .ac-month-title { text-align: center; font-weight: 700; font-size: 9.5pt; color: #1e40af;
                              border-bottom: 0.2mm solid #bfdbfe; padding-bottom: 1mm; margin: 0 0 1.5mm; }

            .ac-row { display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; }
            .ac-dow span { font-size: 7pt; font-weight: 700; text-transform: uppercase; color: #374151; line-height: 4.5mm; }
            
            /* Increased date cell height and line-height */
            .ac-day { font-size: 7.5pt; line-height: 4.5mm; height: 4.5mm; color: #1f2937; }
            
            .ac-sun { color: #e11d48; font-weight: 700; }
            .ac-sat { color: #4f46e5; }
            .ac-out { color: #d1d5db; font-weight: 400; }
            .ac-hol { font-weight: 700; border-radius: 0.8mm; }

            .ac-foot { margin-top: 4mm; padding-top: 2mm; border-top: 0.3mm solid #9ca3af; break-inside: avoid; page-break-inside: avoid; }
            .ac-foot h3 { font-size: 8.5pt; text-transform: uppercase; letter-spacing: .05em; margin: 0 0 2mm; }
            .ac-hols { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1.5mm 2.5mm; }
            .ac-hol-item { display: flex; align-items: center; gap: 1.5mm; font-size: 7pt; padding: 1.2mm 1.5mm;
                           background: #f9fafb; border: 0.3mm solid #e5e7eb; border-radius: 1mm; break-inside: avoid; page-break-inside: avoid; }
            .ac-hol-item span[style*="background-color"] { display: inline-block !important; width: 2.5mm !important; height: 2.5mm !important; border-radius: 50% !important; flex-shrink: 0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .ac-hol-item span:not([style*="background-color"]) { font-weight: 700; color: #1f2937; }
        `;

                document.getElementById('annual-print-frame')?.remove();

                const iframe = document.createElement('iframe');
                iframe.id = 'annual-print-frame';
                iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';
                document.body.appendChild(iframe);

                const doc = iframe.contentWindow.document;
                doc.open();
                doc.write(`<!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>${org} - Calendar ${year}</title>
                <style>${css}</style>
            </head>
            <body>
                <div class="ac-head">
                    <h1>${org} - Calendar for Year ${year}</h1>
                    <p>Annual Work Calendar &amp; Public Holidays</p>
                </div>
                ${source.innerHTML}
                <script>
                    window.onload = function() {
                        setTimeout(function() {
                            window.focus();
                            window.print();
                        }, 200);
                    };
                <\/script>
            </body>
            </html>`);
                doc.close();

                setTimeout(() => iframe.remove(), 10000);
            };
        </script>
    @endscript


    {{-- ========================================================================= --}}
    {{-- MODAL 1: ADD NEW HOLIDAYS (RANGE OR DISCRETE SELECTION)                   --}}
    {{-- ========================================================================= --}}
    @if ($isFormModalActive)
        @teleport('body')
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[92vh] overflow-y-auto p-6 space-y-6">
                    <div class="flex justify-between items-center border-b pb-3">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Add Public Holidays</h3>
                            <p class="text-xs text-gray-500">Pick continuous date ranges or pick individual dates on
                                calendar and assign custom reasons.</p>
                        </div>
                        <button type="button" wire:click="closeAllActiveModals"
                            class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Mode Selection Toggle --}}
                    <div class="flex gap-3">
                        <button type="button" wire:click="setCreationSelectionMode('range')"
                            class="flex-1 py-2.5 px-4 rounded-xl text-xs font-bold border transition {{ $creationSelectionMode === 'range' ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                            1. Range Selection (e.g. New Year 4 Days Continuous)
                        </button>
                        <button type="button" wire:click="setCreationSelectionMode('multiple')"
                            class="flex-1 py-2.5 px-4 rounded-xl text-xs font-bold border transition {{ $creationSelectionMode === 'multiple' ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                            2. Visual Calendar Picker (Pick Separate Dates On Calendar)
                        </button>
                    </div>

                    {{-- Range Mode Form --}}
                    @if ($creationSelectionMode === 'range')
                        <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Start Date</label>
                                    <input type="date" wire:model="rangeStartDate"
                                        class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500">
                                    @error('rangeStartDate')
                                        <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">End Date</label>
                                    <input type="date" wire:model="rangeEndDate"
                                        class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500">
                                    @error('rangeEndDate')
                                        <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="md:col-span-1">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Scope</label>
                                    <select wire:model.live="rangeCommonType"
                                        class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500">
                                        <option value="Full Day">Full Day</option>
                                        <option value="Half Day">Half Day</option>
                                    </select>
                                </div>
                                @if ($rangeCommonType === 'Half Day')
                                    <div class="md:col-span-1">
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Shift</label>
                                        <select wire:model="rangeCommonShift"
                                            class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500">
                                            <option value="Morning">Morning Shift</option>
                                            <option value="Evening">Evening Shift</option>
                                        </select>
                                    </div>
                                @endif

                                <div class="md:col-span-1">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Category</label>
                                    <select wire:model="rangeCommonCategoryId"
                                        class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500">
                                        <option value="">Select Category</option>
                                        @foreach ($allCategories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('rangeCommonCategoryId')
                                        <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="md:col-span-1">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Common Label</label>
                                    <input type="text" wire:model="rangeCommonDescription"
                                        placeholder="Description for these days"
                                        class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500">
                                    @error('rangeCommonDescription')
                                        <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="button" wire:click="applyContinuousRange"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-sm">
                                    + Populate Range to Staged List
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Mode 2: Interactive Pure Livewire Calendar Grid --}}
                    @if ($creationSelectionMode === 'multiple')
                        <div class="border border-gray-200 rounded-2xl p-4 space-y-3 bg-white">
                            <div class="flex justify-between items-center pb-2 border-b">
                                <button type="button" wire:click="previousPickerMonth"
                                    class="p-1.5 border rounded-lg hover:bg-gray-50 text-gray-700 text-xs font-bold">←
                                    Prev</button>
                                <span
                                    class="text-xs font-bold text-gray-900 uppercase tracking-wider">{{ $pickerMonthName }}
                                    {{ $pickerYear }}</span>
                                <button type="button" wire:click="nextPickerMonth"
                                    class="p-1.5 border rounded-lg hover:bg-gray-50 text-gray-700 text-xs font-bold">Next
                                    →</button>
                            </div>

                            <div class="grid grid-cols-7 text-center font-bold text-[10px] text-gray-400 uppercase py-1">
                                <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                            </div>

                            <div class="grid grid-cols-7 gap-1">
                                @foreach ($pickerCalendarMatrix as $week)
                                    @foreach ($week as $cell)
                                        <button type="button"
                                            @if (!$cell['is_already_registered']) wire:click="togglePickerDateSelection('{{ $cell['date'] }}')" @endif
                                            class="h-10 rounded-xl text-xs font-bold flex items-center justify-center transition 
                            {{ $cell['is_already_registered'] ? 'bg-rose-100 text-rose-700 border border-rose-300 cursor-not-allowed opacity-75' : ($cell['is_selected'] ? 'bg-indigo-600 text-white shadow-sm' : ($cell['is_current_month'] ? 'bg-gray-50 hover:bg-indigo-50 text-gray-800' : 'bg-transparent text-gray-300')) }}"
                                            {{ $cell['is_already_registered'] ? 'title=Already Registered as Holiday' : '' }}>
                                            {{ $cell['day_number'] }}
                                        </button>
                                    @endforeach
                                @endforeach
                            </div>
                            <p class="text-[11px] text-gray-500 italic mt-2">* Highlighted red dates in the calendar are
                                already registered as public holidays.</p>
                        </div>
                    @endif

                    {{-- Staged List Section --}}
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Staged Holidays to Save ({{ count($stagedHolidaysList) }} Dates Selected)
                            </h4>
                        </div>

                        @error('stagedHolidaysList')
                            <span class="text-xs text-rose-500 font-semibold block">{{ $message }}</span>
                        @enderror

                        @if (count($stagedHolidaysList) > 0)
                            <div
                                class="border border-gray-200 rounded-xl overflow-hidden divide-y divide-gray-100 max-h-56 overflow-y-auto">
                                @foreach ($stagedHolidaysList as $index => $item)
                                    <div class="p-3 bg-white flex flex-col md:flex-row items-center gap-3">
                                        <span
                                            class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-200 whitespace-nowrap">
                                            {{ $item['date'] }}
                                        </span>

                                        <div class="w-28">
                                            <select wire:model="stagedHolidaysList.{{ $index }}.holiday_type"
                                                class="w-full text-xs rounded-lg border-gray-300 py-1">
                                                <option value="Full Day">Full Day</option>
                                                <option value="Half Day">Half Day</option>
                                            </select>
                                        </div>

                                        @if (($item['holiday_type'] ?? '') === 'Half Day')
                                            <div class="w-28">
                                                <select wire:model="stagedHolidaysList.{{ $index }}.holiday_shift"
                                                    class="w-full text-xs rounded-lg border-gray-300 py-1">
                                                    <option value="Morning">Morning</option>
                                                    <option value="Evening">Evening</option>
                                                </select>
                                            </div>
                                        @endif

                                        <div class="w-32">
                                            <select wire:model="stagedHolidaysList.{{ $index }}.category_id"
                                                class="w-full text-xs rounded-lg border-gray-300 py-1">
                                                <option value="">Select Category</option>
                                                @foreach ($allCategories as $cat)
                                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                @endforeach
                                            </select>
                                            @error("stagedHolidaysList.{$index}.category_id")
                                                <span class="text-[10px] text-rose-500 block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="flex-1 w-full">
                                            <input type="text"
                                                wire:model="stagedHolidaysList.{{ $index }}.description"
                                                placeholder="Reason (e.g. Special Holiday, Poya Day)"
                                                class="w-full text-xs rounded-lg border-gray-300 py-1">
                                            @error("stagedHolidaysList.{$index}.description")
                                                <span class="text-[10px] text-rose-500 block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <button type="button" wire:click="removeStagedHolidayItem({{ $index }})"
                                            class="text-rose-500 hover:text-rose-700 text-xs font-semibold px-2 py-1">
                                            ✕ Remove
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6 border border-dashed rounded-xl text-gray-400 text-xs">
                                No dates staged yet. Use the range tool or click calendar dates above to populate holidays.
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 border-t flex justify-end gap-3">
                        <button type="button" wire:click="closeAllActiveModals"
                            class="px-4 py-2 border rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="button" wire:click="saveAllStagedHolidays"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm">Save
                            All Records</button>
                    </div>
                </div>
            </div>
        @endteleport
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 2: MODIFY (EDIT) EXISTING HOLIDAY MODAL                             --}}
    {{-- ========================================================================= --}}
    @if ($isEditModalActive)
        @teleport('body')
            <div
                class="fixed inset-0 top-0 left-0 w-screen h-screen z-[9999] flex items-center justify-center bg-gray-900/70 backdrop-blur-sm p-4 m-0">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 space-y-5">
                    <div class="flex justify-between items-center border-b pb-3">
                        <h3 class="text-base font-bold text-gray-900">Modify Holiday Record</h3>
                        <button type="button" wire:click="closeAllActiveModals"
                            class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="updateHolidayRecord" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Holiday Date</label>
                            <input type="date" wire:model="editHolidayDate"
                                class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500">
                            @error('editHolidayDate')
                                <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Type Scope</label>
                                <select wire:model.live="editHolidayType"
                                    class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500">
                                    <option value="Full Day">Full Day</option>
                                    <option value="Half Day">Half Day</option>
                                </select>
                            </div>

                            @if ($editHolidayType === 'Half Day')
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Shift</label>
                                    <select wire:model="editHolidayShift"
                                        class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500">
                                        <option value="Morning">Morning Shift</option>
                                        <option value="Evening">Evening Shift</option>
                                    </select>
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Holiday Category</label>
                            <select wire:model="editCategoryId"
                                class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500">
                                <option value="">Select Category</option>
                                @foreach ($allCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('editCategoryId')
                                <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Descriptive Label</label>
                            <input type="text" wire:model="editHolidayDescription"
                                class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500">
                            @error('editHolidayDescription')
                                <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="pt-4 border-t flex justify-end gap-2.5">
                            <button type="button" wire:click="closeAllActiveModals"
                                class="px-4 py-2 border rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
                            <button type="submit"
                                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm">Update
                                Record</button>
                        </div>
                    </form>
                </div>
            </div>
        @endteleport
    @endif

   {{-- ========================================================================= --}}
    {{-- MODAL 3: COMPANY WORK CALENDAR SETTINGS (FULLY CUSTOMIZABLE MONDAY-SUNDAY) --}}
    {{-- ========================================================================= --}}
    @if ($isConfigModalActive)
        @teleport('body')
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[92vh] overflow-y-auto p-6 space-y-6">
                    <div class="flex justify-between items-center border-b pb-3">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Company Work Calendar Settings</h3>
                            <p class="text-xs text-gray-500">Configure weekly operational days, half-day shifts, and
                                attendance calculation rules per day.</p>
                        </div>
                        <button type="button" wire:click="closeAllActiveModals"
                            class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="saveCalendarConfiguration" class="space-y-4">
                        @foreach ($calendarDaysInput as $index => $day)
                            <div
                                class="p-4 rounded-xl border transition {{ $day['is_working_day'] ? 'bg-white border-gray-200 shadow-sm' : 'bg-gray-50 border-gray-200 opacity-60' }}">
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-center">

                                    {{-- Working Day Toggle --}}
                                    <div class="lg:col-span-2 flex items-center gap-3">
                                        <input type="checkbox"
                                            wire:model.live="calendarDaysInput.{{ $index }}.is_working_day"
                                            id="day-box-{{ $index }}"
                                            class="w-4 h-4 rounded text-indigo-600 border-gray-300 focus:ring-indigo-500 cursor-pointer">
                                        <label for="day-box-{{ $index }}"
                                            class="text-sm font-bold cursor-pointer select-none {{ $day['is_working_day'] ? 'text-gray-900' : 'text-gray-400 line-through' }}">
                                            {{ $day['day_of_week'] }}
                                        </label>
                                    </div>

                                    @if ($day['is_working_day'])
                                        {{-- Day Type Selector --}}
                                        <div class="lg:col-span-2">
                                            <select wire:model.live="calendarDaysInput.{{ $index }}.day_type"
                                                class="w-full text-xs rounded-xl border-gray-300 py-1.5">
                                                <option value="Full Day">Full Working Day</option>
                                                <option value="Half Day">Half Working Day</option>
                                            </select>
                                        </div>

                                        {{-- Calculation Rules & Times Matrix --}}
                                        <div class="lg:col-span-8">
                                            @if ($day['day_type'] === 'Half Day')
                                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                                                    {{-- Shift Selector (Col span: 3) --}}
                                                    <div class="sm:col-span-3">
                                                        <select
                                                            wire:model="calendarDaysInput.{{ $index }}.half_day_shift"
                                                            class="w-full text-xs rounded-xl border-purple-200 bg-purple-50 text-purple-900 py-1.5 font-medium">
                                                            <option value="Morning">Morning</option>
                                                            <option value="Evening">Evening</option>
                                                        </select>
                                                    </div>

                                                    {{-- Half Day Start & End Time Inputs with proper gap (Col span: 5) --}}
                                                    <div class="sm:col-span-5 flex items-center gap-2">
                                                        <div class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-2 py-1">
                                                            <input type="time"
                                                                wire:model="calendarDaysInput.{{ $index }}.half_day_start_time"
                                                                class="w-full text-[11px] bg-transparent border-0 p-0 focus:ring-0 text-gray-800 font-medium"
                                                                title="Start Time">
                                                        </div>
                                                        <span class="text-gray-400 text-xs font-bold">-</span>
                                                        <div class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-2 py-1">
                                                            <input type="time"
                                                                wire:model="calendarDaysInput.{{ $index }}.half_day_end_time"
                                                                class="w-full text-[11px] bg-transparent border-0 p-0 focus:ring-0 text-gray-800 font-medium"
                                                                title="End Time">
                                                        </div>
                                                    </div>

                                                    {{-- Present & Absent Calculation Rules (Col span: 4) --}}
                                                    <div class="sm:col-span-4 grid grid-cols-2 gap-1">
                                                        <select
                                                            wire:model="calendarDaysInput.{{ $index }}.present_calculation_rule"
                                                            class="w-full text-[10px] rounded-lg border-green-200 bg-green-50 text-green-900 py-1.5 px-0.5 font-medium"
                                                            title="Present Rule">
                                                            <option value="0.5">0.5 Day</option>
                                                            <option value="1.0">1.0 Day</option>
                                                        </select>
                                                        <select
                                                            wire:model="calendarDaysInput.{{ $index }}.absent_calculation_rule"
                                                            class="w-full text-[10px] rounded-lg border-rose-200 bg-rose-50 text-rose-900 py-1.5 px-0.5 font-medium"
                                                            title="Absent Rule">
                                                            <option value="0.5">0.5 Cut</option>
                                                            <option value="1.0">1.0 Cut</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            @else
                                                <div
                                                    class="text-center py-1.5 bg-gray-50 border rounded-xl text-gray-500 text-xs font-medium">
                                                    Standard Full Day (1.0 Credit / 1.0 Cut)
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="lg:col-span-10 text-rose-500 font-bold text-xs">
                                            Weekend / Company Non-Working Day
                                        </div>
                                    @endif

                                </div>
                            </div>
                        @endforeach

                        <div class="pt-4 border-t flex justify-end gap-3">
                            <button type="button" wire:click="closeAllActiveModals"
                                class="px-4 py-2 border rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
                            <button type="submit"
                                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm">Save
                                Calendar Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        @endteleport
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 4: VIEW DETAILS MODAL                                               --}}
    {{-- ========================================================================= --}}
    @if ($isViewModalActive && $selectedHolidayForView)
        @teleport('body')
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
                    <h3 class="text-base font-bold text-gray-900 border-b pb-2">Holiday Record Details</h3>
                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-gray-400 font-semibold uppercase block">Date</span>
                            <span
                                class="text-gray-900 font-bold text-sm">{{ $selectedHolidayForView['holiday_date'] }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 font-semibold uppercase block">Scope</span>
                            <span class="text-indigo-600 font-semibold">{{ $selectedHolidayForView['holiday_type'] }}
                                {{ $selectedHolidayForView['holiday_shift'] ? '(' . $selectedHolidayForView['holiday_shift'] . ')' : '' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 font-semibold uppercase block">Category</span>
                            @if (isset($selectedHolidayForView['category']) && $selectedHolidayForView['category'])
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-xs font-bold text-white inline-flex items-center gap-1 mt-1"
                                    style="background-color: {{ $selectedHolidayForView['category']['color_code'] }};">
                                    {{ $selectedHolidayForView['category']['name'] }}
                                </span>
                            @else
                                <span class="text-gray-800 font-medium">-</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-400 font-semibold uppercase block">Description</span>
                            <span class="text-gray-800 font-medium">{{ $selectedHolidayForView['description'] }}</span>
                        </div>
                    </div>
                    <div class="pt-3 border-t flex justify-end">
                        <button type="button" wire:click="closeAllActiveModals"
                            class="px-4 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold">Close</button>
                    </div>
                </div>
            </div>
        @endteleport
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 5: DELETE CONFIRMATION MODAL                                        --}}
    {{-- ========================================================================= --}}
    @if ($isDeleteModalActive)
        @teleport('body')
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center space-y-4">
                    @if ($isHistoricalTargetSelected)
                        <h3 class="text-base font-bold text-gray-900">Historical Record Locked</h3>
                        <p class="text-xs text-gray-500">Past holidays cannot be removed to preserve payroll and attendance
                            audit integrity.</p>
                        <button type="button" wire:click="closeAllActiveModals"
                            class="px-4 py-2 bg-gray-100 text-gray-700 text-xs font-semibold rounded-xl">Close</button>
                    @else
                        <h3 class="text-base font-bold text-gray-900">Confirm Deletion</h3>
                        <p class="text-xs text-gray-500">Are you sure you want to permanently remove
                            "{{ $targetHolidayTitleForDeletion }}"?</p>
                        <div class="flex justify-center gap-3 pt-2">
                            <button type="button" wire:click="closeAllActiveModals"
                                class="px-4 py-2 border rounded-xl text-xs font-semibold text-gray-600">Cancel</button>
                            <button type="button" wire:click="executeHolidayDeletion"
                                class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl">Delete</button>
                        </div>
                    @endif
                </div>
            </div>
        @endteleport
    @endif

</div>
