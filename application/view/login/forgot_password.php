<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>
  <link href="<?php echo URL; ?>css/login.css" rel="stylesheet">
  <script>
    function validatePasswords() {
        var newPassword1 = document.getElementById('newPassword1').value;
        var newPassword2 = document.getElementById('newPassword2').value;

        if (newPassword1 !== newPassword2) {
            alert("Passwords do not match. Please enter the same password.");
            return false;
        }
        return true;
    }
</script>

</head>
<body>
 <?php  if (!empty($invalid_credentials)){echo $invalid_credentials; }?>
 <?php  if (!empty($successful_login)){echo $successful_login; }?>
 <?php  if (!empty($null_result)){echo $null_result;}?>
 <div class="container">
   <div class="split left">
     <div class="svg-container">
       <img src="<?php echo URL; ?>img/Primary-Logo_Reversed.png" alt="logo" style="width: 50%; height: 20%;">
      </div>
    </div>
      <div class="split right">
        <form class="login-form" method="POST" action="<?php echo URL; ?>login/submit_password_reset">
          <h2>Reset Password</h2>
          <div class="form-group">
            <label for="password">Email Address:</label>
            <input type="email" id="inputEmailAddress" name="inputEmailAddress" required>
          </div>
          <div class="form-group">
            <label for="password">New Password:</label>
            <input type="password" id="newPassword1" name="newPassword1" required>
          </div>
          <div class="form-group">
            <label for="password">Repeat New Password:</label>
            <input type="password" id="newPassword2" name="newPassword2" required  onblur="validatePasswords()">
          </div>
          <button id="submit_password_reset" type="submit">Reset Password</button>
        </form>
    </div>
  </div>
</body>
</html>