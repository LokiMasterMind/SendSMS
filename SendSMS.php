<!DOCTYPE html>
<html>
<head>
	<title>SMS Box </title>
</head>
<body onload='ShowDivInCenter();' onresize='ShowDivInCenter();'>

<?php session_start();
echo "<center>";
	echo "<span>";	 
		if(isset($_SESSION['message'])) {
					echo $_SESSION['message'];
					unset($_SESSION['message']);
		} 
	echo "</span>";
echo "</center>";
$location="http://localhost/practice/sendsms.php";
if($_SERVER['REQUEST_METHOD'] == "POST") {
	if($_POST['txtTo']!="" && $_POST['txtFrom']!="" && $_POST['txtMessage']!="" && $_POST['txtPassword']!="" ){			
		$to = $_POST['txtTo'];
		$message  = $_POST['txtMessage'];
		$from	  = $_POST['txtFrom'];
		$password	  = $_POST['txtPassword'];
		if(sendWay2SMS($from,$password,$to,$message)){				
			$_SESSION['message']="SMS sent";
			header("Location: {$location}");
		}
		else{			
			$_SESSION['message']="SMS sending failed";
			header("Location: {$location}");
		}
	}else{
			$_SESSION['message']= "Error: Data Insufficient";
			header("Location: {$location}");
	}
}
function sendWay2SMS($uid, $pwd, $phone, $msg)
{
  $curl = curl_init();
  $timeout = 30;
  $result = array();
  $uid = urlencode($uid);
  $pwd = urlencode($pwd);
  // Go where the server takes you :P
  curl_setopt($curl, CURLOPT_URL, "http://way2sms.com");
  curl_setopt($curl, CURLOPT_HEADER, true);
  curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
  $a = curl_exec($curl);
  if(preg_match('#Location: (.*)#', $a, $r))
    $way2sms = trim($r[1]);
  // Setup for login
  curl_setopt($curl, CURLOPT_URL, $way2sms."Login1.action");
  curl_setopt($curl, CURLOPT_POST, 1);
  curl_setopt($curl, CURLOPT_POSTFIELDS, "username=".$uid."&password=".$pwd."&button=Login");
  curl_setopt($curl, CURLOPT_COOKIESESSION, 1);
  curl_setopt($curl, CURLOPT_COOKIEFILE, "cookie_way2sms");
  curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($curl, CURLOPT_MAXREDIRS, 20);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.5) Gecko/2008120122 Firefox/3.0.5");
  curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
  curl_setopt($curl, CURLOPT_REFERER, $way2sms);
  $text = curl_exec($curl);
  // Check if any error occured
  if (curl_errno($curl))
    return "access error : ". curl_error($curl);
  // Check for proper login
  $pos = stripos(curl_getinfo($curl, CURLINFO_EFFECTIVE_URL), "ebrdg.action");
  if ($pos === "FALSE" || $pos == 0 || $pos == "")
    return "invalid login";
  // Check the message
  if (trim($msg) == "" || strlen($msg) == 0)
    return "invalid message";
  // Take only the first 140 characters of the message
  $msg = urlencode(substr($msg, 0, 140));
  // Store the numbers from the string to an array
  $pharr = explode(",", $phone);
  // Set the home page from where we can send message
  $refurl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
  $newurl = str_replace("ebrdg.action?id=", "main.action?section=s&Token=", $refurl);
  curl_setopt($curl, CURLOPT_URL, $newurl);
  // Extract the token from the URL
  $jstoken = substr($newurl, 50, -41);
  //Go to the homepage
  $text = curl_exec($curl);
  // Send SMS to each number
  foreach ($pharr as $p)
  {
    // Check the mobile number
    if (strlen($p) != 10 || !is_numeric($p) || strpos($p, ".") != false)
    {
      $result[] = array('phone' => $p, 'msg' => urldecode($msg), 'result' => "invalid number");
      continue;
    }
    $p = urlencode($p);
    // Setup to send SMS
    curl_setopt($curl, CURLOPT_URL, $way2sms.'smstoss.action');
    curl_setopt($curl, CURLOPT_REFERER, curl_getinfo($curl, CURLINFO_EFFECTIVE_URL));
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "ssaction=ss&Token=".$jstoken."&mobile=".$p."&message=".$msg."&button=Login");
    $contents = curl_exec($curl);
    //Check Message Status
    $pos = strpos($contents, 'Message has been submitted successfully');
    $res = ($pos !== false) ? true : false;
    $result[] = array('phone' => $p, 'msg' => urldecode($msg), 'result' => $res);
  }
  // Logout
  curl_setopt($curl, CURLOPT_URL, $way2sms."LogOut");
  curl_setopt($curl, CURLOPT_REFERER, $refurl);
  $text = curl_exec($curl);
  curl_close($curl);
  return $result;
}

?>
<div id="divSMS" >
	<form name="myform" action="" method="post">
	<table align="center" id="tableSMS">
		<tr align="center" >
		<td colspan="2"><b>Please Enter Details To Send SMS</b></td>
		</tr>
		<tr>
		<td>To</td>
		<td><input style="width: 300px;" type="text" name="txtTo" /></td>
		</tr>
		<tr>
		<td>From</td>
		<td><input style="width: 300px;" type="text" name="txtFrom" /></td>
		</tr>
        <tr>
		<td>Password</td>
		<td><input style="width: 300px;" type="text" name="txtPassword" /></td>
		</tr>	 
		<tr>
		<td>Message</td>
		<td><textarea maxlength="140" style="width: 300px; height: 150px;" type="text" name="txtMessage" ></textarea></td>
		</tr>	 
		<tr>
		<td colspan="2" align="right"><input type="submit" name="submit" value="Submit" /></td>
		</tr>	 	
	</table>	
	</form> 
</div>	
</body>
<script type="text/javascript">	
function ShowDivInCenter()
{
    try
    {
        divWidth = 400;
        divHeight = 400;
        divId = 'divSMS'; // id of the div that you want to show in center

        // Get the x and y coordinates of the center in output browser's window
        var centerX, centerY;
        if (self.innerHeight)
        {
            centerX = self.innerWidth;
            centerY = self.innerHeight;
        }
        else if (document.documentElement && document.documentElement.clientHeight)
        {
            centerX = document.documentElement.clientWidth;
            centerY = document.documentElement.clientHeight;
        }
        else if (document.body)
        {
            centerX = document.body.clientWidth;
            centerY = document.body.clientHeight;
        }
 
        var offsetLeft = (centerX - divWidth) / 2;
        var offsetTop = (centerY - divHeight) / 2;
 
        // The initial width and height of the div can be set in the
        // style sheet with display:none; divid is passed as an argument to // the function
        var ojbDiv = document.getElementById(divId);
         
        ojbDiv.style.position = 'absolute';
        ojbDiv.style.top = offsetTop + 'px';
        ojbDiv.style.left = offsetLeft + 'px';
        ojbDiv.style.display = "block";
    }
    catch (e) {}
}

</script>
</html> 
