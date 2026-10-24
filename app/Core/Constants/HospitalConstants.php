<?php

namespace App\Core\Constants;

class HospitalConstants
{
    /**
     * Standard Hospital Clinical & Operational Departments
     */
    public const DEPARTMENTS = [
        ['name' => 'Cardiology & Coronary Intensive Care', 'code' => 'CARDIO', 'type' => 'clinical', 'description' => 'Comprehensive cardiac care, interventional cath lab, and CCU.'],
        ['name' => 'Emergency Medicine & Trauma Center', 'code' => 'EMERGENCY', 'type' => 'clinical', 'description' => '24x7 Level-1 trauma resuscitation and acute emergency services.'],
        ['name' => 'General Surgery & Laparoscopy', 'code' => 'SURGERY', 'type' => 'clinical', 'description' => 'Minimally invasive laparoscopic, general, and emergency surgery.'],
        ['name' => 'Internal Medicine & Diabetology', 'code' => 'MEDICINE', 'type' => 'clinical', 'description' => 'Comprehensive adult healthcare, chronic illness, and metabolic disorders.'],
        ['name' => 'Pediatrics & Neonatology', 'code' => 'PEDIATRICS', 'type' => 'clinical', 'description' => 'Pediatric care, immunization, NICU, and PICU.'],
        ['name' => 'Obstetrics & Gynecology (OB/GYN)', 'code' => 'OBGYN', 'type' => 'clinical', 'description' => 'Maternity, labor delivery, fetal medicine, and women\'s health.'],
        ['name' => 'Orthopedics & Joint Reconstruction', 'code' => 'ORTHO', 'type' => 'clinical', 'description' => 'Joint replacements, trauma, arthroscopy, and spine surgery.'],
        ['name' => 'Neurology & Neurosurgery', 'code' => 'NEURO', 'type' => 'clinical', 'description' => 'Stroke care, neuro-interventions, brain, and spinal surgery.'],
        ['name' => 'Medical & Surgical Oncology', 'code' => 'ONCO', 'type' => 'clinical', 'description' => 'Comprehensive cancer care, chemotherapy, and surgical oncology.'],
        ['name' => 'Nephrology & Dialysis', 'code' => 'NEPHRO', 'type' => 'clinical', 'description' => 'Renal care, hemodialysis, and peritoneal dialysis.'],
        ['name' => 'Diagnostic Radiology & PACS Imaging', 'code' => 'RADIOLOGY', 'type' => 'diagnostic', 'description' => 'MRI 3.0T, CT 128-Slice, Digital X-Ray, USG, and Mammography.'],
        ['name' => 'Pathology & Clinical Laboratory', 'code' => 'PATHOLOGY', 'type' => 'diagnostic', 'description' => 'Hematology, biochemistry, microbiology, and molecular pathology.'],
        ['name' => 'Blood Transfusion & Apheresis Service', 'code' => 'BLOOD_BANK', 'type' => 'support', 'description' => '24x7 blood component separation, cross-matching, and apheresis.'],
        ['name' => 'Hospital Central Pharmacy & Formulary', 'code' => 'PHARMACY', 'type' => 'pharmacy', 'description' => 'Inpatient dispensing, outpatient POS, formulary, and cold-chain.'],
        ['name' => 'Ambulance Fleet & Emergency Dispatch', 'code' => 'AMBULANCE', 'type' => 'support', 'description' => 'ALS, BLS, and neonatal transport fleet with driver scheduling.'],
        ['name' => 'Anesthesiology & Critical Care (ICU)', 'code' => 'ICU_ANESTH', 'type' => 'clinical', 'description' => 'Multidisciplinary intensive care, ventilator management, and perioperative anesthesia.'],
        ['name' => 'Human Resources & Clinical Credentialing', 'code' => 'HR', 'type' => 'administrative', 'description' => 'Staff credentialing, duty roster, attendance, and payroll.'],
        ['name' => 'Finance, Billing & Insurance TPA', 'code' => 'BILLING', 'type' => 'administrative', 'description' => 'Patient invoicing, GST tax billing, cashless TPA, and accounts.'],
    ];

    /**
     * Standard Sub-Departments / Clinical Units
     */
    public const SUB_DEPARTMENTS = [
        'CARDIO' => [
            ['name' => 'Interventional Cath Lab', 'code' => 'CATH_LAB', 'description' => 'Coronary angioplasty, pacemaker implantation, and structural heart procedures.'],
            ['name' => 'Coronary Care Unit (CCU)', 'code' => 'CCU_BEDS', 'description' => 'Specialized intensive care for acute myocardial infarction and heart failure.'],
            ['name' => 'Non-Invasive Cardiac Lab', 'code' => 'ECHO_TMT', 'description' => '2D Echocardiography, TMT stress testing, and Holter monitoring.'],
        ],
        'EMERGENCY' => [
            ['name' => 'Acute Red Triage Resuscitation Bay', 'code' => 'TRIAGE_RED', 'description' => 'Immediate life-support for cardiac arrest, severe trauma, and stroke.'],
            ['name' => 'Emergency Observation & Short Stay', 'code' => 'ER_OBS', 'description' => '6-24 hour monitoring of stabilized emergency patients.'],
            ['name' => 'Emergency Minor OT & Procedure Room', 'code' => 'ER_MINOR_OT', 'description' => 'Emergency suturing, wound debridement, and fracture reductions.'],
        ],
        'PEDIATRICS' => [
            ['name' => 'Neonatal Intensive Care Unit (NICU)', 'code' => 'NICU_BEDS', 'description' => 'Level-3 care for premature neonates and neonatal jaundice.'],
            ['name' => 'Pediatric Intensive Care Unit (PICU)', 'code' => 'PICU_BEDS', 'description' => 'Intensive monitoring for critically ill infants and children.'],
            ['name' => 'Well-Baby & Immunization Clinic', 'code' => 'PEDI_IMMUNE', 'description' => 'National and IAP schedule vaccinations and developmental milestone tracking.'],
        ],
        'RADIOLOGY' => [
            ['name' => '3.0 Tesla High-Field MRI Suite', 'code' => 'MRI_SUITE', 'description' => 'Neurological, musculoskeletal, and abdominal magnetic resonance studies.'],
            ['name' => '128-Slice Multi-Detector Cardiac CT Suite', 'code' => 'CT_SUITE', 'description' => 'Coronary CT angiography, whole body trauma, and virtual colonoscopy.'],
            ['name' => 'Digital Radiography (X-Ray) Room', 'code' => 'XRAY_SUITE', 'description' => 'High-frequency digital chest, orthopedic, and spine radiography.'],
            ['name' => 'Color Doppler 4D Ultrasound Suite', 'code' => 'USG_SUITE', 'description' => 'Obstetric 4D anomaly scans, vascular doppler, and abdominal sonography.'],
            ['name' => 'Digital Mammography & DEXA Suite', 'code' => 'MAMMO_DEXA', 'description' => 'Early breast cancer screening and bone mineral density DEXA assessment.'],
        ],
        'SURGERY' => [
            ['name' => 'Major Modular Operating Theaters', 'code' => 'MAJOR_OT', 'description' => 'Laminar air flow OT suites for cardiac, ortho, and neurosurgeries.'],
            ['name' => 'Endoscopy & Colonoscopy Suite', 'code' => 'ENDOSCOPY', 'description' => 'Diagnostic and therapeutic upper GI endoscopy, ERCP, and colonoscopy.'],
            ['name' => 'Post-Anesthesia Care Unit (PACU)', 'code' => 'PACU_RECOVERY', 'description' => 'Immediate post-operative hemodynamic monitoring and extubation.'],
        ],
        'NEPHRO' => [
            ['name' => 'Hemodialysis Unit', 'code' => 'DIALYSIS_UNIT', 'description' => '10-station advanced dialysis with isolated stations for seropositive patients.'],
            ['name' => 'Continuous Renal Replacement Therapy (CRRT)', 'code' => 'CRRT_ICU', 'description' => 'Slow continuous dialysis for hemodynamically unstable ICU patients.'],
        ],
        'AMBULANCE' => [
            ['name' => 'Advanced Life Support (ALS) Unit', 'code' => 'ALS_DISPATCH', 'description' => 'Cardiac ambulances equipped with ventilators, defibrillators, and emergency drugs.'],
            ['name' => 'Basic Life Support (BLS) Unit', 'code' => 'BLS_DISPATCH', 'description' => 'Oxygen-equipped rapid transfer vehicles for stable patients.'],
            ['name' => 'Neonatal Mobile Transport Unit', 'code' => 'NICU_TRANSPORT', 'description' => 'Specialized ambulance with transport incubator and infant ventilator.'],
        ],
    ];

    /**
     * Standard Healthcare Staff Designations
     */
    public const DESIGNATIONS = [
        ['title' => 'Chief Medical Director (CMD)', 'department_code' => 'CARDIO', 'min_salary' => 300000, 'max_salary' => 600000, 'description' => 'Executive clinical head overseeing hospital medical governance.'],
        ['title' => 'Senior Consultant Physician', 'department_code' => 'MEDICINE', 'min_salary' => 200000, 'max_salary' => 450000, 'description' => 'Lead specialist physician with 10+ years super-specialty experience.'],
        ['title' => 'Consultant Interventional Cardiologist', 'department_code' => 'CARDIO', 'min_salary' => 250000, 'max_salary' => 500000, 'description' => 'Cath lab primary interventionist and coronary specialist.'],
        ['title' => 'Emergency Physician & Trauma Incharge', 'department_code' => 'EMERGENCY', 'min_salary' => 150000, 'max_salary' => 300000, 'description' => 'Senior ER consultant leading acute resuscitation.'],
        ['title' => 'Consultant Radiologist', 'department_code' => 'RADIOLOGY', 'min_salary' => 180000, 'max_salary' => 380000, 'description' => 'DICOM imaging diagnostic impression specialist.'],
        ['title' => 'Resident Medical Officer (RMO)', 'department_code' => 'MEDICINE', 'min_salary' => 70000, 'max_salary' => 120000, 'description' => '24-hour on-duty ward and emergency clinician.'],
        ['title' => 'Nursing Superintendent', 'department_code' => 'ICU_ANESTH', 'min_salary' => 90000, 'max_salary' => 160000, 'description' => 'Chief of nursing staff, protocols, and ward staffing.'],
        ['title' => 'ICU & Critical Care Staff Nurse', 'department_code' => 'ICU_ANESTH', 'min_salary' => 45000, 'max_salary' => 85000, 'description' => 'BLS/ACLS certified critical care bed nurse.'],
        ['title' => 'Chief Pharmacist & Formulary Manager', 'department_code' => 'PHARMACY', 'min_salary' => 60000, 'max_salary' => 110000, 'description' => 'Supervises hospital pharmacy POS and narcotics register.'],
        ['title' => 'Emergency Ambulance Paramedic', 'department_code' => 'AMBULANCE', 'min_salary' => 35000, 'max_salary' => 65000, 'description' => 'Pre-hospital emergency care and patient stabilization.'],
        ['title' => 'Emergency Ambulance Pilot / Driver', 'department_code' => 'AMBULANCE', 'min_salary' => 25000, 'max_salary' => 45000, 'description' => 'Certified heavy-vehicle ambulance driver trained in blue-light driving.'],
        ['title' => 'Medical Lab Technologist (MLT)', 'department_code' => 'PATHOLOGY', 'min_salary' => 35000, 'max_salary' => 60000, 'description' => 'Clinical sample testing and automated analyzer operator.'],
        ['title' => 'Senior Biomedical Engineer', 'department_code' => 'SUPPORT', 'min_salary' => 50000, 'max_salary' => 95000, 'description' => 'Medical device calibration, preventive maintenance, and NABH compliance.'],
        ['title' => 'Head of Accounts & Financial Billing', 'department_code' => 'BILLING', 'min_salary' => 80000, 'max_salary' => 150000, 'description' => 'Corporate accounts, GST reconciliation, and hospital billing.'],
    ];

    /**
     * Realistic Healthcare Constants by Category
     */
    public const SYSTEM_CONSTANTS = [
        'triage_level' => [
            ['key' => 'level_1_resuscitation', 'name' => 'Level 1 - Resuscitation (Immediate)', 'code' => 'L1_RED', 'badge_color' => 'danger', 'sort_order' => 1, 'description' => 'Immediate life-threat (cardiac arrest, severe respiratory distress).'],
            ['key' => 'level_2_emergent', 'name' => 'Level 2 - Emergent (< 15 mins)', 'code' => 'L2_ORANGE', 'badge_color' => 'warning', 'sort_order' => 2, 'description' => 'Severe chest pain, altered mental status, major fractures.'],
            ['key' => 'level_3_urgent', 'name' => 'Level 3 - Urgent (< 30 mins)', 'code' => 'L3_YELLOW', 'badge_color' => 'warning', 'sort_order' => 3, 'description' => 'High fever, moderate asthma, persistent vomiting.'],
            ['key' => 'level_4_semi_urgent', 'name' => 'Level 4 - Semi-Urgent (< 60 mins)', 'code' => 'L4_GREEN', 'badge_color' => 'success', 'sort_order' => 4, 'description' => 'Minor trauma, sprain, uncomplicated lacerations.'],
            ['key' => 'level_5_non_urgent', 'name' => 'Level 5 - Non-Urgent (< 120 mins)', 'code' => 'L5_BLUE', 'badge_color' => 'info', 'sort_order' => 5, 'description' => 'Suture removal, prescription renewal, minor rash.'],
        ],

        'ambulance_service_type' => [
            ['key' => 'als', 'name' => 'Advanced Life Support (ALS)', 'code' => 'AMB_ALS', 'badge_color' => 'danger', 'sort_order' => 1, 'description' => 'Cardiac monitor, ventilator, defibrillator, emergency medication kit.'],
            ['key' => 'bls', 'name' => 'Basic Life Support (BLS)', 'code' => 'AMB_BLS', 'badge_color' => 'primary', 'sort_order' => 2, 'description' => 'Oxygen delivery, suction apparatus, automated external defibrillator (AED).'],
            ['key' => 'neonatal_mobile_icu', 'name' => 'Neonatal Mobile ICU (NICU Ambulance)', 'code' => 'AMB_NICU', 'badge_color' => 'purple', 'sort_order' => 3, 'description' => 'Transport incubator, infant T-piece resuscitator, micro-infusion pumps.'],
            ['key' => 'patient_transport_vehicle', 'name' => 'Patient Transport Vehicle (PTV)', 'code' => 'AMB_PTV', 'badge_color' => 'secondary', 'sort_order' => 4, 'description' => 'Non-emergency transfers, dialysis runs, wheelchair accessibility ramp.'],
        ],

        'shift_type' => [
            ['key' => 'morning_shift', 'name' => 'Morning Shift (07:00 AM - 03:00 PM)', 'code' => 'SHIFT_M', 'badge_color' => 'info', 'sort_order' => 1, 'description' => 'Primary ward rounds, elective surgeries, and OPD checkups.'],
            ['key' => 'evening_shift', 'name' => 'Evening Shift (03:00 PM - 11:00 PM)', 'code' => 'SHIFT_E', 'badge_color' => 'primary', 'sort_order' => 2, 'description' => 'Post-op reviews, evening medicine dispensation, and visitor management.'],
            ['key' => 'night_duty', 'name' => 'Night Duty (11:00 PM - 07:00 AM)', 'code' => 'SHIFT_N', 'badge_color' => 'dark', 'sort_order' => 3, 'description' => 'Overnight emergency coverage, ICU monitoring, and vital signs charting.'],
            ['key' => 'emergency_24h_oncall', 'name' => '24-Hour Emergency On-Call', 'code' => 'SHIFT_24H', 'badge_color' => 'danger', 'sort_order' => 4, 'description' => 'Standby emergency surgeon, interventional cardiologist, and anesthetist.'],
        ],

        'payment_mode' => [
            ['key' => 'cash', 'name' => 'Cash at Counter', 'code' => 'CASH', 'badge_color' => 'success', 'sort_order' => 1, 'description' => 'Direct cash payment with printed GST receipt.'],
            ['key' => 'upi_qr', 'name' => 'UPI / Bharat QR Code (Instant)', 'code' => 'UPI', 'badge_color' => 'primary', 'sort_order' => 2, 'description' => 'Google Pay, PhonePe, Paytm, and BHIM QR scan.'],
            ['key' => 'card_pos', 'name' => 'Credit / Debit Card (POS Machine)', 'code' => 'CARD', 'badge_color' => 'info', 'sort_order' => 3, 'description' => 'Visa, MasterCard, RuPay, and American Express swipe.'],
            ['key' => 'neft_rtgs', 'name' => 'Bank Transfer (NEFT / RTGS / IMPS)', 'code' => 'BANK', 'badge_color' => 'secondary', 'sort_order' => 4, 'description' => 'Direct corporate or patient bank transfer.'],
            ['key' => 'insurance_tpa', 'name' => 'Corporate Insurance / TPA Cashless', 'code' => 'TPA', 'badge_color' => 'warning', 'sort_order' => 5, 'description' => 'Star Health, HDFC ERGO, ICICI Lombard, Vidal, Medi Assist cashless pre-auth.'],
            ['key' => 'ayushman_bharat', 'name' => 'Ayushman Bharat (AB PM-JAY / CGHS)', 'code' => 'PMJAY', 'badge_color' => 'success', 'sort_order' => 6, 'description' => 'Government healthcare scheme cashless coverage.'],
        ],

        'ward_type' => [
            ['key' => 'presidential_suite', 'name' => 'Presidential Deluxe Suite', 'code' => 'WARD_SUITE', 'badge_color' => 'primary', 'sort_order' => 1, 'description' => 'Private luxury suite with attendant room, pantry, and dedicated nurse.'],
            ['key' => 'single_private_room', 'name' => 'Single Private Room (Deluxe)', 'code' => 'WARD_SINGLE', 'badge_color' => 'info', 'sort_order' => 2, 'description' => 'Air-conditioned single room with attached washroom and attendant couch.'],
            ['key' => 'twin_sharing', 'name' => 'Twin Sharing Semi-Private', 'code' => 'WARD_TWIN', 'badge_color' => 'secondary', 'sort_order' => 3, 'description' => '2-patient shared room with privacy curtains and monitoring.'],
            ['key' => 'general_medical_ward', 'name' => 'General Medical / Surgical Ward', 'code' => 'WARD_GEN', 'badge_color' => 'light', 'sort_order' => 4, 'description' => 'Subsidized multi-bed general care ward with centralized nursing station.'],
            ['key' => 'icu_isolation_unit', 'name' => 'ICU Negative-Pressure Isolation Bed', 'code' => 'WARD_ICU_ISO', 'badge_color' => 'danger', 'sort_order' => 5, 'description' => 'Infection control isolation room with HEPA filtration and dedicated ventilator.'],
        ],
    ];

    /**
     * Get all categories
     */
    public static function getCategories(): array
    {
        return array_keys(self::SYSTEM_CONSTANTS);
    }
}
