<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Audit\AuditLogger;
use App\Core\Tenancy\TenantManager;
use App\Http\Controllers\Controller;
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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
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
     * GET /api/v1/hospitals
     * Location-wise hospital and branch search
     */
    public function hospitals(Request $request): JsonResponse
    {
        $query = Branch::with(['hospital', 'departments'])
            ->where('is_active', true);

        // Filter by City
        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->query('city').'%');
        }

        // Filter by State
        if ($request->filled('state')) {
            $query->where('state', 'like', '%'.$request->query('state').'%');
        }

        // Filter by Keyword (name, city, address)
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $branches = $query->orderBy('is_main_branch', 'desc')
            ->orderBy('name')
            ->get();

        $data = $branches->map(function ($b) {
            return [
                'id' => $b->id,
                'hospital_id' => $b->hospital_id,
                'hospital_name' => $b->hospital?->name,
                'branch_name' => $b->name,
                'branch_code' => $b->code,
                'is_main_branch' => (bool) $b->is_main_branch,
                'address' => $b->address,
                'city' => $b->city,
                'state' => $b->state,
                'pincode' => $b->pincode,
                'phone' => $b->phone,
                'email' => $b->email,
                'emergency_phone' => SystemSetting::get('emergency_hotline', '1066', $b->id, $b->hospital_id),
                'ambulance_phone' => SystemSetting::get('ambulance_phone', '108', $b->id, $b->hospital_id),
                'opd_timings' => SystemSetting::get('opd_hours', 'Mon - Sat: 08:00 AM - 08:00 PM', $b->id, $b->hospital_id),
                'departments_count' => $b->departments->count(),
                'active_doctors_count' => DoctorChart::where('branch_id', $b->id)->where('is_active', true)->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Hospitals and branches retrieved successfully.',
            'count' => $data->count(),
            'data' => $data,
        ]);
    }

    /**
     * GET /api/v1/doctors
     * Doctors list with department, specialty, and branch filters
     */
    public function doctors(Request $request): JsonResponse
    {
        $hospital = $this->getActiveHospital();
        $query = DoctorChart::with(['branch', 'department', 'testimonials'])
            ->where('is_active', true);

        if ($hospital) {
            $query->where('hospital_id', $hospital->id);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->query('department_id'));
        }

        // Filter by search keyword
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('doctor_name', 'like', "%{$search}%")
                    ->orWhere('specialty', 'like', "%{$search}%")
                    ->orWhere('degrees', 'like', "%{$search}%");
            });
        }

        $doctors = $query->orderBy('doctor_name')->get();

        $data = $doctors->map(function ($d) {
            return [
                'id' => $d->id,
                'user_id' => $d->user_id,
                'doctor_name' => $d->doctor_name,
                'degrees' => $d->degrees,
                'specialty' => $d->specialty,
                'department_id' => $d->department_id,
                'department_name' => $d->department?->name ?? 'General Medicine',
                'branch_id' => $d->branch_id,
                'branch_name' => $d->branch?->name,
                'branch_city' => $d->branch?->city,
                'opd_days' => $d->opd_days,
                'start_time' => $d->start_time,
                'end_time' => $d->end_time,
                'timing_label' => $d->formatted_timing,
                'room_number' => $d->room_number,
                'consultation_fee' => (float) $d->consultation_fee,
                'formatted_fee' => $d->formatted_fee,
                'status' => $d->status,
                'average_rating' => $d->average_rating,
                'testimonials_count' => $d->testimonials_count,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Doctors list retrieved successfully.',
            'count' => $data->count(),
            'data' => $data,
        ]);
    }

    /**
     * GET /api/v1/doctors/{id}
     * Doctor profile with chamber schedule, consultation fee, and customer testimonials
     */
    public function doctorProfile(int $id): JsonResponse
    {
        $doctor = DoctorChart::with(['branch', 'department', 'testimonials' => function ($q) {
            $q->where('is_published', true)->orderByDesc('created_at');
        }])->find($id);

        if (! $doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found.',
            ], 404);
        }

        $testimonials = $doctor->testimonials->map(function ($t) {
            return [
                'id' => $t->id,
                'patient_name' => $t->patient_name,
                'patient_location' => $t->patient_location,
                'rating' => $t->rating,
                'headline' => $t->headline,
                'comment' => $t->comment,
                'treatment_received' => $t->treatment_received,
                'is_verified' => (bool) $t->is_verified,
                'created_at' => $t->created_at->format('d M, Y'),
            ];
        });

        $profile = [
            'id' => $doctor->id,
            'user_id' => $doctor->user_id,
            'doctor_name' => $doctor->doctor_name,
            'degrees' => $doctor->degrees,
            'specialty' => $doctor->specialty,
            'department_name' => $doctor->department?->name ?? 'Specialist OPD',
            'branch' => [
                'id' => $doctor->branch?->id,
                'name' => $doctor->branch?->name,
                'city' => $doctor->branch?->city,
                'address' => $doctor->branch?->address,
                'phone' => $doctor->branch?->phone,
            ],
            'chamber_schedule' => [
                'opd_days' => $doctor->opd_days,
                'start_time' => $doctor->start_time,
                'end_time' => $doctor->end_time,
                'timing_label' => $doctor->formatted_timing,
                'room_number' => $doctor->room_number,
                'instructions' => $doctor->instructions,
            ],
            'fees' => [
                'consultation_fee' => (float) $doctor->consultation_fee,
                'formatted_consultation_fee' => $doctor->formatted_fee,
                'follow_up_fee' => (float) $doctor->follow_up_fee,
                'formatted_follow_up_fee' => $doctor->formatted_follow_up_fee,
                'emergency_fee' => (float) $doctor->emergency_fee,
                'formatted_emergency_fee' => $doctor->formatted_emergency_fee,
            ],
            'status' => $doctor->status,
            'average_rating' => $doctor->average_rating,
            'testimonials_count' => $testimonials->count(),
            'testimonials' => $testimonials,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Doctor profile retrieved successfully.',
            'data' => $profile,
        ]);
    }

    /**
     * GET /api/v1/packages
     * Hospital-wise health checkup packages
     */
    public function packages(Request $request): JsonResponse
    {
        $hospital = $this->getActiveHospital();
        $query = HealthCheckupPackage::with(['hospital', 'branch'])
            ->where('is_active', true);

        if ($hospital) {
            $query->where('hospital_id', $hospital->id);
        }

        // Branch filter
        if ($request->filled('branch_id')) {
            $branchId = (int) $request->query('branch_id');
            $query->where(function ($q) use ($branchId) {
                $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        $packages = $query->orderBy('is_popular', 'desc')
            ->orderBy('discounted_price')
            ->get();

        $data = $packages->map(function ($pkg) {
            return [
                'id' => $pkg->id,
                'code' => $pkg->code,
                'name' => $pkg->name,
                'slug' => $pkg->slug,
                'category' => $pkg->category,
                'hospital_name' => $pkg->hospital?->name,
                'branch_name' => $pkg->branch?->name ?? 'All Network Branches',
                'branch_id' => $pkg->branch_id,
                'original_price' => (float) $pkg->original_price,
                'formatted_original_price' => $pkg->formatted_original_price,
                'discounted_price' => (float) $pkg->discounted_price,
                'formatted_discounted_price' => $pkg->formatted_discounted_price,
                'discount_percentage' => $pkg->discount_percentage,
                'tests_count' => $pkg->tests_count,
                'turnaround_time' => $pkg->turnaround_time,
                'fasting_required' => (bool) $pkg->fasting_required,
                'fasting_hours' => $pkg->fasting_hours,
                'gender_target' => $pkg->gender_target,
                'age_group' => $pkg->age_group,
                'tests_included' => $pkg->tests_included,
                'description' => $pkg->description,
                'is_popular' => (bool) $pkg->is_popular,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Health checkup packages retrieved successfully.',
            'count' => $data->count(),
            'data' => $data,
        ]);
    }

    /**
     * POST /api/v1/appointments
     * Quick appointment creation via REST API
     */
    public function createAppointment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $branch = Branch::findOrFail($validated['branch_id']);
        $hospitalId = $branch->hospital_id;
        $doctor = User::findOrFail($validated['doctor_id']);
        $doctorChart = DoctorChart::where('user_id', $doctor->id)->first();

        // Find or create patient
        $patient = Patient::where('hospital_id', $hospitalId)
            ->where('phone', $validated['patient_phone'])
            ->first();

        if (! $patient) {
            $dob = Carbon::now()->subYears((int) $validated['patient_age'])->toDateString();
            $nameParts = explode(' ', trim($validated['patient_name']), 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? 'Patient';
            $mrn = 'MRN-'.strtoupper(substr($branch->code ?? 'HOS', 0, 3)).'-'.date('Y').'-'.str_pad((string) rand(1000, 99999), 5, '0', STR_PAD_LEFT);

            $patient = Patient::create([
                'hospital_id' => $hospitalId,
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

        // Generate token number
        $tokenNumber = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $validated['appointment_date'])
            ->max('token_number') + 1;

        $tokenPrefix = SystemSetting::get('token_prefix', 'TK-', $branch->id, $hospitalId);

        $appointment = Appointment::create([
            'hospital_id' => $hospitalId,
            'branch_id' => $branch->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $validated['department_id'] ?? ($doctorChart?->department_id),
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'token_number' => $tokenNumber,
            'status' => 'scheduled',
            'symptoms' => $validated['symptoms'] ?? 'API Quick Appointment Booking',
        ]);

        AuditLogger::log(
            moduleKey: 'opd',
            action: 'api_appointment_booked',
            entityType: 'Appointment',
            entityId: $appointment->id,
            details: [
                'token' => $tokenNumber,
                'doctor' => $doctor->name,
                'patient' => $patient->name,
                'phone' => $patient->phone,
                'branch' => $branch->name,
            ],
            hospitalId: $hospitalId
        );

        return response()->json([
            'success' => true,
            'message' => "Appointment confirmed successfully! Your Token Number is #{$tokenNumber}.",
            'data' => [
                'appointment_id' => $appointment->id,
                'token_number' => $tokenNumber,
                'token_display' => $tokenPrefix.str_pad((string) $tokenNumber, 2, '0', STR_PAD_LEFT),
                'patient_name' => $patient->name,
                'patient_phone' => $patient->phone,
                'mrn' => $patient->mrn,
                'doctor_name' => $doctor->name,
                'degrees' => $doctorChart?->degrees ?? 'Specialist Physician',
                'specialty' => $doctorChart?->specialty ?? 'General OPD',
                'room_number' => $doctorChart?->room_number ?? 'Consultation Suite',
                'branch_name' => $branch->name,
                'branch_city' => $branch->city,
                'appointment_date' => Carbon::parse($validated['appointment_date'])->format('d M, Y (l)'),
                'appointment_time' => Carbon::parse($validated['appointment_time'])->format('h:i A'),
                'consultation_fee' => (float) ($doctorChart?->consultation_fee ?? 500.00),
                'currency' => '₹',
                'status' => $appointment->status,
            ],
        ], 201);
    }

    /**
     * POST /api/v1/packages/book
     * Book Health Checkup Package via REST API
     */
    public function bookPackage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
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
            action: 'api_package_booked',
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

        return response()->json([
            'success' => true,
            'message' => "Health package booked successfully! Your Booking Reference is {$bookingNumber}.",
            'data' => [
                'booking_id' => $booking->id,
                'booking_number' => $bookingNumber,
                'package_name' => $package->name,
                'category' => $package->category,
                'price' => (float) $package->discounted_price,
                'formatted_price' => $package->formatted_discounted_price,
                'tests_count' => $package->tests_count,
                'fasting_required' => (bool) $package->fasting_required,
                'fasting_hours' => $package->fasting_hours,
                'turnaround_time' => $package->turnaround_time,
                'patient_name' => $booking->patient_name,
                'patient_phone' => $booking->patient_phone,
                'preferred_date' => Carbon::parse($booking->preferred_date)->format('d M, Y (l)'),
                'branch_name' => $branch->name,
                'branch_city' => $branch->city,
                'status' => $booking->status,
            ],
        ], 201);
    }

    /**
     * GET /api/v1/testimonials
     * Patient reviews & customer testimonials
     */
    public function testimonials(Request $request): JsonResponse
    {
        $hospital = $this->getActiveHospital();
        $query = DoctorTestimonial::with(['doctorChart', 'branch'])
            ->where('is_published', true);

        if ($hospital) {
            $query->where('hospital_id', $hospital->id);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_chart_id', $request->query('doctor_id'));
        }

        $testimonials = $query->orderByDesc('created_at')->limit(30)->get();

        $data = $testimonials->map(function ($t) {
            return [
                'id' => $t->id,
                'patient_name' => $t->patient_name,
                'patient_location' => $t->patient_location,
                'rating' => $t->rating,
                'headline' => $t->headline,
                'comment' => $t->comment,
                'treatment_received' => $t->treatment_received,
                'doctor_name' => $t->doctorChart?->doctor_name,
                'doctor_specialty' => $t->doctorChart?->specialty,
                'branch_name' => $t->branch?->name,
                'is_verified' => (bool) $t->is_verified,
                'created_at' => $t->created_at->format('d M, Y'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Customer testimonials retrieved successfully.',
            'count' => $data->count(),
            'data' => $data,
        ]);
    }
}
