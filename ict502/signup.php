<!DOCTYPE html>
<html lang="en">

<head>
    <title>Sign Up</title>
    <link rel="stylesheet" href="style2.css">
</head>

<body>
    <div class="main">
        <div class="form">
            <h2>Register as Farmer</h2>
            <form action="submit_signup.php" method="POST">
                <div class="user-details">
                    <div class="input-box">
                        <input type="text" name="first_name" required placeholder="First Name">
                    </div>
                    <div class="input-box">
                        <input type="text" name="last_name" required placeholder="Last Name">
                    </div>
                    <div class="input-box">
                        <input type="text" name="phone_number" required placeholder="Phone Number">
                    </div>
                    <div class="input-box">
                        <input type="email" name="email_address" required placeholder="Email Address">
                    </div>
                    <div class="input-box">
                        <input type="password" name="password" required placeholder="Password">
                    </div>
                </div>
                <div class="button">
                    <input type="submit" value="Register">
                </div>
            </form>
        </div>
    </div>
</body>

</html>
