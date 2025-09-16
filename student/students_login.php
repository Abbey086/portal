<?php
session_start();
require 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM students WHERE email = :email");
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $stmt->execute();
    $student = $result->fetchArray(SQLITE3_ASSOC);

    if ($student && password_verify($password, $student['password'])) {
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['full_name'];
        $_SESSION['school_id'] = $student['school_id'];
        $_SESSION['grade_level'] = $student['grade_level'];

        header("Location: student_dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student - Login</title>
      <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>
</head>
<body class="md:px-[27vw]">
                <img src="../global/dd2.png" class="fill-teal-600 md:w-1/4 w-1/3 mx-auto mt-6">
        <h2 class="text-3xl font-bold text-center mb-12">Student Log In </h2>

                <form id="loginForm" class="p-3" method="post"><?php if ($error): ?><p style="color:red;"><?php echo $error; ?></p><?php endif; ?>

                    <div class="form-group">
                        <label for="email" class="text-sm font-semibold text-gray-700 block">Email</label>
                        <input 
                            type="email" 
                            id="email" name="email"
                            class="w-full p-2 border outline-teal-600 rounded mb-3" 
                            placeholder="Enter your email"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="text-sm font-semibold text-gray-700 block">Password</label>
                        <input 
                            type="password" 
                            id="password" name="password"
                            class="w-full p-2 border outline-teal-600 rounded mb-3" 
                            placeholder="Enter your password"
                            required
                        >
                    </div>
                            <p class="text-sm mt-4 text-right">Don't have an account? <a href="students_register.php" class="text-teal-600 underline">Sign Up</a></p>

                    <button type="submit" class="bg-teal-600 text-white rounded font-bold text-sm py-2 px-4"  id="loginBtn">
                        Login
                    </button>
                </form>

    <script>
        // Modal functionality
        function closeModal() {
            const modal = document.getElementById('modalOverlay');
            modal.style.animation = 'modalSlideOut 0.3s ease-in forwards';
            
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        // Add slide out animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes modalSlideOut {
                from {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
                to {
                    opacity: 0;
                    transform: translateY(-30px) scale(0.95);
                }
            }
        `;
        document.head.appendChild(style);

        // Form handling
        function handleLogin(event) {
            event.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const loginBtn = document.getElementById('loginBtn');
            const form = document.getElementById('loginForm');
            
            // Add loading state
            form.classList.add('loading');
            loginBtn.textContent = '';
            
            // Simulate login process
            setTimeout(() => {
                form.classList.remove('loading');
                loginBtn.textContent = 'Login';
                
                if (email && password) {
                    alert(`Welcome back! Logging in with email: ${email}`);
                    // Here you would typically redirect to dashboard
                    // window.location.href = '/dashboard';
                } else {
                    alert('Please fill in all fields');
                }
            }, 2000);
        }

        // Social login functions
        function loginWithGoogle() {
            const btn = event.target;
            btn.style.transform = 'scale(0.98)';
            
            setTimeout(() => {
                btn.style.transform = 'scale(1)';
                alert('Redirecting to Google login...');
                // Here you would integrate with Google OAuth
            }, 150);
        }

        function loginWithFacebook() {
            const btn = event.target;
            btn.style.transform = 'scale(0.98)';
            
            setTimeout(() => {
                btn.style.transform = 'scale(1)';
                alert('Redirecting to Facebook login...');
                // Here you would integrate with Facebook OAuth
            }, 150);
        }

        // Sign up function
        function showSignUp() {
            alert('Redirecting to Sign Up page...');
            // Here you would show sign up modal or redirect
        }

        // Close modal when clicking outside
        document.getElementById('modalOverlay').addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // Form validation with real-time feedback
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        emailInput.addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.style.borderColor = '#e74c3c';
                this.style.background = '#fdf2f2';
            } else {
                this.style.borderColor = '#e9ecef';
                this.style.background = '#f8f9fa';
            }
        });

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            if (password.length > 0 && password.length < 6) {
                this.style.borderColor = '#f39c12';
                this.style.background = '#fef9e7';
            } else if (password.length >= 6) {
                this.style.borderColor = '#27ae60';
                this.style.background = '#f0fff4';
            } else {
                this.style.borderColor = '#e9ecef';
                this.style.background = '#f8f9fa';
            }
        });

        // Add focus animations
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
                this.parentElement.style.transition = 'transform 0.3s ease';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });

        // Initialize modal
        document.addEventListener('DOMContentLoaded', function() {
            // Focus on email input when modal loads
            setTimeout(() => {
                document.getElementById('email').focus();
            }, 500);
        });
    </script>
</body>
</html>