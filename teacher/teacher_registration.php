<?php
session_start();
require 'config.php';

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['step1'])) {
        // Step 1: Profile Information
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validation
        if (!$first_name || !$last_name || !$email || !$password || !$confirm_password) {
            $error = "All fields are required.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } else {
            // Check if email is unique
            $stmt = $conn->prepare("SELECT * FROM teachers WHERE email = :email");
            $stmt->bindValue(':email', $email, SQLITE3_TEXT);
            $existingTeacher = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

            if ($existingTeacher) {
                $error = "Email is already registered.";
            } else {
                // Store in session and move to step 2
                $_SESSION['registration_data'] = [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => $password
                ];
                header("Location: teacher_registration.php?step=2");
                exit;
            }
        }
    } elseif (isset($_POST['step2'])) {
        // Step 2: School Information
        $school_id = trim($_POST['school_id']);
        
        if (!$school_id) {
            $error = "School code is required.";
        } else {
            // Check if school_id exists
            $stmt = $conn->prepare("SELECT * FROM schools WHERE school_id = :school_id");
            $stmt->bindValue(':school_id', $school_id, SQLITE3_TEXT);
            $school = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

            if (!$school) {
                $error = "Invalid school code.";
            } else {
                $_SESSION['registration_data']['school_id'] = $school_id;
                $_SESSION['registration_data']['school_name'] = $school['school_name'];
                header("Location: teacher_registration.php?step=3");
                exit;
            }
        }
    } elseif (isset($_POST['step3'])) {
        // Step 3: Complete Registration
        if (!isset($_SESSION['registration_data'])) {
            header("Location: teacher_registration.php?step=1");
            exit;
        }

        $data = $_SESSION['registration_data'];
        $full_name = $data['first_name'] . ' ' . $data['last_name'];
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

        $insert = $conn->prepare("INSERT INTO teachers (school_id, email, password, full_name, phone) VALUES (:school_id, :email, :password, :full_name, :phone)");
        $insert->bindValue(':school_id', $data['school_id'], SQLITE3_TEXT);
        $insert->bindValue(':email', $data['email'], SQLITE3_TEXT);
        $insert->bindValue(':password', $hashed_password, SQLITE3_TEXT);
        $insert->bindValue(':full_name', $full_name, SQLITE3_TEXT);
        $insert->bindValue(':phone', $data['phone'], SQLITE3_TEXT);

        if ($insert->execute()) {
            unset($_SESSION['registration_data']);
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Failed to register. Try again later.";
        }
    }
}

// Get available schools for dropdown
$schools_result = $conn->query("SELECT * FROM schools ORDER BY school_name");
$schools = [];
while ($row = $schools_result->fetchArray(SQLITE3_ASSOC)) {
    $schools[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Account - SchoolHub</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>
</head>
<body>
    <div class="registration-container w-1/2 mx-auto">
        <!-- Header -->
        <header class="header flex flex-col justify-center my-6 gap-1.5 items-center">
            <div class="logo text-4xl text-teal-600">
                <div class="logo-icon">
                    <i class=" ri-graduation-cap-fill"></i>
                </div>
            </div>
            <h1 class="page-title text-2xl font-bold text-center">Create New Account</h1>
        </header>

        <!-- Progress Steps -->
        <div class="progress-container">
            <div class="w-full md:gap-16 gap-4 px-8 grid grid-cols-3 progress-steps">
                <div class="step rounded-full aspect-square col-span-1 inline-block bg-teal-50 flex-col flex justify-center items-center <?php echo $step >= 1 ? 'active bg-teal-600 text-white' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                    <div class="text-4xl font-extrabold step-number">1</div>
                    <div class="text-xs text-center uppercase opacity-60 step-label">Your Profile</div>
                </div>
                <div class="step rounded-full aspect-square col-span-1 inline-block bg-teal-50 flex-col flex justify-center items-center<?php echo $step >= 2 ? 'active text-center bg-teal-600 text-white' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                    <div class="step-number text-4xl font-extrabold step-number">2</div>
                    <div class="text-xs text-center uppercase opacity-60 step-label">School <br>Details</div>
                </div>
                <div class="step rounded-full aspect-square col-span-1 inline-block bg-teal-50 flex-col flex justify-center items-center<?php echo $step >= 3 ? 'active  bg-teal-600 text-white' : ''; ?>">
                    <div class="step-number text-4xl font-extrabold text-center step-number">3</div>
                    <div class="text-xs text-center uppercase opacity-60 step-label">Complete Setup</div>
                </div>
            </div>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <?php if ($step == 1): ?>
                <!-- Step 1: Profile Information -->
                <div class="p-3 mt-6 form-content">
                    <div class="form-header">
                        <h2 class="text-sm text-gray-400 uppercase">Step 1</h2>
                        <h3 class="text-teal-600 text-3xl font-bold">Your Profile</h3>
                    </div>
<style>
  input{
    margin-bottom: 12px;
  }
</style>
                    <form method="POST" class="registration-form" id="step1Form">
                        <div class=" form-row">
                            <div class="form-group">
                                <label class="text-sm text-gray-400 uppercase">First Name*</label>
                                <input class="block border w-full border-teal-600 outline-teal-600 p-1.5 rounded" name="first_name" required 
                                       value="<?php echo isset($_SESSION['registration_data']['first_name']) ? htmlspecialchars($_SESSION['registration_data']['first_name']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label class="text-sm text-gray-400 uppercase"  for="last_name">Last Name*</label>
                                <input class="block border w-full border-teal-600 outline-teal-600 p-1.5 rounded"  type="text" id="last_name" name="last_name" placeholder="Input Your Last Name" required
                                       value="<?php echo isset($_SESSION['registration_data']['last_name']) ? htmlspecialchars($_SESSION['registration_data']['last_name']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="text-sm text-gray-400 uppercase"  for="email">Email*</label>
                                <input class="block border w-full border-teal-600 outline-teal-600 p-1.5 rounded"  type="email" id="email" name="email" placeholder="Input Your Email" required
                                       value="<?php echo isset($_SESSION['registration_data']['email']) ? htmlspecialchars($_SESSION['registration_data']['email']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label class="text-sm text-gray-400 uppercase"  for="phone">Phone Number*</label>
                                <input class="block border w-full border-teal-600 outline-teal-600 p-1.5 rounded"  type="tel" id="phone" name="phone" placeholder="Input Your Phone Number" required
                                       value="<?php echo isset($_SESSION['registration_data']['phone']) ? htmlspecialchars($_SESSION['registration_data']['phone']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="text-sm text-gray-400 uppercase"  for="password">Password*</label>
                                <input class="block border w-full border-teal-600 outline-teal-600 p-1.5 rounded"  type="password" id="password" name="password" placeholder="Create Password" required>
                            </div>
                            <div class="form-group">
                                <label class="text-sm text-gray-400 uppercase"  for="confirm_password">Confirm Password*</label>
                                <input class="block border w-full border-teal-600 outline-teal-600 p-1.5 rounded"  type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Your Password" required>
                            </div>
                        </div>

                        <input class="block border w-full border-teal-600 outline-teal-600 p-1.5 rounded"  type="hidden" name="step1" value="1">
                    </form>
                </div>

            <?php elseif ($step == 2): ?>
                <!-- Step 2: School Information -->
                <div class="form-content p-3">
                    <div class="form-header">
                        <h2 class="text-sm text-gray-400 uppercase">Step 2</h2>
                        <h3 class="text-teal-600 text-3xl font-bold">School Information</h3>
                        <p class="text-sm text-gray-400">Select your school from the list below. If you don't see your school, please contact support.</p>
                    </div>

                    <form method="POST" class="registration-form" id="step2Form">
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label class="text-sm text-gray-800 pt-6 uppercase"  for="school_id">School Code*</label>
                                <select id="school_id" class="block border w-full border-teal-600 outline-teal-600 p-1.5 rounded"  name="school_id" required>
                                    <option value="">Select Your School</option>
                                    <?php foreach ($schools as $school): ?>
                                        <option value="<?php echo htmlspecialchars($school['school_id']); ?>"
                                                <?php echo (isset($_SESSION['registration_data']['school_id']) && $_SESSION['registration_data']['school_id'] == $school['school_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($school['school_name'] . ' (' . $school['school_id'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="school-info">
                            <div class="info-card">
                                <i class="fas ri-info-i"></i>
                                <div>
                                    <h4 class="font-bold mt-7 ">Need Help?</h4>
                                    <p>If you can't find your school in the list, please contact your school administrator or our support team.</p>
                                </div>
                            </div>
                        </div>

                        <input class="block border w-full border-teal-600 outline-teal-600 p-1.5 rounded"  type="hidden" name="step2" value="1">
                    </form>
                </div>

            <?php else: ?>
                <!-- Step 3: Complete Setup -->
                <div class="p-3 form-content">
                    <div class="form-header">
                        <h2 class="text-sm text-gray-400 uppercase">Step 3</h2>
                        <h3 class="text-teal-600 text-3xl font-bold">Complete Setup</h3>
                        <p class="text-gray-600 text-sm">Review your information and complete your registration.</p>
                    </div>

                    <?php if (isset($_SESSION['registration_data'])): ?>
                        <div class="review-info">
                            <div class="info-section">
                                <h4 class="text-lg font-semibold mt-3">Personal Information</h4>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['registration_data']['first_name'] . ' ' . $_SESSION['registration_data']['last_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['registration_data']['email']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($_SESSION['registration_data']['phone']); ?></p>
                            </div>
                            
                            <div class="info-section">
                                <h4 class="text-lg font-semibold mt-3">School Information</h4>
                                <p><strong>School:</strong> <?php echo htmlspecialchars($_SESSION['registration_data']['school_name']); ?></p>
                                <p><strong>School Code:</strong> <?php echo htmlspecialchars($_SESSION['registration_data']['school_id']); ?></p>
                            </div>
                        </div>

                        <form method="POST" class="registration-form" id="step3Form">
                            <div class="terms-section">
                                <label class="text-sm text-gray-400 uppercase"  class="checkbox-container">
                                    <input class="outline-none checked:bg-teal-600" type="checkbox" id="terms" required>
                                    <span class="checkmark"></span>
                                    I agree to the <a href="#" class="text-teal-600 underline" target="_blank">End User License Agreement</a>
                                </label>
                            </div>
                            <input class="block border w-full border-teal-600 outline-teal-600 p-1.5 rounded"  type="hidden" name="step3" value="1">
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Error/Success Messages -->
            <?php if ($error): ?>
                <div class="message error">
                    <i class="ri-error-warning-line"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="message success">
                    <i class="fas ri-checkbox-circle-fill"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Navigation Buttons -->
            <div class="p-3 flex justify-between items-center form-navigation">
                <?php if ($step > 1): ?>
                    <a href="teacher_registration.php?step=<?php echo $step - 1; ?>" class="btn btn-link text-teal-900 font-bold text-lg">
                        <i class="ri-arrow-left-line text-lg"></i> Back
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-link text-teal-900 font-bold text-lg">
                        <i class="ri-arrow-left-line"></i> Back to Login
                    </a>
                <?php endif; ?>

                <?php if ($step < 3): ?>
                    <button type="submit" form="step<?php echo $step; ?>Form" class="btn btn-primary bg-teal-600 text-white rounded py-2 px-3 ">
                        Next Step <i class="fas ri-arrow-right"></i>
                    </button>
                <?php else: ?>
                    <button type="submit" form="step3Form" class="btn btn-primary bg-teal-600 text-white rounded py-2 px-3 ">
                        Complete Registration <i class="fas ri-checkbox-circle-fill"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
      // Form validation and interactivity
class RegistrationForm {
    constructor() {
        this.initializeValidation();
        this.initializeInteractivity();
    }

    initializeValidation() {
        // Real-time validation for Step 1
        const step1Form = document.getElementById('step1Form');
        if (step1Form) {
            const passwordField = document.getElementById('password');
            const confirmPasswordField = document.getElementById('confirm_password');
            const emailField = document.getElementById('email');

            // Password strength validation
            if (passwordField) {
                passwordField.addEventListener('input', (e) => {
                    this.validatePassword(e.target);
                });
            }

            // Password confirmation validation
            if (confirmPasswordField) {
                confirmPasswordField.addEventListener('input', (e) => {
                    this.validatePasswordConfirmation(passwordField, e.target);
                });
            }

            // Email validation
            if (emailField) {
                emailField.addEventListener('blur', (e) => {
                    this.validateEmail(e.target);
                });
            }

            // Form submission validation
            step1Form.addEventListener('submit', (e) => {
                if (!this.validateStep1Form()) {
                    e.preventDefault();
                }
            });
        }

        // Step 2 validation
        const step2Form = document.getElementById('step2Form');
        if (step2Form) {
            step2Form.addEventListener('submit', (e) => {
                if (!this.validateStep2Form()) {
                    e.preventDefault();
                }
            });
        }

        // Step 3 validation
        const step3Form = document.getElementById('step3Form');
        if (step3Form) {
            step3Form.addEventListener('submit', (e) => {
                if (!this.validateStep3Form()) {
                    e.preventDefault();
                }
            });
        }
    }

    validatePassword(passwordField) {
        const password = passwordField.value;
        const minLength = 6;
        
        // Remove existing validation message
        this.removeValidationMessage(passwordField);

        if (password.length > 0 && password.length < minLength) {
            this.showValidationMessage(passwordField, `Password must be at least ${minLength} characters long`, 'error');
            return false;
        } else if (password.length >= minLength) {
            this.showValidationMessage(passwordField, 'Password strength: Good', 'success');
            return true;
        }
        return true;
    }

    validatePasswordConfirmation(passwordField, confirmPasswordField) {
        const password = passwordField.value;
        const confirmPassword = confirmPasswordField.value;
        
        // Remove existing validation message
        this.removeValidationMessage(confirmPasswordField);

        if (confirmPassword.length > 0) {
            if (password !== confirmPassword) {
                this.showValidationMessage(confirmPasswordField, 'Passwords do not match', 'error');
                return false;
            } else {
                this.showValidationMessage(confirmPasswordField, 'Passwords match', 'success');
                return true;
            }
        }
        return true;
    }

    validateEmail(emailField) {
        const email = emailField.value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        // Remove existing validation message
        this.removeValidationMessage(emailField);

        if (email.length > 0) {
            if (!emailRegex.test(email)) {
                this.showValidationMessage(emailField, 'Please enter a valid email address', 'error');
                return false;
            } else {
                this.showValidationMessage(emailField, 'Valid email address', 'success');
                return true;
            }
        }
        return true;
    }

    validateStep1Form() {
        const form = document.getElementById('step1Form');
        const requiredFields = form.querySelectorAll('input[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                this.showValidationMessage(field, 'This field is required', 'error');
                isValid = false;
            }
        });

        // Check password match
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            this.showValidationMessage(document.getElementById('confirm_password'), 'Passwords do not match', 'error');
            isValid = false;
        }

        if (password.length < 6) {
            this.showValidationMessage(document.getElementById('password'), 'Password must be at least 6 characters long', 'error');
            isValid = false;
        }

        return isValid;
    }

    validateStep2Form() {
        const schoolSelect = document.getElementById('school_id');
        if (!schoolSelect.value) {
            this.showValidationMessage(schoolSelect, 'Please select a school', 'error');
            return false;
        }
        return true;
    }

    validateStep3Form() {
        const termsCheckbox = document.getElementById('terms');
        if (!termsCheckbox.checked) {
            alert('Please accept the Terms of Service and Privacy Policy to continue.');
            return false;
        }
        return true;
    }

    showValidationMessage(field, message, type) {
        this.removeValidationMessage(field);
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `validation-message ${type}`;
        messageDiv.textContent = message;
        
        field.parentNode.appendChild(messageDiv);
        
        // Add visual feedback to field
        field.classList.remove('valid', 'invalid');
        field.classList.add(type === 'success' ? 'valid' : 'invalid');
    }

    removeValidationMessage(field) {
        const existingMessage = field.parentNode.querySelector('.validation-message');
        if (existingMessage) {
            existingMessage.remove();
        }
        field.classList.remove('valid', 'invalid');
    }

    initializeInteractivity() {
        // Smooth scrolling for navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Auto-focus first input field
        const firstInput = document.querySelector('input:not([type="hidden"])');
        if (firstInput) {
            firstInput.focus();
        }

        // Phone number formatting
        const phoneField = document.getElementById('phone');
        if (phoneField) {
            phoneField.addEventListener('input', (e) => {
                this.formatPhoneNumber(e.target);
            });
        }

        // Form field animations
        document.querySelectorAll('input, select').forEach(field => {
            field.addEventListener('focus', (e) => {
                e.target.parentNode.classList.add('focused');
            });

            field.addEventListener('blur', (e) => {
                e.target.parentNode.classList.remove('focused');
                if (e.target.value) {
                    e.target.parentNode.classList.add('filled');
                } else {
                    e.target.parentNode.classList.remove('filled');
                }
            });

            // Check if field is pre-filled
            if (field.value) {
                field.parentNode.classList.add('filled');
            }
        });

        // Loading states for form submission
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas ri-loader-2-line ri-spin"></i> Processing...';
                }
            });
        });
    }

    formatPhoneNumber(field) {
        let value = field.value.replace(/\D/g, '');
        
        if (value.length >= 6) {
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        } else if (value.length >= 3) {
            value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
        }
        
        field.value = value;
    }
}

// Progress animation
class ProgressAnimation {
    constructor() {
        this.animateProgress();
    }

    animateProgress() {
        const steps = document.querySelectorAll('.step');
        steps.forEach((step, index) => {
            setTimeout(() => {
                step.style.opacity = '0';
                step.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    step.style.transition = 'all 0.5s ease';
                    step.style.opacity = '1';
                    step.style.transform = 'translateY(0)';
                }, 100);
            }, index * 200);
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new RegistrationForm();
    new ProgressAnimation();
    
    // Add CSS for validation states
    const style = document.createElement('style');
    style.textContent = `
        .validation-message {
            font-size: 12px;
            margin-top: 5px;
            padding: 5px 10px;
            border-radius: 6px;
        }
        
        .validation-message.error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .validation-message.success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
        
        .form-group input.valid {
            border-color: #16a34a;
        }
        
        .form-group input.invalid {
            border-color: #dc2626;
        }
        
        .form-group.focused {
            transform: scale(1.02);
            transition: transform 0.2s ease;
        }
        
        .form-group.filled label {
            color: #667eea;
            font-weight: 600;
        }
    `;
    document.head.appendChild(style);
});

// Handle browser back/forward buttons
window.addEventListener('popstate', (e) => {
    if (e.state && e.state.step) {
        window.location.href = `teacher_registration.php?step=${e.state.step}`;
    }
});

// Add current step to browser history
const currentStep = new URLSearchParams(window.location.search).get('step') || '1';
history.replaceState({ step: currentStep }, '', window.location.href);
    </script>
</body>
</html>