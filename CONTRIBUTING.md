# Contributing to OpenHMS 🏥

Thank you for your interest in contributing to **OpenHMS**! We are committed to building an open, accessible, and resilient hospital management software ecosystem for clinics, hospitals, and healthcare networks worldwide.

---

## 🌳 Branching Strategy & Workflow

To maintain production stability and protect patient/clinical critical code from accidental breakage, OpenHMS follows a strict dual-branch model:

```
  main (Protected Production Releases)
    ▲
    │ (Pull Requests with reviews & passed CI only)
    │
  develop (Active Staging & Integration)
    ▲
    ├──────────────┬──────────────┐
    │              │              │
feature/ambulance-gps   fix/triage-severity   feature/pacu-monitoring
```

### 1. `main` Branch (Protected Production)
- **Direct pushes to `main` are strictly forbidden.**
- Contains only tested, audited, production-ready release tags.
- All code reaching `main` **must** originate from a Pull Request merged from `develop` or a critical `hotfix/*` branch.

### 2. `develop` Branch (Active Integration)
- The default development and collaboration branch.
- Feature branches branch off from `develop` and merge back into `develop` via approved Pull Requests.

### 3. Feature & Bugfix Branches
- `feature/<short-feature-name>` for new extensions, modules, or UI improvements.
- `bugfix/<short-bug-description>` for fixing existing issues.
- `hotfix/<critical-patch>` for urgent production patches.

---

## 🛡️ Setting Up Branch Protection on GitHub (Repository Maintainer Guide)

To protect the `main` branch from direct pushes or unreviewed changes, maintainers should configure GitHub Branch Protection Rules as follows:

1. On GitHub, navigate to **Settings** &rarr; **Branches** (in the repository sidebar).
2. Click **Add branch protection rule** (or **Add rule**).
3. In **Branch name pattern**, enter: `main`.
4. Enable the following protections:
   - ✅ **Require a pull request before merging**:
     - Check **Require approvals** (set minimum to at least `1`).
     - Check **Dismiss stale pull request approvals when new commits are pushed**.
     - Check **Require review from Code Owners** (optional).
   - ✅ **Require status checks to pass before merging**:
     - Check **Require branches to be up to date before merging**.
     - Search and select the CI status check: `test-and-lint` (from GitHub Actions).
   - ✅ **Require conversation resolution before merging**:
     - Ensures all code review comments and feedback are resolved.
   - ✅ **Require linear history** (keeps clean Git commit history).
   - ✅ **Do not allow bypassing the above settings**:
     - Applies rules to administrators as well, ensuring no one accidentally force-pushes to `main`.
5. Click **Create** or **Save changes**.
6. Set the repository's **Default branch** to `develop` under **Settings** &rarr; **General** so all contributors automatically target `develop`.

---

## 🛠️ Local Development & Testing Standards

### 1. Fork & Clone
```bash
git clone https://github.com/<your-username>/Modular-Hospital-Management-System.git
cd Modular-Hospital-Management-System
git checkout develop
git checkout -b feature/my-new-feature
```

### 2. Environment Setup
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

### 3. Coding Style (Laravel Pint)
All code must adhere to PSR-12 and Laravel styling conventions. Run the linter before committing:
```bash
./vendor/bin/pint
```
Verify zero lint errors:
```bash
./vendor/bin/pint --test
```

### 4. Running Tests (Pest PHP)
All new features and bug fixes **must** include tests. The entire test suite must pass with a 100% success rate:
```bash
# Run complete test suite
php artisan test

# Run security penetration tests
php artisan test --filter=SecurityPenetrationAndMasterConstantsTest
```

---

## 📬 Submitting a Pull Request (PR)

1. Commit your changes with clear, semantic commit messages (`feat: ...`, `fix: ...`, `docs: ...`, `refactor: ...`).
2. Push your branch to your GitHub fork:
   ```bash
   git push origin feature/my-new-feature
   ```
3. Open a Pull Request targeting the **`develop`** branch of `mesourish/Modular-Hospital-Management-System`.
4. Fill out the Pull Request template completely, describing:
   - The purpose of the changes.
   - Affected modules or database tables.
   - Test results and verification steps.
5. Wait for automated CI checks to pass and request review from maintainers.

---

## 💡 Code of Conduct

Please note that this project is released with a [Contributor Code of Conduct](CODE_OF_CONDUCT.md). By participating in this project you agree to abide by its terms.
