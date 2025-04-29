<?php
session_start();

// Check if logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$database = "demochatapp";

// Create a connection
$conn = mysqli_connect($servername, $db_username, $db_password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get total users count
$users_query = "SELECT COUNT(*) as total_users FROM users";
$users_result = mysqli_query($conn, $users_query);
$users_data = mysqli_fetch_assoc($users_result);
$total_users = $users_data['total_users'];

// Get total teachers count
$teachers_query = "SELECT COUNT(*) as total_teachers FROM teachers";
$teachers_result = mysqli_query($conn, $teachers_query);
$teachers_data = mysqli_fetch_assoc($teachers_result);
$total_teachers = $teachers_data['total_teachers'];

// Get recent users
$recent_users_query = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
$recent_users_result = mysqli_query($conn, $recent_users_query);

// Get recent teachers
$recent_teachers_query = "SELECT * FROM teachers ORDER BY created_at DESC LIMIT 5";
$recent_teachers_result = mysqli_query($conn, $recent_teachers_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: #f5f5f5;
        }
        
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: #5995fd;
            color: white;
            padding: 20px;
        }
        
        .sidebar h2 {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .sidebar ul {
            list-style: none;
        }
        
        .sidebar ul li {
            margin-bottom: 15px;
        }
        
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .sidebar ul li a:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar ul li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .header h1 {
            color: #333;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info span {
            margin-right: 15px;
        }
        
        .logout-btn {
            background: #ff3860;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: #e5304e;
        }
        
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 5px;
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card i {
            font-size: 30px;
            margin-bottom: 10px;
            color: #5995fd;
        }
        
        .stat-card h3 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #333;
        }
        
        .stat-card p {
            color: #666;
            font-size: 14px;
        }
        
        .recent-section {
            background: white;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .recent-section h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 20px;
        }
        
        .users-table, .teachers-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .users-table th, .users-table td,
        .teachers-table th, .teachers-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .users-table th, .teachers-table th {
            font-weight: 600;
            color: #333;
        }
        
        .users-table tr:hover, .teachers-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .delete-btn {
            background: #ff3860;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            transition: background 0.3s;
        }
        
        .delete-btn:hover {
            background: #e5304e;
        }
        
        .delete-btn i {
            margin-right: 5px;
        }
        
        .view-all {
            display: block;
            text-align: right;
            margin-top: 15px;
            color: #5995fd;
            text-decoration: none;
            font-size: 14px;
        }
        
        .view-all:hover {
            text-decoration: underline;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        @media (max-width: 992px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
            .stats {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="admin_teachers.php"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
                <li><a href="#"><i class="fas fa-comments"></i> Messages</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <div class="stats">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3><?php echo $total_users; ?></h3>
                    <p>Total Users</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h3><?php echo $total_teachers; ?></h3>
                    <p>Total Teachers</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-comments"></i>
                    <h3>0</h3>
                    <p>Total Messages</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-user-plus"></i>
                    <?php 
                    // Get count of new users registered today
                    $new_users_query = "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()";
                    $new_users_result = mysqli_query($conn, $new_users_query);
                    $new_users = mysqli_fetch_assoc($new_users_result)['count'];
                    ?>
                    <h3><?php echo $new_users; ?></h3>
                    <p>New Users Today</p>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <div class="recent-section">
                    <h2>Recent Users</h2>
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = mysqli_fetch_assoc($recent_users_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="admin_delete_user.php?id=<?php echo $user['id']; ?>" 
                                       class="delete-btn" 
                                       onclick="return confirm('Are you sure you want to delete this user?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <a href="admin_users.php" class="view-all">View All Users</a>
                </div>
                
                <div class="recent-section">
                    <h2>Recent Teachers</h2>
                    <table class="teachers-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Expertise</th>
                                <th>Added Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($teacher = mysqli_fetch_assoc($recent_teachers_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['expertise']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($teacher['created_at'])); ?></td>
                                <td>
                                    <a href="admin_teachers.php?action=delete&id=<?php echo $teacher['id']; ?>" 
                                       class="delete-btn" 
                                       onclick="return confirm('Are you sure you want to delete this teacher?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <a href="admin_teachers.php" class="view-all">View All Teachers</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>