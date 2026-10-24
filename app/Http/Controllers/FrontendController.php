<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\Tenancy\TenantManager;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Department;
use App\Models\DoctorChart;
use App\Models\DoctorTestimonial;
use App\Models\HealthCheckupPackage;
use App\Models\Hospital;
use App\Models\PackageBooking;
use App\Models\Patient;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class FrontendController extends Controller
{
    /**
     * Resolve default hospital tenant
     */
    protected function getActiveHospital(): ?Hospital
    {
        $hospital = TenantManager::getHospital();
        if (! $hospital) {
            $hospital = Hospital::where('is_active', true)->first();
            if ($hospital) {
                TenantManager::setActiveHospital($hospital);
            }
        }

        return $hospital;
    }

    /**
     * Helper to load scoped system settings
     */
    protected function getPortalSettings(?Hospital $hospital, $selectedBranchId = null): array
    {
        $hospitalId = $hospital?->id;

        return [
            'site_name' => SystemSetting::get('site_name', $hospital?->name ?? 'Apollo Hospitals Healthcare Network', $selectedBranchId, $hospitalId),
            'brand_tagline' => SystemSetting::get('brand_tagline', 'Touching Lives • World-Class Clinical Excellence', $selectedBranchId, $hospitalId),
            'hero_title' => SystemSetting::get('frontend_hero_title', "India's Leading Multi-Super Specialty Healthcare Network", $selectedBranchId, $hospitalId),
            'hero_subtitle' => SystemSetting::get('frontend_hero_subtitle', 'Book instant confirmed OPD consultation tokens, check specialist doctor duty schedules, and access preventive health checkups.', $selectedBranchId, $hospitalId),
            'announcement_enabled' => SystemSetting::get('announcement_enabled', true, $selectedBranchId, $hospitalId),
            'announcement_text' => SystemSetting::get('announcement_text', '⚡ Free Preventive Health Screening Camp this week at all branches! Walk-in ECG & Blood Sugar consultations available.', $selectedBranchId, $hospitalId),
            'allow_online_booking' => SystemSetting::get('allow_online_booking', true, $selectedBranchId, $hospitalId),
            'show_doctor_fees' => SystemSetting::get('show_doctor_fees', true, $selectedBranchId, $hospitalId),
            'emergency_hotline' => SystemSetting::get('emergency_hotline', '1066 / +91 22 2656 7890', $selectedBranchId, $hospitalId),
            'ambulance_phone' => SystemSetting::get('ambulance_phone', '108 / +91 22 2656 7899', $selectedBranchId, $hospitalId),
            'whatsapp_number' => SystemSetting::get('whatsapp_number', '+91 98200 12345', $selectedBranchId, $hospitalId),
            'support_email' => SystemSetting::get('support_email', 'care@apollocare.in', $selectedBranchId, $hospitalId),
            'hospital_address' => SystemSetting::get('hospital_address', 'Apollo Hospital Complex, Dr. E. Moses Road, Mahalaxmi, Mumbai, Maharashtra 400011', $selectedBranchId, $hospitalId),
            'opd_hours' => SystemSetting::get('opd_hours', 'Mon - Sat: 08:00 AM - 08:00 PM (Emergency 24x7)', $selectedBranchId, $hospitalId),
            'currency_symbol' => SystemSetting::get('currency_symbol', '₹', $selectedBranchId, $hospitalId),
            'branch_notice' => $selectedBranchId ? SystemSetting::get('branch_notice', null, $selectedBranchId, $hospitalId) : null,
        ];
    }

    /**
     * Switch Frontend Language (strictly Hindi 'hi' and Marathi 'mr' only)
     */
    public function setLanguage(Request $request, string $locale)
    {
        $allowed = ['hi', 'mr'];
        if (in_array($locale, $allowed, true)) {
            session(['app_locale' => $locale]);
            App::setLocale($locale);
        }

        return back();
    }

    /**
     * Frontend Home Landing Page
     */
    public function home(Request $request)
    {
        $hospital = $this->getActiveHospital();
        if (! $hospital) {
            return redirect()->route('login');
        }

        $selectedBranchId = $request->query('branch_id');
        $branch = null;
        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $branch = Branch::where('hospital_id', $hospital->id)->find($selectedBranchId);
        }

        $settings = $this->getPortalSettings($hospital, $selectedBranchId);

        // Active operational branches
        $branches = Branch::where('hospital_id', $hospital->id)
            ->where('is_active', true)
            ->get();

        // Clinical Departments
        $departments = Department::where('hospital_id', $hospital->id)
            ->where('is_active', true)
            ->get();

        // Featured Doctor Charts
        $doctorQuery = DoctorChart::where('hospital_id', $hospital->id)
            ->where('is_active', true)
            ->with(['department', 'branch', 'doctorUser', 'testimonials']);

        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $doctorQuery->where('branch_id', (int) $selectedBranchId);
        }

        $doctorCharts = $doctorQuery->orderBy('doctor_name')->get();

        // Featured Health Packages
        $packages = HealthCheckupPackage::where('hospital_id', $hospital->id)
            ->where('is_active', true)
            ->when($selectedBranchId && $selectedBranchId !== 'all', function ($q) use ($selectedBranchId) {
                $q->where(fn ($sub) => $sub->whereNull('branch_id')->orWhere('branch_id', $selectedBranchId));
            })
            ->orderBy('is_popular', 'desc')
            ->limit(4)
            ->get();

        // Featured Patient Testimonials
        $testimonials = DoctorTestimonial::where('hospital_id', $hospital->id)
            ->where('is_published', true)
            ->with(['doctorChart', 'branch'])
            ->orderByDesc('rating')
            ->limit(6)
            ->get();

        // Live Tracker search query
        $trackedAppointments = collect();
        $trackingQuery = trim($request->query('track_query', ''));
        if ($trackingQuery !== '') {
            $trackedAppointments = Appointment::where('hospital_id', $hospital->id)
                ->where(function ($q) use ($trackingQuery) {
                    $q->where('token_number', $trackingQuery)
                        ->orWhereHas('patient', function ($pq) use ($trackingQuery) {
                            $pq->where('phone', 'like', "%{$trackingQuery}%")
                                ->orWhere('mrn', 'like', "%{$trackingQuery}%")
                                ->orWhere('name', 'like', "%{$trackingQuery}%");
                        });
                })
                ->with(['patient', 'doctor', 'branch', 'department'])
                ->orderByDesc('appointment_date')
                ->limit(5)
                ->get();
        }

        // Live Operational Metrics
        $metrics = [
            'total_branches' => $branches->count(),
            'total_doctors' => $doctorCharts->count(),
            'total_specialties' => $departments->count(),
            'appointments_today' => Appointment::where('hospital_id', $hospital->id)
                ->whereDate('appointment_date', now()->toDateString())
                ->count(),
        ];

        return view('frontend.home', compact(
            'hospital',
            'branch',
            'selectedBranchId',
            'settings',
            'branches',
            'departments',
            'doctorCharts',
            'packages',
            'testimonials',
            'trackingQuery',
            'trackedAppointments',
            'metrics'
        ));
    }

    /**
     * Backward-compatible index alias
     */
    public function index(Request $request)
    {
        return $this->home($request);
    }

    /**
     * About Us Page
     */
    public function about(Request $request)
    {
        $hospital = $this->getActiveHospital();
        $settings = $this->getPortalSettings($hospital);

        $branches = Branch::where('hospital_id', $hospital?->id)
            ->where('is_active', true)
            ->get();

        $departments = Department::where('hospital_id', $hospital?->id)
            ->where('is_active', true)
            ->get();

        $doctorsCount = DoctorChart::where('hospital_id', $hospital?->id)
            ->where('is_active', true)
            ->count();

        return view('frontend.about', compact(
            'hospital',
            'settings',
            'branches',
            'departments',
            'doctorsCount'
        ));
    }

    /**
     * Location-Wise Hospital & Branch Search Page
     */
    public function locations(Request $request)
    {
        $hospital = $this->getActiveHospital();
        $settings = $this->getPortalSettings($hospital);

        $query = Branch::with(['hospital', 'departments'])
            ->where('hospital_id', $hospital?->id)
            ->where('is_active', true);

        // Filter by state
        if ($request->filled('state') && $request->query('state') !== 'all') {
            $query->where('state', $request->query('state'));
        }

        // Filter by city
        if ($request->filled('city') && $request->query('city') !== 'all') {
            $query->where('city', $request->query('city'));
        }

        // Search query
        $search = trim($request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $branches = $query->orderBy('is_main_branch', 'desc')->orderBy('name')->get();

        // Unique states and cities for dropdown filters
        $states = Branch::where('hospital_id', $hospital?->id)->whereNotNull('state')->distinct()->pluck('state');
        $cities = Branch::where('hospital_id', $hospital?->id)->whereNotNull('city')->distinct()->pluck('city');

        return view('frontend.locations', compact(
            'hospital',
            'settings',
            'branches',
            'states',
            'cities',
            'search'
        ));
    }

    /**
     * Doctors List Page (with Department & Branch filters and 'View Profile' button)
     */
    public function doctors(Request $request)
    {
        $hospital = $this->getActiveHospital();
        $settings = $this->getPortalSettings($hospital);

        $query = DoctorChart::where('hospital_id', $hospital?->id)
            ->where('is_active', true)
            ->with(['branch', 'department', 'testimonials']);

        // Filter by branch
        $branchId = $request->query('branch_id');
        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', (int) $branchId);
        }

        // Filter by department
        $departmentId = $request->query('department_id');
        if ($departmentId && $departmentId !== 'all') {
            $query->where('department_id', (int) $departmentId);
        }

        // Search query (doctor name, degrees, specialty)
        $search = trim($request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('doctor_name', 'like', "%{$search}%")
                    ->orWhere('specialty', 'like', "%{$search}%")
                    ->orWhere('degrees', 'like', "%{$search}%");
            });
        }

        $doctorCharts = $query->orderBy('doctor_name')->get();

        $branches = Branch::where('hospital_id', $hospital?->id)->where('is_active', true)->get();
        $departments = Department::where('hospital_id', $hospital?->id)->where('is_active', true)->get();

        return view('frontend.doctors', compact(
            'hospital',
            'settings',
            'doctorCharts',
            'branches',
            'departments',
            'branchId',
            'departmentId',
            'search'
        ));
    }

    /**
     * Doctor Detailed Profile Page (Chamber Timings, Fees in INR, Quick Appointment & Testimonials)
     */
    public function doctorProfile(DoctorChart $doctorChart)
    {
        $doctorChart->load(['branch', 'department', 'doctorUser', 'testimonials' => function ($q) {
            $q->where('is_published', true)->orderByDesc('created_at');
        }]);

        $hospital = $doctorChart->hospital ?? $this->getActiveHospital();
        $settings = $this->getPortalSettings($hospital, $doctorChart->branch_id);

        $branches = Branch::where('hospital_id', $hospital?->id)->where('is_active', true)->get();
        $departments = Department::where('hospital_id', $hospital?->id)->where('is_active', true)->get();

        // Related doctors in same specialty
        $relatedDoctors = DoctorChart::where('hospital_id', $hospital?->id)
            ->where('id', '!=', $doctorChart->id)
            ->where('is_active', true)
            ->where('department_id', $doctorChart->department_id)
            ->limit(3)
            ->get();

        return view('frontend.doctor_profile', compact(
            'doctorChart',
            'hospital',
            'settings',
            'branches',
            'departments',
            'relatedDoctors'
        ));
    }

    /**
     * Hospital-Wise Health Checkup Packages Page
     */
    public function packages(Request $request)
    {
        $hospital = $this->getActiveHospital();
        $settings = $this->getPortalSettings($hospital);

        $query = HealthCheckupPackage::with(['hospital', 'branch'])
            ->where('hospital_id', $hospital?->id)
            ->where('is_active', true);

        // Branch filter
        $branchId = $request->query('branch_id');
        if ($branchId && $branchId !== 'all') {
            $query->where(function ($q) use ($branchId) {
                $q->whereNull('branch_id')->orWhere('branch_id', (int) $branchId);
            });
        }

        // Category filter
        $category = $request->query('category');
        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        $packages = $query->orderBy('is_popular', 'desc')->orderBy('discounted_price')->get();

        $categories = HealthCheckupPackage::where('hospital_id', $hospital?->id)
            ->distinct()
            ->pluck('category');

        $branches = Branch::where('hospital_id', $hospital?->id)->where('is_active', true)->get();

        return view('frontend.packages', compact(
            'hospital',
            'settings',
            'packages',
            'categories',
            'branches',
            'branchId',
            'category'
        ));
    }

    /**
     * Book Health Checkup Package
     */
    public function bookPackage(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:health_checkup_packages,id',
            'branch_id' => 'required|exists:branches,id',
            'patient_name' => 'required|string|max:255',
            'patient_phone' => 'required|string|max:20',
            'patient_email' => 'nullable|email|max:255',
            'patient_age' => 'required|integer|min:1|max:120',
            'patient_gender' => 'required|in:male,female,other',
            'preferred_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:500',
        ]);

        $package = HealthCheckupPackage::findOrFail($validated['package_id']);
        $branch = Branch::findOrFail($validated['branch_id']);
        $hospitalId = $package->hospital_id;

        $bookingNumber = 'PKG-BK-'.date('Y').'-'.str_pad((string) rand(1000, 99999), 5, '0', STR_PAD_LEFT);

        $booking = PackageBooking::create([
            'hospital_id' => $hospitalId,
            'branch_id' => $branch->id,
            'package_id' => $package->id,
            'booking_number' => $bookingNumber,
            'patient_name' => $validated['patient_name'],
            'patient_phone' => $validated['patient_phone'],
            'patient_email' => $validated['patient_email'] ?? null,
            'patient_age' => $validated['patient_age'],
            'patient_gender' => $validated['patient_gender'],
            'preferred_date' => $validated['preferred_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'confirmed',
        ]);

        AuditLogger::log(
            moduleKey: 'packages',
            action: 'portal_package_booked',
            entityType: 'PackageBooking',
            entityId: $booking->id,
            details: [
                'booking_number' => $bookingNumber,
                'package' => $package->name,
                'patient' => $validated['patient_name'],
                'phone' => $validated['patient_phone'],
            ],
            hospitalId: $hospitalId
        );

        $confirmation = [
            'booking_number' => $bookingNumber,
            'package_name' => $package->name,
            'category' => $package->category,
            'price' => $package->formatted_discounted_price,
            'patient_name' => $booking->patient_name,
            'patient_phone' => $booking->patient_phone,
            'preferred_date' => Carbon::parse($booking->preferred_date)->format('d M, Y (l)'),
            'branch_name' => $branch->name,
            'branch_city' => $branch->city,
        ];

        return back()
            ->with('success', "Health Checkup Package Booked! Your Reference Number is {$bookingNumber}.")
            ->with('package_confirmation', $confirmation);
    }

    /**
     * Submit Customer Review / Testimonial for Doctor
     */
    public function submitTestimonial(Request $request)
    {
        $validated = $request->validate([
            'doctor_chart_id' => 'required|exists:doctor_charts,id',
            'patient_name' => 'required|string|max:255',
            'patient_location' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'headline' => 'nullable|string|max:255',
            'treatment_received' => 'nullable|string|max:255',
            'comment' => 'required|string|max:1000',
        ]);

        $doctorChart = DoctorChart::findOrFail($validated['doctor_chart_id']);

        $testimonial = DoctorTestimonial::create([
            'hospital_id' => $doctorChart->hospital_id,
            'branch_id' => $doctorChart->branch_id,
            'doctor_chart_id' => $doctorChart->id,
            'patient_name' => $validated['patient_name'],
            'patient_location' => $validated['patient_location'],
            'rating' => $validated['rating'],
            'headline' => $validated['headline'] ?? 'Patient Review',
            'treatment_received' => $validated['treatment_received'] ?? $doctorChart->specialty,
            'comment' => $validated['comment'],
            'is_verified' => true,
            'is_published' => true,
        ]);

        AuditLogger::log(
            moduleKey: 'opd',
            action: 'doctor_testimonial_submitted',
            entityType: 'DoctorTestimonial',
            entityId: $testimonial->id,
            details: [
                'doctor' => $doctorChart->doctor_name,
                'patient' => $testimonial->patient_name,
                'rating' => $testimonial->rating,
            ],
            hospitalId: $doctorChart->hospital_id
        );

        return back()->with('success', 'Thank you! Your testimonial and rating have been published.');
    }

    /**
     * Customer Online OPD Token Booking
     */
    public function bookAppointment(Request $request)
    {
        $hospital = $this->getActiveHospital();

        if (! $hospital) {
            return back()->with('error', 'Hospital not found.');
        }

        // Check if online booking is enabled
        $allowBooking = SystemSetting::get('allow_online_booking', true, $request->branch_id, $hospital->id);
        if (! $allowBooking) {
            return back()->with('error', 'Online appointment booking is currently paused. Please contact our 24x7 helpline for urgent care.');
        }

        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'patient_phone' => 'required|string|max:20',
            'patient_gender' => 'required|in:male,female,other',
            'patient_age' => 'required|integer|min:1|max:120',
            'patient_abha' => 'nullable|string|max:50',
            'branch_id' => 'required|exists:branches,id',
            'doctor_id' => 'required|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|string',
            'symptoms' => 'nullable|string|max:500',
        ]);

        $branch = Branch::findOrFail($validated['branch_id']);
        $doctor = User::findOrFail($validated['doctor_id']);
        $doctorChart = DoctorChart::where('user_id', $doctor->id)->first();

        // Find or create patient record by phone/name
        $patient = Patient::where('hospital_id', $hospital->id)
            ->where('phone', $validated['patient_phone'])
            ->first();

        if (! $patient) {
            $dob = Carbon::now()->subYears((int) $validated['patient_age'])->toDateString();
            $nameParts = explode(' ', trim($validated['patient_name']), 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? 'Patient';
            $mrn = 'MRN-'.strtoupper(substr($hospital->code ?? 'HOS', 0, 3)).'-'.date('Y').'-'.str_pad((string) rand(1000, 99999), 5, '0', STR_PAD_LEFT);

            $patient = Patient::create([
                'hospital_id' => $hospital->id,
                'mrn' => $mrn,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $validated['patient_phone'],
                'gender' => $validated['patient_gender'],
                'date_of_birth' => $dob,
                'abha_id' => $validated['patient_abha'] ?? null,
                'blood_group' => 'B+',
            ]);
        }

        // Auto-increment token number for the doctor on that day
        $tokenNumber = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $validated['appointment_date'])
            ->max('token_number') + 1;

        $tokenPrefix = SystemSetting::get('token_prefix', 'TK-', $branch->id, $hospital->id);

        $appointment = Appointment::create([
            'hospital_id' => $hospital->id,
            'branch_id' => $branch->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $validated['department_id'] ?? ($doctorChart?->department_id),
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'token_number' => $tokenNumber,
            'status' => 'scheduled',
            'symptoms' => $validated['symptoms'] ?? 'Public Online Booking via Patient Portal',
        ]);

        AuditLogger::log(
            moduleKey: 'opd',
            action: 'public_appointment_booked',
            entityType: 'Appointment',
            entityId: $appointment->id,
            details: [
                'token' => $tokenNumber,
                'doctor' => $doctor->name,
                'patient' => $patient->name,
                'phone' => $patient->phone,
                'branch' => $branch->name,
                'date' => $validated['appointment_date'],
            ],
            hospitalId: $hospital->id
        );

        $currency = SystemSetting::get('currency_symbol', '₹', $branch->id, $hospital->id);

        // Store confirmation card payload in session
        $confirmation = [
            'token_number' => $tokenNumber,
            'token_display' => $tokenPrefix.str_pad((string) $tokenNumber, 2, '0', STR_PAD_LEFT),
            'appointment_id' => $appointment->id,
            'patient_name' => $patient->name,
            'patient_phone' => $patient->phone,
            'doctor_name' => $doctor->name,
            'degrees' => $doctorChart?->degrees ?? 'Specialist Physician',
            'specialty' => $doctorChart?->specialty ?? 'General OPD',
            'room_number' => $doctorChart?->room_number ?? 'OPD Consultation Room',
            'branch_name' => $branch->name,
            'branch_city' => $branch->city,
            'appointment_date' => Carbon::parse($validated['appointment_date'])->format('d M, Y (l)'),
            'appointment_time' => Carbon::parse($validated['appointment_time'])->format('h:i A'),
            'consultation_fee' => $doctorChart ? $doctorChart->consultation_fee : 500,
            'currency' => $currency,
        ];

        return back()
            ->with('success', "Appointment Confirmed! Your Token Number is #{$tokenNumber}.")
            ->with('booking_confirmation', $confirmation);
    }

    /**
     * Live Token Tracker Lookup (Direct search & real-time AJAX response)
     */
    public function trackToken(Request $request)
    {
        $query = trim($request->query('query', $request->query('track_query', '')));

        if ($request->wantsJson() || $request->ajax()) {
            $hospital = $this->getActiveHospital();
            if (! $hospital || empty($query)) {
                return response()->json(['success' => true, 'count' => 0, 'data' => []]);
            }

            $trackedAppointments = Appointment::where('hospital_id', $hospital->id)
                ->where(function ($q) use ($query) {
                    if (is_numeric($query)) {
                        $q->where('token_number', (int) $query)
                            ->orWhereHas('patient', function ($pq) use ($query) {
                                $pq->where('phone', 'like', "%{$query}%");
                            });
                    } else {
                        $q->whereHas('patient', function ($pq) use ($query) {
                            $pq->where('phone', 'like', "%{$query}%")
                                ->orWhere('name', 'like', "%{$query}%");
                        });
                    }
                })
                ->with(['doctor', 'patient', 'branch'])
                ->orderBy('appointment_date', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($app) {
                    return [
                        'token_number' => $app->token_number,
                        'token_display' => 'Token #'.$app->token_number,
                        'patient_name' => $app->patient?->name,
                        'doctor_name' => $app->doctor?->name,
                        'branch_name' => $app->branch?->name,
                        'appointment_date' => Carbon::parse($app->appointment_date)->format('d M, Y'),
                        'appointment_time' => Carbon::parse($app->appointment_time)->format('h:i A'),
                        'status' => ucfirst($app->status),
                        'status_badge' => match ($app->status) {
                            'completed' => 'bg-success',
                            'in_consultation', 'in_progress' => 'bg-warning text-dark',
                            'checked_in' => 'bg-primary',
                            'cancelled' => 'bg-danger',
                            default => 'bg-info'
                        },
                    ];
                });

            return response()->json([
                'success' => true,
                'count' => $trackedAppointments->count(),
                'data' => $trackedAppointments,
            ]);
        }

        return redirect()->route('frontend.home', [
            'track_query' => $query,
            '#track-status',
        ]);
    }
}
