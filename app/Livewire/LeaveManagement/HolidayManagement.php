<?php

namespace App\Livewire\LeaveManagement;

use Livewire\WithPagination;
use Livewire\Component;
use App\Models\PublicHoliday;
use App\Models\Organisation;
use App\Models\CompanyCalendarSetting;
use Carbon\Carbon;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class HolidayManagement extends Component
{
    use WithPagination;

    // View Navigation (table vs calendar view)
    public string $activeViewTab = 'table';
    public int $calendarViewYear;
    public int $calendarViewMonth;

    // Filters for Table View
    public string $filterSelectedYear = '';
    public string $filterSelectedMonth = '';
    public string $filterSelectedType = '';
    public bool $isYearDropdownOpen = false;
    public bool $isMonthDropdownOpen = false;
    public bool $isTypeDropdownOpen = false;

    // Calendar Settings Modal State
    public bool $isConfigModalActive = false;
    public array $calendarDaysInput = [];

    // Add Holidays Modal State (Interactive Visual Picker)
    public bool $isFormModalActive = false;
    public string $creationSelectionMode = 'range'; // 'range' or 'multiple'
    public int $pickerYear;
    public int $pickerMonth;

    // Range Mode Inputs
    public string $rangeStartDate = '';
    public string $rangeEndDate = '';
    public string $rangeCommonDescription = '';
    public string $rangeCommonType = 'Full Day';
    public string $rangeCommonShift = 'Morning';
    public ?int $rangeCommonCategoryId = null;

    // Staged Holiday Records for Insertion
    public array $stagedHolidaysList = [];

    // Edit Modal State
    public bool $isEditModalActive = false;
    public ?int $editingHolidayId = null;
    public string $editHolidayDate = '';
    public string $editHolidayType = 'Full Day';
    public ?string $editHolidayShift = 'Morning';
    public string $editHolidayDescription = '';
    public ?int $editCategoryId = null;

    // View & Delete Modal States
    public bool $isViewModalActive = false;
    public ?array $selectedHolidayForView = null;
    public bool $isDeleteModalActive = false;
    public ?int $targetHolidayIdForDeletion = null;
    public ?string $targetHolidayTitleForDeletion = null;
    public bool $isHistoricalTargetSelected = false;

    public int $annualViewYear;

    public function mount(): void
    {
        $currentDate = Carbon::now();
        $this->filterSelectedYear = $currentDate->format('Y');
        $this->calendarViewYear = (int) $currentDate->format('Y');
        $this->calendarViewMonth = (int) $currentDate->format('n');
        $this->pickerYear = (int) $currentDate->format('Y');
        $this->pickerMonth = (int) $currentDate->format('n');
        $this->annualViewYear = (int) $currentDate->format('Y');

        $this->loadCalendarConfiguration();
    }

    // --- Tab Switcher ---
    public function setViewTab(string $tabName): void
    {
        $this->activeViewTab = $tabName;
    }

    // --- Government Calendar Navigation ---
    public function nextCalendarMonth(): void
    {
        $target = Carbon::createFromDate($this->calendarViewYear, $this->calendarViewMonth, 1)->addMonth();
        $this->calendarViewYear = (int) $target->format('Y');
        $this->calendarViewMonth = (int) $target->format('n');
    }

    public function previousCalendarMonth(): void
    {
        $target = Carbon::createFromDate($this->calendarViewYear, $this->calendarViewMonth, 1)->subMonth();
        $this->calendarViewYear = (int) $target->format('Y');
        $this->calendarViewMonth = (int) $target->format('n');
    }

    // --- Visual Date Picker Navigation (Inside Creation Modal) ---
    public function nextPickerMonth(): void
    {
        $target = Carbon::createFromDate($this->pickerYear, $this->pickerMonth, 1)->addMonth();
        $this->pickerYear = (int) $target->format('Y');
        $this->pickerMonth = (int) $target->format('n');
    }

    public function previousPickerMonth(): void
    {
        $target = Carbon::createFromDate($this->pickerYear, $this->pickerMonth, 1)->subMonth();
        $this->pickerYear = (int) $target->format('Y');
        $this->pickerMonth = (int) $target->format('n');
    }

    public function nextAnnualYear(): void
    {
        $this->annualViewYear++;
    }

    public function previousAnnualYear(): void
    {
        $this->annualViewYear--;
    }

    public function setCreationSelectionMode(string $mode): void
    {
        $this->creationSelectionMode = $mode;
    }

    // --- Interactive Date Selection (No JS) ---
    public function togglePickerDateSelection(string $dateString): void
    {
        $existingIndex = null;
        foreach ($this->stagedHolidaysList as $index => $item) {
            if ($item['date'] === $dateString) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            unset($this->stagedHolidaysList[$existingIndex]);
            $this->stagedHolidaysList = array_values($this->stagedHolidaysList);
        } else {
            $this->stagedHolidaysList[] = [
                'date'          => $dateString,
                'description'   => '',
                'holiday_type'  => 'Full Day',
                'holiday_shift' => 'Morning',
                'category_id'   => null,
            ];
        }
    }

    public function removeStagedHolidayItem(int $index): void
    {
        if (isset($this->stagedHolidaysList[$index])) {
            unset($this->stagedHolidaysList[$index]);
            $this->stagedHolidaysList = array_values($this->stagedHolidaysList);
        }
    }

    public function applyContinuousRange(): void
    {
        $this->validate([
            'rangeStartDate'         => 'required|date',
            'rangeEndDate'           => 'required|date|after_or_equal:rangeStartDate',
            'rangeCommonDescription' => 'required|string|min:2|max:255',
            'rangeCommonType'        => 'required|string|in:Full Day,Half Day',
            'rangeCommonShift'       => 'required_if:rangeCommonType,Half Day|in:Morning,Evening',
            'rangeCommonCategoryId'  => 'required|exists:holiday_categories,id',
        ]);

        $start = Carbon::parse($this->rangeStartDate);
        $end = Carbon::parse($this->rangeEndDate);

        while ($start->lte($end)) {
            $dateString = $start->format('Y-m-d');
            
            $found = false;
            foreach ($this->stagedHolidaysList as $index => $item) {
                if ($item['date'] === $dateString) {
                    $this->stagedHolidaysList[$index]['description'] = $this->rangeCommonDescription;
                    $this->stagedHolidaysList[$index]['holiday_type'] = $this->rangeCommonType;
                    $this->stagedHolidaysList[$index]['holiday_shift'] = ($this->rangeCommonType === 'Half Day') ? $this->rangeCommonShift : null;
                    $this->stagedHolidaysList[$index]['category_id'] = $this->rangeCommonCategoryId;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $this->stagedHolidaysList[] = [
                    'date'          => $dateString,
                    'description'   => $this->rangeCommonDescription,
                    'holiday_type'  => $this->rangeCommonType,
                    'holiday_shift' => ($this->rangeCommonType === 'Half Day') ? $this->rangeCommonShift : null,
                    'category_id'   => $this->rangeCommonCategoryId,
                ];
            }

            $start->addDay();
        }

        $this->reset(['rangeStartDate', 'rangeEndDate', 'rangeCommonDescription', 'rangeCommonCategoryId']);
        $this->rangeCommonType = 'Full Day';
    }

    public function saveAllStagedHolidays(): void
    {
        if (empty($this->stagedHolidaysList)) {
            $this->addError('stagedHolidaysList', 'Please select at least one holiday date to record.');
            return;
        }

        foreach ($this->stagedHolidaysList as $index => $item) {
            if (empty(trim($item['description'] ?? ''))) {
                $this->addError("stagedHolidaysList.{$index}.description", "Description is mandatory for date {$item['date']}.");
                return;
            }

            if (empty($item['category_id'])) {
                $this->addError("stagedHolidaysList.{$index}.category_id", "Category is mandatory for date {$item['date']}.");
                return;
            }

            $exists = PublicHoliday::where('holiday_date', $item['date'])->exists();
            if ($exists) {
                $this->addError('stagedHolidaysList', "The date {$item['date']} has already been registered as a public holiday.");
                return;
            }
        }

        foreach ($this->stagedHolidaysList as $item) {
            PublicHoliday::create([
                'holiday_date'  => $item['date'],
                'holiday_type'  => $item['holiday_type'],
                'holiday_shift' => ($item['holiday_type'] === 'Half Day') ? ($item['holiday_shift'] ?? 'Morning') : null,
                'description'   => $item['description'],
                'category_id'   => $item['category_id'],
            ]);
        }

        $this->closeAllActiveModals();
        session()->flash('holidayActionFeedback', 'Public holidays successfully recorded.');
    }

    // --- Modify (Edit) Single Record Logic ---
    public function triggerModificationModal(int $targetHolidayId): void
    {
        $holiday = PublicHoliday::findOrFail($targetHolidayId);

        if (Carbon::parse($holiday->holiday_date)->startOfDay()->isBefore(Carbon::now()->startOfDay())) {
            session()->flash('holidayActionError', 'Past holiday records cannot be modified.');
            return;
        }

        $this->editingHolidayId        = $holiday->id;
        $this->editHolidayDate         = Carbon::parse($holiday->holiday_date)->format('Y-m-d');
        $this->editHolidayType         = $holiday->holiday_type;
        $this->editHolidayShift        = $holiday->holiday_shift ?? 'Morning';
        $this->editHolidayDescription  = $holiday->description;
        $this->editCategoryId          = $holiday->category_id;

        $this->isEditModalActive = true;
    }

    public function updateHolidayRecord(): void
    {
        $this->validate([
            'editHolidayDate'        => 'required|date',
            'editHolidayType'        => 'required|string|in:Full Day,Half Day',
            'editHolidayShift'       => 'required_if:editHolidayType,Half Day|nullable|in:Morning,Evening',
            'editHolidayDescription' => 'required|string|min:2|max:255',
            'editCategoryId'         => 'required|exists:holiday_categories,id',
        ]);

        $formattedDate = Carbon::parse($this->editHolidayDate)->format('Y-m-d');

        if (Carbon::parse($formattedDate)->startOfDay()->isBefore(Carbon::now()->startOfDay())) {
            $this->addError('editHolidayDate', 'You cannot set a holiday to a past date.');
            return;
        }

        $exists = PublicHoliday::where('holiday_date', $formattedDate)
            ->where('id', '!=', $this->editingHolidayId)
            ->exists();

        if ($exists) {
            $this->addError('editHolidayDate', 'This date has already been registered as a public holiday.');
            return;
        }

        $record = PublicHoliday::findOrFail($this->editingHolidayId);
        $record->update([
            'holiday_date'  => $formattedDate,
            'holiday_type'  => $this->editHolidayType,
            'holiday_shift' => ($this->editHolidayType === 'Half Day') ? $this->editHolidayShift : null,
            'description'   => $this->editHolidayDescription,
            'category_id'   => $this->editCategoryId,
        ]);

        $this->closeAllActiveModals();
        session()->flash('holidayActionFeedback', 'Holiday record successfully updated.');
    }

    // --- Company Calendar Settings Logic ---
    public function openConfigModal(): void
    {
        $this->loadCalendarConfiguration();
        $this->isConfigModalActive = true;
    }

    private function buildAnnualCalendarMatrix(int $year): array
    {
        $monthsData = [];
        
        $holidays = PublicHoliday::with('category')->whereYear('holiday_date', $year)->get()->keyBy(function ($item) {
            return Carbon::parse($item->holiday_date)->format('Y-m-d');
        });

        for ($m = 1; $m <= 12; $m++) {
            $firstOfMonth = Carbon::createFromDate($year, $m, 1)->startOfMonth();
            $lastOfMonth = $firstOfMonth->copy()->endOfMonth();

            $startOfGrid = $firstOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
            $endOfGrid = $lastOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

            $weeks = [];
            $cursor = $startOfGrid->copy();

            while ($cursor->lte($endOfGrid)) {
                $week = [];
                for ($i = 0; $i < 7; $i++) {
                    $dateKey = $cursor->format('Y-m-d');
                    $holiday = $holidays->get($dateKey);
                    
                    $isHoliday = ($holiday !== null);
                    $holidayTitle = $holiday ? $holiday->description : null;
                    $isPoya = $isHoliday && (stripos($holidayTitle, 'poya') !== false);

                    $week[] = [
                        'date'             => $dateKey,
                        'day_number'       => $cursor->day,
                        'is_current_month' => ($cursor->month === $m),
                        'is_today'         => $cursor->isToday(),
                        'is_sunday'        => $cursor->isSunday(),
                        'has_holiday'      => $isHoliday,
                        'is_poya'          => $isPoya,
                        'holiday_title'    => $holidayTitle,
                        'category_name'    => $holiday?->category?->name ?? 'Holiday',
                        'color_code'       => $holiday?->category?->color_code ?? '#e11d48',
                    ];
                    $cursor->addDay();
                }
                $weeks[] = $week;
            }

            $monthsData[] = [
                'month_number' => $m,
                'month_name'   => Carbon::createFromDate($year, $m, 1)->format('F'),
                'weeks'        => $weeks,
            ];
        }

        return $monthsData;
    }

    public function loadCalendarConfiguration(): void
    {
        $settings = CompanyCalendarSetting::orderBy('id', 'asc')->get();

        if ($settings->isEmpty()) {
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            foreach ($days as $day) {
                CompanyCalendarSetting::create([
                    'day_of_week'              => $day,
                    'is_working_day'           => ($day !== 'Sunday'),
                    'day_type'                 => ($day === 'Saturday') ? 'Half Day' : 'Full Day',
                    'half_day_shift'           => ($day === 'Saturday') ? 'Morning' : null,
                    'half_day_start_time'      => '08:00',
                    'half_day_end_time'        => '12:00',
                    'present_calculation_rule' => '1.0',
                    'absent_calculation_rule'  => '1.0',
                ]);
            }
            $settings = CompanyCalendarSetting::orderBy('id', 'asc')->get();
        }

        $this->calendarDaysInput = $settings->toArray();
    }

   public function saveCalendarConfiguration(): void
    {
        foreach ($this->calendarDaysInput as $dayData) {
            $setting = CompanyCalendarSetting::find($dayData['id']);
            if ($setting) {
                $isWorking = (bool) $dayData['is_working_day'];
                $dayType = $isWorking ? $dayData['day_type'] : 'Full Day';
                $halfDayShift = ($isWorking && $dayType === 'Half Day') ? ($dayData['half_day_shift'] ?? 'Morning') : null;
                
                $startTime = $dayData['half_day_start_time'] ?? '08:00';
                $endTime   = $dayData['half_day_end_time'] ?? '12:00';

                $presentRule = $isWorking ? ($dayData['present_calculation_rule'] ?? '1.0') : '1.0';
                $absentRule = $isWorking ? ($dayData['absent_calculation_rule'] ?? '1.0') : '1.0';

                $setting->update([
                    'is_working_day'           => $isWorking,
                    'day_type'                 => $dayType,
                    'half_day_shift'           => $halfDayShift,
                    'half_day_start_time'      => $startTime,
                    'half_day_end_time'        => $endTime,
                    'present_calculation_rule' => $presentRule,
                    'absent_calculation_rule'  => $absentRule,
                ]);
            }
        }

        session()->flash('holidayActionFeedback', 'Company calendar rules and shift times updated successfully.');
        $this->closeAllActiveModals();
    }

    // --- Modal Triggers & Actions ---
    public function triggerCreationModal(): void
    {
        $this->stagedHolidaysList = [];
        $this->reset(['rangeStartDate', 'rangeEndDate', 'rangeCommonDescription', 'rangeCommonCategoryId']);
        $this->isFormModalActive = true;
    }

    public function triggerViewModal(int $targetHolidayId): void
    {
        $record = PublicHoliday::with('category')->findOrFail($targetHolidayId);
        $this->selectedHolidayForView = $record->toArray();
        $this->isViewModalActive = true;
    }

    public function triggerDeleteConfirmationModal(int $targetHolidayId, string $targetHolidayDescription): void
    {
        $record = PublicHoliday::findOrFail($targetHolidayId);
        $this->isHistoricalTargetSelected = Carbon::parse($record->holiday_date)->startOfDay()->isBefore(Carbon::now()->startOfDay());
        $this->targetHolidayIdForDeletion = $targetHolidayId;
        $this->targetHolidayTitleForDeletion = $targetHolidayDescription;
        $this->isDeleteModalActive = true;
    }

    public function executeHolidayDeletion(): void
    {
        if ($this->isHistoricalTargetSelected) {
            return;
        }

        if ($this->targetHolidayIdForDeletion) {
            PublicHoliday::findOrFail($this->targetHolidayIdForDeletion)->delete();
        }

        $this->closeAllActiveModals();
        session()->flash('holidayActionFeedback', 'Holiday record removed successfully.');
    }

    public function closeAllActiveModals(): void
    {
        $this->isFormModalActive = false;
        $this->isEditModalActive = false;
        $this->isDeleteModalActive = false;
        $this->isConfigModalActive = false;
        $this->isViewModalActive = false;
        $this->stagedHolidaysList = [];
        $this->editingHolidayId = null;
        $this->editCategoryId = null;
    }

    // --- Filter Handlers ---
    public function selectYearFilter(string $year): void
    {
        $this->filterSelectedYear = $year;
        $this->isYearDropdownOpen = false;
        $this->resetPage();
    }

    public function selectMonthFilter(string $month): void
    {
        $this->filterSelectedMonth = $month;
        $this->isMonthDropdownOpen = false;
        $this->resetPage();
    }

    public function selectTypeFilter(string $type): void
    {
        $this->filterSelectedType = $type;
        $this->isTypeDropdownOpen = false;
        $this->resetPage();
    }

    // --- Helper Matrix Generators ---
    private function buildMonthlyCalendarMatrix(int $year, int $month): array
    {
        $firstOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $lastOfMonth = $firstOfMonth->copy()->endOfMonth();

        $startOfGrid = $firstOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfGrid = $lastOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        $holidays = PublicHoliday::with('category')->whereBetween('holiday_date', [
            $startOfGrid->format('Y-m-d'),
            $endOfGrid->format('Y-m-d')
        ])->get()->keyBy(function ($item) {
            return Carbon::parse($item->holiday_date)->format('Y-m-d');
        });

        $weeks = [];
        $cursor = $startOfGrid->copy();

        while ($cursor->lte($endOfGrid)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dateKey = $cursor->format('Y-m-d');
                $holiday = $holidays->get($dateKey);

                $week[] = [
                    'date'             => $dateKey,
                    'day_number'       => $cursor->day,
                    'is_current_month' => ($cursor->month === $month),
                    'is_today'         => $cursor->isToday(),
                    'is_sunday'        => $cursor->isSunday(),
                    'has_holiday'      => ($holiday !== null),
                    'holiday_title'    => $holiday ? $holiday->description : null,
                    'holiday_type'     => $holiday ? $holiday->holiday_type : null,
                    'holiday_shift'    => $holiday ? $holiday->holiday_shift : null,
                    'category_name'    => $holiday?->category?->name ?? 'Holiday',
                    'color_code'       => $holiday?->category?->color_code ?? '#e11d48',
                ];
                $cursor->addDay();
            }
            $weeks[] = $week;
        }

        return $weeks;
    }

    private function buildPickerCalendarMatrix(int $year, int $month): array
    {
        $firstOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $lastOfMonth = $firstOfMonth->copy()->endOfMonth();

        $startOfGrid = $firstOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfGrid = $lastOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        $existingHolidays = PublicHoliday::whereBetween('holiday_date', [
            $startOfGrid->format('Y-m-d'),
            $endOfGrid->format('Y-m-d')
        ])->pluck('holiday_type', 'holiday_date')->toArray();

        $selectedDatesMap = [];
        foreach ($this->stagedHolidaysList ?? [] as $item) {
            $selectedDatesMap[$item['date']] = true;
        }

        $weeks = [];
        $cursor = $startOfGrid->copy();

        while ($cursor->lte($endOfGrid)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dateKey = $cursor->format('Y-m-d');
                $week[] = [
                    'date'                  => $dateKey,
                    'day_number'            => $cursor->day,
                    'is_current_month'      => ($cursor->month === $month),
                    'is_today'              => $cursor->isToday(),
                    'is_selected'           => isset($selectedDatesMap[$dateKey]),
                    'is_already_registered' => isset($existingHolidays[$dateKey]),
                ];
                $cursor->addDay();
            }
            $weeks[] = $week;
        }

        return $weeks;
    }

    private function fetchFilteredHolidaysDataset()
    {
        $queryBuilder = PublicHoliday::query()->with('category');

        if (!empty($this->filterSelectedYear)) {
            $queryBuilder->whereYear('holiday_date', $this->filterSelectedYear);
        }

        if (!empty($this->filterSelectedMonth)) {
            $queryBuilder->whereMonth('holiday_date', $this->filterSelectedMonth);
        }

        if (!empty($this->filterSelectedType)) {
            $queryBuilder->where('holiday_type', $this->filterSelectedType);
        }

        $paginated = $queryBuilder->orderBy('holiday_date', 'asc')->paginate(10);

        $paginated->through(function ($item) {
            $item->is_historical_date = Carbon::parse($item->holiday_date)->startOfDay()->isBefore(Carbon::now()->startOfDay());
            return $item;
        });

        return $paginated;
    }

    private function getMonthsMappingList(): array
    {
        return [
            '' => 'All Months',
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ];
    }

    private function getAnnualHolidaysList()
    {
        return PublicHoliday::with('category')->whereYear('holiday_date', $this->annualViewYear)
            ->orderBy('holiday_date', 'asc')
            ->get();
    }
    
    private function getOrganisationName(): string
    {
        return Organisation::first()?->name ?? 'Inbizsys';
    }

    public function render()

    {
        $annualHolidaysList = $this->getAnnualHolidaysList();
        $annualCategories = $annualHolidaysList->pluck('category')->filter()->unique('id');

        return view('livewire.LeaveManagement.holiday-management', [
            'registeredHolidaysDataset' => $this->fetchFilteredHolidaysDataset(),
            'monthsMappingList'         => $this->getMonthsMappingList(),
            'governmentCalendarMatrix'  => $this->buildMonthlyCalendarMatrix($this->calendarViewYear, $this->calendarViewMonth),
            'calendarViewMonthName'     => Carbon::createFromDate($this->calendarViewYear, $this->calendarViewMonth, 1)->format('F'),
            'pickerCalendarMatrix'      => $this->buildPickerCalendarMatrix($this->pickerYear, $this->pickerMonth),
            'pickerMonthName'           => Carbon::createFromDate($this->pickerYear, $this->pickerMonth, 1)->format('F'),
            'annualCalendarMonths'      => $this->buildAnnualCalendarMatrix($this->annualViewYear),
            'annualHolidaysList'        => $annualHolidaysList,
            'annualCategories'          => $annualCategories,
            'organisationName'          => $this->getOrganisationName(),
            'allCategories'             => \App\Models\HolidayCategory::all(),
        ]);
    }
}