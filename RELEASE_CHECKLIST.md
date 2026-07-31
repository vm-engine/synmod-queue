# Release Checklist for vm-engine/synmod-queue

Use this checklist before releasing a new version of the package.

## ✅ Pre-Release Tasks (COMPLETED)

### Files Created
- [x] `.gitignore` - Excludes vendor, composer.lock, and development files
- [x] `LICENSE` - MIT License
- [x] `CHANGELOG.md` - Version history and changes
- [x] Enhanced `composer.json` with:
  - Keywords for discoverability
  - License information
  - PHP and Laravel version constraints
  - Service provider auto-discovery

### Code Quality
- [ ] All tests passing
- [x] Module.json properly configured
- [x] Namespaces correctly set (VmEngine\Queue\)
- [ ] Service provider registered (Task 2)

## ⚠️ Actions Required Before Release

### 1. Clean Up Development Files
```bash
# Remove vendor directory (should NOT be in git)
rm -rf vendor/

# Remove composer.lock (not needed for libraries)
rm composer.lock

# Verify .gitignore is working
git status
```

### 2. Update Version Numbers
- [x] Update version in `module.json` (currently: 0.1.0)
- [x] Update version in `CHANGELOG.md`
- [x] Decided on semantic version: 0.1.0 (Initial release)

### 3. Review Documentation
- [ ] README.md is accurate and complete
- [ ] All installation instructions are correct
- [ ] Test commands work as documented

### 4. Git Repository Preparation
```bash
# Add all new files
git add .gitignore LICENSE CHANGELOG.md

# Check what will be committed
git status

# Commit the changes
git add -A
git commit -m "Prepare for v0.1.0 release"

# Create version tag
git tag -a v0.1.0 -m "Release version 0.1.0"
```

### 5. Final Testing
- [ ] Run all tests: `./vendor/bin/pest packages/synmod-queue/tests`
- [ ] Check code style: `./vendor/bin/pint packages/synmod-queue`
- [ ] Test installation in a fresh Laravel project
- [ ] Check all routes are accessible

### 6. Composer Package Registration
- [ ] Update composer.json repositories section if publishing to Packagist
- [ ] Remove or update `repositories` section for public release
- [ ] Ensure dependencies are publicly available

### 7. Documentation Review
- [ ] CLAUDE.md is up to date (for AI assistants)
- [ ] Examples are working
- [ ] Links in README are valid

## 📦 Release Process

### For Internal/Private Repository
```bash
# Push to repository
git push origin master
git push origin v0.1.0

# Update dependent projects
composer update vm-engine/synmod-queue
```

### For Public Release (Packagist)
1. **Clean composer.json**:
   - Remove local `repositories` section
   - Ensure all dependencies are on Packagist
   - Update stability to `"minimum-stability": "stable"`

2. **Submit to Packagist**:
   - Create account at https://packagist.org
   - Submit package URL
   - Configure GitHub webhook for auto-updates

3. **Tag the release**:
   ```bash
   git tag -a v0.1.0 -m "Initial release"
   git push origin v0.1.0
   ```

## 🔍 Post-Release Verification

- [ ] Package installs correctly: `composer require vm-engine/synmod-queue`
- [ ] Service provider auto-discovered
- [ ] Tests pass in consumer project
- [ ] Routes are registered correctly
- [ ] ACL permissions work

## ⚡ Quick Commands

```bash
# Clean vendor
rm -rf vendor/ composer.lock

# Run tests
cd ../../ && ./vendor/bin/pest packages/synmod-queue/tests

# Fix code style
cd ../../ && ./vendor/bin/pint packages/synmod-queue

# Create release
git add -A
git commit -m "Release v0.1.0"
git tag -a v0.1.0 -m "Release version 0.1.0"
git push origin master --tags
```

## 🚨 Critical Checks

- [ ] **NO** vendor directory in git
- [ ] **NO** .env files
- [ ] **NO** hardcoded credentials
- [ ] **NO** development-only code
- [ ] License file present
- [ ] All tests passing
- [ ] Documentation complete

---

**Current Status**: 🚧 Scaffolding complete, implementation in progress

**Next Version**: 0.1.0 → 0.2.0 (for next feature) or 0.1.1 (for bugfix)
