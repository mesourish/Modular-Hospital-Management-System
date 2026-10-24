## Description
Provide a concise summary of the changes introduced by this pull request.

## Type of Change
- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds clinical/administrative functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Security / Multi-Tenant Isolation hardening
- [ ] Documentation update

## Affected Modules
- [ ] Master Constants & Taxonomies
- [ ] Ambulance Fleet & Scheduler
- [ ] Settings / Factory Reset
- [ ] Doctor Schedule / Rx
- [ ] Operation Theater (OT)
- [ ] ICU & Critical Care
- [ ] Radiology / RIS
- [ ] Pharmacy POS & Stock
- [ ] Blood Bank
- [ ] Inpatient & Wards
- [ ] Billing & Invoicing
- [ ] Multi-Language Patient Portal

## Testing & Quality Verification
- [ ] All automated tests pass (`php artisan test`)
- [ ] Security penetration tests pass (`php artisan test --filter=SecurityPenetrationAndMasterConstantsTest`)
- [ ] Code formatted with Laravel Pint (`./vendor/bin/pint --test`)
- [ ] Multi-tenant isolation verified (`TenantScope` adhered to)
- [ ] No sensitive credentials, API keys, or `.env` entries included

## Checklist
- [ ] My code adheres to the project's code style guidelines.
- [ ] I have added tests that prove my fix is effective or that my feature works.
- [ ] I have updated relevant documentation in `README.md` or module docs.
