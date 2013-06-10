<?php
    include_once "fbaccess.php";
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <title>Nostalgia Room</title>    
		<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
		
    </head>
<body>

<div id="nostalgia-logo"><img src="images/nostalgialogo_new.png" /></div>	

<?php if(!$user) { ?>

	<div id="f-connect-button"><a href="<?=$loginUrl?>"><img src="images/f-connect.png"></a></div>
<p>Login to connect your facebook account.</p>



<?php } else { ?>
	
<P>	
<img src="images/ajax_loader_blue.gif?rand=<?=rand(1,1000);?>" alt=""/>
</p>

<p>Login  Succcessful !!!! </p>

<script>
window.onload = function(event) {
    event.stopPropagation(true);
    window.location.href="downloadImages.php";
};
</script>
<?php } ?>

<div id="fluid-logo"><img src="images/fluidlogo.png"></div>
<!-- <div id="info-eco-logo"><img src="images/InformationEcologyLogo.png"></div> -->
</body>
</html>