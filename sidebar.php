<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <a href="admindashboard.php">Dashboard</a>
    <a href="orders.php">Orders</a>
    <a href="add_menu_item.php">Edit Menu</a>
    <a href="review_moderation.php">Review Moderation</a>
    <a href="logout.php">Logout</a>
</div>

<!-- Button to open sidebar -->
<button class="openbtn" id="openbtn" onclick="openNav()">&#9776;</button>

<script>
    // Open sidebar function
    function openNav() {
        document.getElementById("sidebar").style.left = "0"; // Slide in from the left
        document.body.style.marginLeft = "250px"; // Push the content to the right
        document.getElementById("openbtn").style.display = "none"; // Hide the open button
    }

    // Close sidebar function
    function closeNav() {
        document.getElementById("sidebar").style.left = "-250px"; // Slide out to the left
        document.body.style.marginLeft = "0"; // Reset content margin
        document.getElementById("openbtn").style.display = "block"; // Show the open button
    }
</script>

<style>
    /* Sidebar Styles */
    .sidebar {
        height: 100%;
        width: 250px;
        position: fixed;
        left: -250px; /* Initially off-screen */
        top: 0;
        background-color: #333;
        color: gold; /* Changed text color to gold */
        z-index: 100;
        padding-top: 60px;
        transition: left 0.3s ease; /* Animation for sliding in */
    }

    .sidebar a {
        color: gold; /* Changed text color to gold */
        text-decoration: none;
        display: block;
        padding: 15px;
        font-size: 18px;
        transition: background-color 0.3s ease;
    }

    .sidebar a:hover {
        background-color: #575757;
    }

    .sidebar .closebtn {
        position: absolute;
        top: 0;
        right: 25px;
        font-size: 36px;
        margin-left: 50px;
        cursor: pointer;
        color: gold; /* Changed close button color to gold */
    }

    /* Open/Close button styles */
    .openbtn {
        font-size: 30px;
        cursor: pointer;
        position: absolute;
        top: 20px;
        left: 20px;
        z-index: 200;
        background-color: transparent;
        border: none;
        color: gold; /* Changed button color to gold */
        transition: color 0.3s ease;
    }
</style>
