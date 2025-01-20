<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi Role Login System</title>

    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            background: #666;
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;        
            overflow: hidden;
            height: 100vh;
        }
        
        .main {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: rgba(0, 0, 0, 0.4);
            height: 100vh;
        }

        .login-container, .registration-container {
            width: 500px;
            box-shadow: rgba(255, 255, 255, 0.24) 0px 3px 8px;
            border-radius: 10px;
            background-color: rgba(0, 0, 0, 0.7);
            padding: 30px;
            color: rgb(255, 255, 255);
        }

        .title-container > h1 {
            font-size: 70px !important;
            color: rgb(255, 255, 255);
            text-shadow: 2px 4px 2px rgba(200,200,200,0.6);
        }

        .h2 {
            font-size: 60px !important;
            color: rgb(255, 255, 255);
            text-shadow: 2px 4px 2px rgba(200,200,200,0.6);
        }

        .h3 {
            font-size: 45px !important;
            color: rgb(255, 255, 255);
            text-shadow: 2px 4px 2px rgba(200,200,200,0.6);
        }

        .show-form {
            color: rgb(100, 100, 200);
            text-decoration: underline;
            cursor: pointer;
        }

        .show-form:hover {
            color: rgb(100, 100, 255);
        }
    </style>
</head>
<body>
    <div id="navbar"></div>
    <div class="main row">
    <div id="navbar"></div>
        <div class="title-container col-6">
            <h1>Community Farming </h1>
            <h2><br>Farming Form Digitalization</h2>
            <p class="par">A system where a group of people work together to grow crops or raise animals on shared land. <br>
                Everyone contributes by helping with tasks like planting, watering, and harvesting. <br>
                The food produced is usually shared among the group or sold to benefit the community. <br>
                It’s a way to work as a team, use resources wisely, and support everyone involved.</p>

        </div>

        <div class="main-container col-4">
        <!-- Login Form -->
        <div class="login-container" id="loginForm">
            <h3 class="text-center">Farmer Login</h3>
            <div class="login-form">
                <form action="./endpoint/login.php" method="POST">
                    <div class="form-group">
                        <label for="email_address">Email Address:</label>
                        <input type="email" class="form-control" id="email_address" name="email_address" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="row m-auto">
                    <div class="form-group form-check col-6">
                        <input type="checkbox" class="form-check-input" id="exampleCheck1">
                        <label class="form-check-label" for="exampleCheck1">Remember Password</label>
                    </div>
                    <small class="show-form col-6 text-center pl-4" onclick="showForm()">No Account? Register Here.</small>
                    </div>

                    <button type="submit" class="btn btn-primary login-btn form-control">Login</button>
                </form>
            </div>
        </div>

        <!-- Registration Area -->
            <div class="registration-container" id="registrationForm" style="display:none;">
                <h2 class="text-center">Register Your Account!</h2>
                <p class="text-center">Please enter your personal details.</p>
                <div class="registration-form">
                <form action="./endpoint/add-user.php" method="POST">
                    <div class="form-group">
                        <label for="first_name">First Name:</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name:</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone Number:</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                    </div>
                    <div class="form-group">
                        <label for="email_address">Email Address:</label>
                        <input type="email" class="form-control" id="email_address" name="email_address" required>
                    </div>
                    <div class="form-group">
                        <label for="registerPassword">Password:</label>
                        <input type="password" class="form-control" id="registerPassword" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password:</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    <div class="form-group float-right">
                        <small class="show-form" onclick="showForm()">Already have an account? Login Here.</small>
                    </div>
                    <button type="submit" class="btn btn-primary login-register form-control">Register</button>
                </form>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap 4 JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

    <!-- Load navbar dynamically -->
    <script>
        fetch('navbar.php')
            .then(response => response.text())
            .then(data => document.getElementById('navbar').innerHTML = data)
            .catch(error => console.error('Error loading navbar:', error));
    </script>

    <script>
        function showForm() {
            const loginForm = document.getElementById('loginForm');
            const registrationForm = document.getElementById('registrationForm');

            if (registrationForm.style.display == 'none') {
                loginForm.style.display = 'none';
                registrationForm.style.display = '';
            } else {
                loginForm.style.display = '';
                registrationForm.style.display = 'none';
            }
        }
    </script>
</body>
</html>
