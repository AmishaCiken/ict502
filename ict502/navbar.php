<style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
    }

    .navbar {
        width: 100%;
        height: 60px;
        background: transparent; /* Background set to transparent */
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 25px;
        box-sizing: border-box;
        color: white;
    }

    .welcome {
        font-size: 14px;
        font-weight: bold;
    }

    .icon {
        display: flex;
        align-items: center;
    }

    .logo {
        color: #ff7200;
        font-size: 40px;
        margin-left: 160px;
    }

    .menu {
        flex: 1;
        display: flex;
        justify-content: center;
    }

    .menu ul {
        list-style: none;
        display: flex;
        margin: 0;
        padding: 0;
    }

    .menu ul li {
        margin-left: 40px;
    }

    .menu ul li a {
        text-decoration: none;
        color: #fff;
        font-weight: bold;
        transition: color 0.4s ease-in-out;
    }

    .menu ul li a:hover {
        color: #ff7200;
    }

    .logout {
        display: flex;
        align-items: center;
        font-size: 14px;
    }

    .logout a {
        text-decoration: none;
        color: #fff;
        font-weight: bold;
        padding: 0 13px;
        transition: color 0.4s ease-in-out;
    }

    .logout a:hover {
        color: #ff7200;
    }
</style>

<div class="navbar">

    <div class="icon">
        <h1 class="logo">CFS</h1>
    </div>

    <div class="menu">
        <ul>
            <li><a href="mainpage.php">HOME</a></li>
            <li><a href="about.php">ABOUT</a></li>
            <li><a href="service.php">SERVICE</a></li>
            <li><a href="contact.php">CONTACT</a></li>
        </ul>
    </div>
</div>
