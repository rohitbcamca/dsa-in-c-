<?php

session_start();
if (isset($_SESSION['error'])) {
    echo "<div style='color: red; text-align: center; font-weight: bold;'>".$_SESSION['error']."</div>";
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Create Your Account - Tour and Travel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a2a6c;
            --secondary-color: #b21f1f;
            --accent-color: #fdbb2d;
            --text-color: #333;
            --light-gray: #f5f5f5;
            --border-color: #ddd;
            --error-color: #dc3545;
            --success-color: #28a745;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            padding: 40px;
            position: relative;
        }

        h2 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 5px;
            font-size: 16px;
            transition: all 0.3s ease;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-color: white;
        }

        .form-group input:-webkit-autofill,
        .form-group input:-webkit-autofill:hover,
        .form-group input:-webkit-autofill:focus,
        .form-group input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px white inset !important;
            -webkit-text-fill-color: var(--text-color) !important;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 42, 108, 0.1);
            background-color: white;
        }

        .btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: var(--text-color);
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .registration-type {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .registration-type button {
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .registration-type button.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .registration-type button:not(.active) {
            background: var(--light-gray);
            color: var(--text-color);
        }
        
        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
        }

        .nav-links {
            text-align: center;
            margin-bottom: 20px;
        }

        .nav-links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .nav-links a:hover {
            text-decoration: underline;
        }

        .error, .success {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: 500;
        }

        .error {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--error-color);
            border: 1px solid var(--error-color);
        }

        .success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }

        .rating-stars {
            display: flex;
            gap: 5px;
            margin: 10px 0;
        }

        .rating-stars i {
            font-size: 24px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.3s;
        }

        .rating-stars i.active {
            color: #ffd700;
        }

        .rating-stars i:hover,
        .rating-stars i:hover ~ i {
            color: #ffd700;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create Your Account</h2>
        <div class="nav-links">
            <a href="home"><i class="fas fa-home"></i> Back to Home</a>
        </div>

        <div class="registration-type">
            <button type="button" class="active" data-type="user">User Registration</button>
            <button type="button" data-type="hotel">Hotel Registration</button>
            <button type="button" data-type="driver">Cab Driver Registration</button>
        </div>

        <form action="/Tour and Travel/process_registration.php" method="post" id="registrationForm">

            <input type="hidden" name="role" id="role" value="user">
            
            <!-- User Registration Form -->
            <div class="form-section active" id="userForm">
                <div class="form-group">
                    <label for="user_name">Full Name</label>
                    <input type="text" id="user_name" name="user_name">
                </div>
                
                <div class="form-group">
                    <label for="user_email">Email Address</label>
                    <input type="email" id="user_email" name="user_email">
                </div>
                
                <div class="form-group">
                    <label for="user_password">Password</label>
                    <input type="password" id="user_password" name="user_password">
                </div>

                <div class="form-group">
                    <label for="user_phone">Phone Number</label>
                    <input type="tel" id="user_phone" name="user_phone">
                </div>
                
                <div class="form-group">
                    <label for="user_address">Address</label>
                    <textarea id="user_address" name="user_address" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="user_country">Country</label>
                    <input type="text" id="user_country" name="user_country" placeholder="Enter country name">
                </div>

                <div class="form-group">
                    <label for="user_state">State</label>
                    <input type="text" id="user_state" name="user_state" placeholder="Enter state name">
                </div>

                <div class="form-group">
                    <label for="user_city">City</label>
                    <input type="text" id="user_city" name="user_city" placeholder="Enter city name">
                </div>
            </div>

            <!-- Hotel Registration Form -->
            <div class="form-section" id="hotelForm">
                <div class="form-group">
                    <label for="hotel_owner_name">Owner Name</label>
                    <input type="text" id="hotel_owner_name" name="hotel_owner_name">
                </div>
                
                <div class="form-group">
                    <label for="hotel_name">Hotel Name</label>
                    <input type="text" id="hotel_name" name="hotel_name">
                </div>

                <div class="form-group">
                    <label for="hotel_email">Email Address</label>
                    <input type="email" id="hotel_email" name="hotel_email">
                </div>

                <div class="form-group">
                    <label for="hotel_password">Password</label>
                    <input type="password" id="hotel_password" name="hotel_password">
                </div>
                
                <div class="form-group">
                    <label for="hotel_phone">Phone Number</label>
                    <input type="tel" id="hotel_phone" name="hotel_phone">
                </div>
                
                <div class="form-group">
                    <label for="hotel_address">Hotel Address</label>
                    <textarea id="hotel_address" name="hotel_address" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="hotel_country">Country</label>
                    <input type="text" id="hotel_country" name="hotel_country" placeholder="Enter country name">
                </div>

                <div class="form-group">
                    <label for="hotel_state">State</label>
                    <input type="text" id="hotel_state" name="hotel_state" placeholder="Enter state name">
                </div>
                
                <div class="form-group">
                    <label for="hotel_city">City</label>
                    <input type="text" id="hotel_city" name="hotel_city" placeholder="Enter city name">
                </div>

                <div class="form-group">
                    <label for="hotel_rating">Hotel Rating (1-7 stars)</label>
                    <div class="rating-stars">
                        <?php for ($i = 1; $i <= 7; $i++): ?>
                            <i class="fas fa-star" data-rating="<?php echo $i; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" id="hotel_rating" name="hotel_rating" value="1">
                </div>
            </div>

            <!-- Cab Driver Registration Form -->
            <div class="form-section" id="driverForm">
                <div class="form-group">
                    <label for="driver_name">Full Name</label>
                    <input type="text" id="driver_name" name="driver_name">
                </div>
                
                <div class="form-group">
                    <label for="driver_email">Email Address</label>
                    <input type="email" id="driver_email" name="driver_email">
                </div>
                
                <div class="form-group">
                    <label for="driver_password">Password</label>
                    <input type="password" id="driver_password" name="driver_password">
                </div>
                
                <div class="form-group">
                    <label for="driver_phone">Phone Number</label>
                    <input type="tel" id="driver_phone" name="driver_phone">
                </div>
                
                <div class="form-group">
                    <label for="driver_address">Address</label>
                    <textarea id="driver_address" name="driver_address" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="driver_country">Country</label>
                    <input type="text" id="driver_country" name="driver_country" placeholder="Enter country name">
                </div>

                <div class="form-group">
                    <label for="driver_state">State</label>
                    <input type="text" id="driver_state" name="driver_state" placeholder="Enter state name">
                </div>
                
                <div class="form-group">
                    <label for="driver_city">City</label>
                    <input type="text" id="driver_city" name="driver_city" placeholder="Enter city name">
                </div>
                
                <div class="form-group">
                    <label for="driver_license">License Number</label>
                    <input type="text" id="driver_license" name="driver_license">
                </div>
                
                <div class="form-group">
                    <label for="driver_vehicle_type">Vehicle Type</label>
                    <input type="text" id="driver_vehicle_type" name="driver_vehicle_type">
                </div>
                
                <div class="form-group">
                    <label for="driver_vehicle_number">Vehicle Number</label>
                    <input type="text" id="driver_vehicle_number" name="driver_vehicle_number">
                </div>
            </div>

            <button type="submit" class="btn" name="register">Register</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="/Tour and Travel/login.php">Login here</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form type switching
            const typeButtons = document.querySelectorAll('.registration-type button');
            const formSections = document.querySelectorAll('.form-section');
            const roleInput = document.getElementById('role');
            
            // Handle form type switching
            typeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const type = this.getAttribute('data-type');
                    
                    // Update active button
                    typeButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update active form
                    formSections.forEach(section => section.classList.remove('active'));
                    document.getElementById(type + 'Form').classList.add('active');
                    
                    // Update role input value
                    roleInput.value = type;
                });
            });

            // Form validation
            function validateForm(formElement) {
                let isValid = true;
                const inputs = formElement.querySelectorAll('input[type="text"], input[type="email"], input[type="password"], input[type="tel"], textarea');
                
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.style.borderColor = 'red';
                        isValid = false;
                    } else {
                        input.style.borderColor = '';
                    }

                    if (input.type === 'email' && input.value) {
                        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailPattern.test(input.value)) {
                            input.style.borderColor = 'red';
                            isValid = false;
                        }
                    }

                    if (input.type === 'tel' && input.value) {
                        const phonePattern = /^\d{10}$/;
                        if (!phonePattern.test(input.value.replace(/\D/g, ''))) {
                            input.style.borderColor = 'red';
                            isValid = false;
                        }
                    }
                });

                return isValid;
            }

            // Form submission
            document.querySelector('form').addEventListener('submit', function(e) {
                const activeForm = document.querySelector('.form-section.active');
                if (!validateForm(activeForm)) {
                    e.preventDefault();
                }
            });

            // Reset validation on input
            document.querySelectorAll('input, textarea').forEach(input => {
                input.addEventListener('input', function() {
                    this.style.borderColor = '';
                });
            });

            const stars = document.querySelectorAll('.rating-stars i');
            const ratingInput = document.getElementById('hotel_rating');
            
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = this.getAttribute('data-rating');
                    ratingInput.value = rating;
                    
                    stars.forEach(s => {
                        if (s.getAttribute('data-rating') <= rating) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>

