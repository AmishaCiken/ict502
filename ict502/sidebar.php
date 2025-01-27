<?php
include('conn/conn.php'); // Include the database connection
?>
<div class="bg-sidebar vh-100 w-50 position-fixed" id="sidebar">
    <!-- Logo and Close Button -->
    <div class="log d-flex justify-content-between">
        <h1 class="E-classe text-start ms-3 ps-1 mt-3 h6 fw-bold">Farming System</h1>
        <i class="far fa-times h4 me-3 close align-self-end d-md-none" id="closeSidebar"></i>
    </div>

    <!-- Admin Profile Section -->
    <div class="img-admin d-flex flex-column align-items-center text-center gap-2">
      

    </div>

    <!-- Navigation Links -->
    <div class="bg-list d-flex flex-column align-items-center fw-bold gap-2 mt-4">
        <ul class="d-flex flex-column list-unstyled">
            <li class="h7">
                <a class="nav-link text-dark" href="available_farm.php">
                    <i class="fal fa-home-lg-alt me-2"></i> <span>Available Farm</span>
                </a>
            </li>
            <li class="h7">
                <a class="nav-link text-dark" href="booking_farm.php">
                    <i class="fal fa-bookmark me-2"></i> <span>Booking Farm</span>
                </a>
            </li>
            <li class="h7">
                <a class="nav-link text-dark" href="tool.php">
                    <i class="far fa-graduation-cap me-2"></i> <span>Add Tools</span>
                </a>
            </li>
            <li class="h7">
                <a class="nav-link text-dark" href="animal.php">
                    <i class="fal fa-usd-square me-2"></i> <span>Animals</span>
                </a>
            </li>

            <li class="h7">
                <a class="nav-link text-dark" href="animal_produce.php">
                    <i class="fal fa-usd-square me-2"></i> <span>Animal Produce</span>
                </a>
            </li>

            <li class="h7">
                <a class="nav-link text-dark" href="crop.php">
                    <i class="fal fa-file-chart-line me-2"></i> <span>Crops</span>
                </a>
            </li>

            <li class="h7">
                <a class="nav-link text-dark" href="crop_produce.php">
                    <i class="fal fa-file-chart-line me-2"></i> <span>Crop Produce</span>
                </a>
            </li>
            
        </ul>

        <!-- Logout -->
        <ul class="logout d-flex justify-content-start list-unstyled">
            <li class="h7">
                <a class="nav-link text-dark" href="logout.php">
                    <span>Logout</span><i class="fal fa-sign-out-alt ms-2"></i>
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- JavaScript for Close Button -->
<script>
    document.getElementById('closeSidebar').addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('d-none'); // Toggle visibility
    });
</script>
