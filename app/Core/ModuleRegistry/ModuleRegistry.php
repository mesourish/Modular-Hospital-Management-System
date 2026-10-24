<?php

namespace App\Core\ModuleRegistry;

use App\Core\Tenancy\TenantManager;
use App\Models\Hospital;
use App\Models\HospitalModule;
use Illuminate\Support\Facades\Cache;

class ModuleRegistry
{
    /**
     * Define the platform's available module catalog and manifests.
     */
    public static function getAvailableModules(): array
    {
        return [
            'admissions' => [
                'key' => 'admissions',
                'name' => 'Patient Master & Bed Admissions (ADT)',
                'category' => 'Patient Care',
                'description' => 'Complete patient demographics, MRN generation, ward management, bed allocation, and inpatient admissions/discharge.',
                'icon' => 'bx-user-plus',
                'route' => 'admissions.index',
                'is_core' => true,
                'version' => '1.0.0',
            ],
            'opd' => [
                'key' => 'opd',
                'name' => 'OPD & Clinical Consultation',
                'category' => 'Patient Care',
                'description' => 'Doctor appointment queues, triage vitals recording, clinical encounter notes, diagnoses, and electronic prescriptions.',
                'icon' => 'bx-calendar-heart',
                'route' => 'opd.index',
                'is_core' => true,
                'version' => '1.0.0',
            ],
            'hr_payroll' => [
                'key' => 'hr_payroll',
                'name' => 'Hospital Employee Records & Salaries',
                'category' => 'Human Resources & Finance',
                'description' => 'Employee master records, job designations, medical licenses, salary structures (allowances & deductions), and monthly payrolls.',
                'icon' => 'bx-id-card',
                'route' => 'hr.index',
                'is_core' => false,
                'version' => '1.0.0',
            ],
            'er_workforce' => [
                'key' => 'er_workforce',
                'name' => 'Workforce Roster & Patient Assignments',
                'category' => 'Workforce Management',
                'description' => 'Staff duty shift scheduling, Emergency Room (ER) team assignments, on-call rosters, and attending care-team assignments per bed/patient.',
                'icon' => 'bx-timer',
                'route' => 'workforce.index',
                'is_core' => false,
                'version' => '1.0.0',
            ],
            'blood_bank' => [
                'key' => 'blood_bank',
                'name' => 'Blood Bank & Transfusion Service',
                'category' => 'Diagnostics & Transfusion',
                'description' => 'Voluntary donor registries, blood bag serology screening (HIV, HBV, HCV), component separation (PRBC, Platelets, FFP), cross-match testing, and verified blood issue.',
                'icon' => 'bx-droplet',
                'route' => 'blood_bank.index',
                'is_core' => false,
                'version' => '1.2.0',
            ],
            'radiology' => [
                'key' => 'radiology',
                'name' => 'Radiology & Medical Imaging (RIS)',
                'category' => 'Diagnostics & Imaging',
                'description' => 'Digital X-Ray, CT Scanner, MRI, and Ultrasound order management, technician scheduling, radiologist findings verification, and DICOM study linkage.',
                'icon' => 'bx-scan',
                'route' => 'radiology.index',
                'is_core' => false,
                'version' => '1.1.0',
            ],
            'pharmacy' => [
                'key' => 'pharmacy',
                'name' => 'Pharmacy & Medicine Store',
                'category' => 'Pharmacy & Procurement',
                'description' => 'Medicine catalog with generic formulary, batch/expiry ledger, low-stock reorder thresholds, and direct e-prescription dispensing.',
                'icon' => 'bx-plus-medical',
                'route' => 'pharmacy.index',
                'is_core' => false,
                'version' => '1.0.0',
            ],
            'billing' => [
                'key' => 'billing',
                'name' => 'Hospital Billing & Invoices',
                'category' => 'Finance & Accounting',
                'description' => 'Unified patient invoice generation across OPD consultations, bed days, radiology scans, blood issues, and pharmacy items with multi-mode receipts.',
                'icon' => 'bx-receipt',
                'route' => 'billing.index',
                'is_core' => true,
                'version' => '1.0.0',
            ],
            'vital_records' => [
                'key' => 'vital_records',
                'name' => 'Vital Records (Birth & Death Registry)',
                'category' => 'Patient Care & Statutory',
                'description' => 'Statutory birth registration, APGAR scoring, mother-infant linkage, death certification, ICD-10 cause of death, and printable official certificates.',
                'icon' => 'bx-certification',
                'route' => 'vital_records.index',
                'is_core' => false,
                'version' => '1.0.0',
            ],
            'ambulance' => [
                'key' => 'ambulance',
                'name' => 'Ambulance Fleet & Emergency Dispatch',
                'category' => 'Emergency Services',
                'description' => 'ALS/BLS ambulance fleet management, GPS tracking, driver roster, paramedic triage, and emergency handover.',
                'icon' => 'bx-car',
                'route' => 'ambulances.index',
                'is_core' => false,
                'version' => '1.0.0',
            ],
            'dietary' => [
                'key' => 'dietary',
                'name' => 'Inpatient Dietary & Nutrition Care',
                'category' => 'Clinical Services',
                'description' => 'Clinical diet ordering (Diabetic, Renal, Low Sodium, Post-Op), meal scheduling, calorie targets, and kitchen delivery confirmation.',
                'icon' => 'bx-bowl-hot',
                'route' => 'dietary.index',
                'is_core' => false,
                'version' => '1.0.0',
            ],
            'biomedical' => [
                'key' => 'biomedical',
                'name' => 'Biomedical Equipment & Engineering',
                'category' => 'Operations & Engineering',
                'description' => 'Hospital medical device assets, calibration schedules, warranty tracking, preventive maintenance, and fault breakdown tickets.',
                'icon' => 'bx-wrench',
                'route' => 'biomedical.index',
                'is_core' => false,
                'version' => '1.0.0',
            ],
        ];
    }

    /**
     * Check if a specific module is active for a given hospital.
     */
    public static function isModuleEnabled(string $moduleKey, ?int $hospitalId = null): bool
    {
        if (! $hospitalId) {
            $hospitalId = TenantManager::getHospitalId();
        }

        if (! $hospitalId) {
            return false;
        }

        // Cache module lookup per hospital for high performance
        return Cache::remember("hospital_{$hospitalId}_module_{$moduleKey}", 60, function () use ($hospitalId, $moduleKey) {
            return HospitalModule::where('hospital_id', $hospitalId)
                ->where('module_key', $moduleKey)
                ->where('is_enabled', true)
                ->exists();
        });
    }

    /**
     * Get all active modules for a given hospital.
     */
    public static function getActiveModulesForHospital(int $hospitalId): array
    {
        $enabledKeys = HospitalModule::where('hospital_id', $hospitalId)
            ->where('is_enabled', true)
            ->pluck('module_key')
            ->toArray();

        $all = static::getAvailableModules();
        $active = [];

        foreach ($enabledKeys as $key) {
            if (isset($all[$key])) {
                $active[$key] = $all[$key];
            }
        }

        return $active;
    }

    /**
     * Clear module cache for a hospital.
     */
    public static function clearCache(int $hospitalId): void
    {
        $modules = array_keys(static::getAvailableModules());
        foreach ($modules as $mod) {
            Cache::forget("hospital_{$hospitalId}_module_{$mod}");
        }
    }
}
