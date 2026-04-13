<?php
session_start();
include('../db_config.php');

// Verify if a candidate ID is provided in the URL
if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM candidates WHERE id = '$id'");
    $candidate = mysqli_fetch_assoc($result);
    
    // Error handling if candidate does not exist
    if(!$candidate) {
        die("<div style='text-align:center; padding:50px;'><h2>Candidate not found!</h2><a href='portal.php'>Back to List</a></div>");
    }
} else {
    // Redirect to list if no ID is specified
    header("Location: portal.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Profile | <?php echo htmlspecialchars($candidate['name']); ?></title>
    <style>
        /* RESET & CENTERING SYSTEM */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background-color: #f1f5f9; 
            display: flex; 
            justify-content: center; /* Horizontal center */
            align-items: center;     /* Vertical center */
            min-height: 100vh;       /* Use full screen height */
            margin: 0;
        }

        .profile-card { 
            background: white; 
            width: 90%; 
            max-width: 500px; 
            padding: 40px; 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            text-align: center; /* Center all text */
        }

        /* IMAGE STYLES */
        .image-container { 
            width: 150px; 
            height: 150px; 
            margin: 0 auto 20px; 
            border-radius: 50%; 
            overflow: hidden; 
            border: 5px solid #5665e5; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .candidate-img { width: 100%; height: 100%; object-fit: cover; }

        /* TEXT STYLES */
        .candidate-name { font-size: 28px; font-weight: bold; color: #1e293b; margin-bottom: 5px; text-transform: uppercase; }
        .candidate-faculty { color: #6571ea; font-size: 16px; font-weight: 600; margin-bottom: 20px; }
        
        .manifesto-box { 
            text-align: left; 
            background: #f8fafc; 
            padding: 20px; 
            border-radius: 12px; 
            border: 1px solid #e2e8f0; 
            margin-top: 10px;
        }
        .manifesto-box h4 { margin-bottom: 10px; color: #1e293b; border-bottom: 2px solid #e67e22; display: inline-block; }
        .manifesto-text { color: #475569; line-height: 1.6; font-size: 15px; }

        .back-btn { 
            display: inline-block; 
            margin-top: 25px; 
            color: #64748b; 
            text-decoration: none; 
            font-size: 14px; 
            font-weight: 600; 
        }
        .back-btn:hover { color: #4648d2; }
    </style>
</head>
<body>

    <div class="profile-card">
        <div class="image-container">
            <img src="../assets/img/<?php echo $candidate['image']; ?>" class="candidate-img" alt="Candidate Photo">
        </div>

        <h1 class="candidate-name"><?php echo htmlspecialchars($candidate['name']); ?></h1>
        <p class="candidate-faculty"><?php echo htmlspecialchars($candidate['faculty']); ?></p>
        <p style="font-size: 13px; color: #94a3b8; margin-bottom: 20px;">Candidate ID: #<?php echo htmlspecialchars($candidate['id']); ?></p>

        <div class="manifesto-box">
            <h4>Manifesto & Vision</h4>
            <div class="manifesto-text">
                <?php echo nl2br(htmlspecialchars($candidate['manifesto'])); ?>
            </div>
        </div>

        <a href="portal.php" class="back-btn">← Back to Candidate List</a>
    </div>

</body>
</html>