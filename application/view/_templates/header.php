<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$role = $_SESSION['role'] ?? null;
$user_email = $_SESSION['user_email'] ?? '';
$user_name = ucwords(str_replace(['.', '_', '-'], ' ', explode('@', $user_email)[0] ?? 'User'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Staff Management System">
    <meta name="author" content="Evidence Action">
    <title>EA OPERATIONS</title>

    <!-- Bootstrap & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Custom CSS -->
    <link href="<?php echo URL; ?>css/style.css" rel="stylesheet">
    <link rel="icon" type="image/jpg" sizes="16x16" href="<?php echo URL; ?>img/icon.jpg">

    <style>
        @font-face {
            font-family: ArchivoBlack;
            src: url("<?php echo URL; ?>/fonts/ArchivoBlack-Regular.ttf");
        }

        .bt-logout {
            background-color: #20253a;
            color: #fff;
            border-style: none;
            padding: .5rem 1rem;
            font-size: 16px;
            border-radius: 5px;
            margin-right: 2rem;
            text-decoration: none;
        }

        .bt-logout:hover {
            background-color: #e600a0;
            color: #fff;
        }

        .nav-option:hover,
        .dropdown-item:hover {
            background-color: #20253a !important;
            color: #ffffff !important;
        }

        .active,
        .nav-item.active,
        .nav-item.active .nav-link,
        .dropdown-item.active {
            background-color: #e600a0 !important;
            font-weight: 600;
            color: #ffffff !important;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-toggle {
            cursor: pointer;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            min-width: 200px;
            z-index: 1000;
        }

        .dropdown-item {
            padding: 10px;
            font-size: 14px;
            color: #20253a;
            text-decoration: none;
            display: block;
        }

        .dropdown:hover .dropdown-menu {
            display: block;
        }

        @media(max-width: 768px) {
            .dropdown-menu {
                position: static;
                float: none;
            }
        }
    </style>
</head>
<body>

<?php
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$current_page = trim(basename($uri), '/');
function isActive($pages, $current) {
    return is_array($pages) ? (in_array($current, $pages) ? 'active' : '') : ($current === $pages ? 'active' : '');
}
?>

<!-- NAVIGATION -->
<div class="top-nav navbar navbar-expand-lg navbar-light px-3" style="background-color: #d5f4f7;">
    <a href="<?php echo URL; ?>home" class="navbar-brand">
        <img src="<?php echo URL; ?>img/ea_logo.png" alt="logo" style="padding: 10px; width: 150px; height: auto;">
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
        <div class="navbar-nav mx-auto d-flex align-items-center gap-3">
            
            <!-- Dashboard Link -->
            <a href="<?php echo URL; ?>home/index" class="nav-link">
                <i class="fas fa-tachometer-alt"></i> DASHBOARD
            </a>
            
            <!-- Trip Requests Dropdown -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                    <i class="fas fa-car"></i> TRIP REQUESTS
                </a>
                <div class="dropdown-menu">
                    <!-- My Requests - Shows both Intrastate and Interstate -->
                    <a href="<?php echo URL; ?>intrastate/index" class="dropdown-item">
                        <i class="fas fa-file-alt"></i> My Requests
                    </a>
                    <div class="dropdown-divider"></div>
                    
                    <!-- New Request Forms -->
                    <a href="<?php echo URL; ?>intrastate/create" class="dropdown-item">
                        <i class="fas fa-plus-circle text-primary"></i> New Intrastate Request
                    </a>
                    <a href="<?php echo URL; ?>interstate/create" class="dropdown-item">
                        <i class="fas fa-plus-circle text-success"></i> New Interstate Request
                    </a>
                    <div class="dropdown-divider"></div>
                    
                    <!-- Test Requests (Legacy) -->
                    <a href="<?php echo URL; ?>trip/myrequests" class="dropdown-item">
                        <i class="fas fa-flask"></i> Test Requests
                    </a>
                </div>
            </div>
            
            <!-- Approvals Dropdown -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                    <i class="fas fa-check-double"></i> APPROVALS
                </a>
                <div class="dropdown-menu">
                    <a href="<?php echo URL; ?>intrastate/allTrips" class="dropdown-item">
                        <i class="fas fa-list-alt text-dark"></i> All Trips
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo URL; ?>intrastate/operationsDashboard" class="dropdown-item">
                        <i class="fas fa-clock"></i> Intrastate Approvals
                    </a>
                    <a href="<?php echo URL; ?>interstate/operationsDashboard" class="dropdown-item">
                        <i class="fas fa-clock"></i> Interstate Approvals
                    </a>
                    <?php if ($role === 'admin' || $role === 'super_admin'): ?>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo URL; ?>trip/supervisorDashboard" class="dropdown-item">
                            <i class="fas fa-user-tie"></i> Test Approvals (Supervisor)
                        </a>
                        <a href="<?php echo URL; ?>trip/operationsDashboard" class="dropdown-item">
                            <i class="fas fa-chart-line"></i> Test Operations Dashboard
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Configurations Dropdown (Admin only) -->
            <?php if ($role === 'admin' || $role === 'super_admin'): ?>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i> CONFIGURATIONS
                    </a>
                    <div class="dropdown-menu">
                        <!-- User Management -->
                        <a href="<?php echo URL; ?>users" class="dropdown-item">
                            <i class="fas fa-users"></i> User Management
                        </a>
                        <div class="dropdown-divider"></div>
                        
                        <!-- Organization -->
                        <a href="<?php echo URL; ?>department/index" class="dropdown-item">
                            <i class="fas fa-building"></i> Departments
                        </a>
                        <div class="dropdown-divider"></div>
                        
                        <!-- Travel & Logistics -->
                        <a href="<?php echo URL; ?>funders/index" class="dropdown-item">
                            <i class="fas fa-hand-holding-usd"></i> Funders
                        </a>
                        <a href="<?php echo URL; ?>airline/index" class="dropdown-item">
                            <i class="fas fa-plane"></i> Airlines
                        </a>
                        <a href="<?php echo URL; ?>hotel/index" class="dropdown-item">
                            <i class="fas fa-hotel"></i> Hotels
                        </a>
                        <a href="<?php echo URL; ?>drivers/index" class="dropdown-item">
                            <i class="fas fa-users"></i> Drivers
                        </a>
                        <div class="dropdown-divider"></div>
                        
                        <!-- Locations -->
                        <a href="<?php echo URL; ?>location/countries" class="dropdown-item">
                            <i class="fas fa-globe"></i> Locations
                        </a>
                        <a href="<?php echo URL; ?>eastates" class="dropdown-item">
                            <i class="fas fa-map-marker-alt"></i> EA States
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>
        
        <!-- User Profile Section -->
        <div class="d-flex align-items-center gap-3">
            <?php if(isset($_SESSION['user_email'])): ?>
                <div style="position:relative;display:inline-block;">
                    <span id="profileTrigger" onclick="toggleProfileCard()" style="cursor:pointer;">
                        <i class="fas fa-user-circle" style="font-size:22px;color:#e600a0;"></i>
                        <b style="margin-left:6px;font-size:16px;"><?php echo htmlspecialchars($user_name); ?></b>
                    </span>
                    <div id="profileCard" style="display:none;position:absolute;top:40px;right:0;background:#fff;border:1px solid #ddd;border-radius:16px;padding:25px;width:380px;box-shadow:0px 8px 20px rgba(0,0,0,0.15);font-size:15px;line-height:1.7;z-index:999;">
                        <p><i class="fas fa-smile text-warning"></i> <strong>Name:</strong> <span id="cardName"></span></p>
                        <p><i class="fas fa-envelope text-danger"></i> <strong>Email:</strong> <span id="cardEmail"></span></p>
                        <p><i class="fas fa-tag text-info"></i> <strong>Role:</strong> <span id="cardRole"></span></p>
                        <div style="text-align:center;margin-top:20px;">
                            <a href="<?php echo URL; ?>login/logout" class="bt-logout">LOGOUT</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
function toggleProfileCard() {
    const card = document.getElementById('profileCard');
    card.style.display = card.style.display === 'none' ? 'block' : 'none';
    if (card.style.display === 'block') {
        fetch('<?php echo URL; ?>users/getUserProfile')
            .then(r => r.json())
            .then(d => {
                document.getElementById('cardName').innerText = d.name || '<?php echo $user_name; ?>';
                document.getElementById('cardEmail').innerText = d.email || '<?php echo $user_email; ?>';
                document.getElementById('cardRole').innerText = d.role || '<?php echo $role; ?>';
            })
            .catch(err => console.error('Profile error:', err));
    }
}

document.addEventListener('click', function(e) {
    const card = document.getElementById('profileCard');
    const trigger = document.getElementById('profileTrigger');
    if (card && trigger && !card.contains(e.target) && !trigger.contains(e.target)) {
        card.style.display = 'none';
    }
});
</script>

</body>
</html>