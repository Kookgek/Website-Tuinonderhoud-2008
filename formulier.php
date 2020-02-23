<?php
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

session_start();

// Config Gedeelte
$cfg['url'] = "contact.php";// Site waarnaar je terug gaat als je een bericht hebt achtergelaten
$cfg['naam'] = "Tuinonderhoudbedrijf Gremmé";                // Webmaster naam
$cfg['email'] = "mailadres";        // Webmaster E-mail
$cfg['spam'] = 0;                        // Anti Spam Tijd in Minuten ( Voer "0" om de Spam Beveiliging uit te zetten )
$cfg['text'] = TRUE;                    // Bij Fout Text Rood maken ( TRUE voor aan, FALSE voor uit )
$cfg['input'] = TRUE;                    // Bij Fout Border om Vakje Rood maken ( TRUE voor aan, FALSE voor uit )
$cfg['HTML'] = TRUE;                    // Een HTML email ( TRUE voor aan, FALSE voor uit )
$cfg['CAPTCHA'] = FALSE;                    // CAPTCHA ( TRUE voor aan, FALSE voor uit )

// Hieronder niks meer veranderen
// E-mail Checker / Validator
function checkmail($email)
{
    if(preg_match("(^[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,4}$)", $email))
    {
        return TRUE;
    }
    return FALSE;
}

$formulier = TRUE;

if(!isset($_COOKIE['formulier']))
{
    if(isset($_POST['wis']) && ($_SERVER['REQUEST_METHOD'] == "POST"))
    {
        foreach($_POST as $key => $value)
        {
            unset($value);
        }
        header("Location: ".$_SERVER['PHP_SELF']."");
    }
        
    if(isset($_POST['verzenden']) && ($_SERVER['REQUEST_METHOD'] == "POST"))
    {
        $aFout = array();
        
		$aanhef = $_POST['aanhef'];
        $naam = trim($_POST['naam']);
        $email = trim($_POST['email']);
        $telefoon = trim($_POST['telefoon']); 
		$bericht = trim($_POST['bericht']);
        
        if($cfg['CAPTCHA'])
        {
            $code = $_POST['code'];
        }
     
		if(empty($naam) || (strlen($naam) < 3) || preg_match("[<>]", $naam) )
        {
            $aFout[] = "Er is geen naam ingevuld.";
            unset($naam);
            $fout['text']['naam'] = TRUE;
            $fout['input']['naam'] = TRUE;
        }
        if(empty($email))
        {
            $aFout[] = "Er is geen e-mail adres ingevuld.";
            unset($email);
            $fout['text']['email'] = TRUE;
            $fout['input']['email'] = TRUE;
        }
        elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) 
        {
            $aFout[] = "Er is geen correct e-mail adres ingevuld.";
            unset($email);
            $fout['text']['email'] = TRUE;
            $fout['input']['email'] = TRUE;
        }
        if(!preg_match("/^[0-9]{10,11}$/",$telefoon))
        {
            $aFout[] = "Voer een 10 cijferige telefoon- of mobiele nummer in alstublieft.";
            unset($telefoon);
            $fout['text']['telefoon'] = TRUE;
            $fout['input']['telefoon'] = TRUE;
        }
		if(empty($bericht) || (strlen($bericht) < 3) || preg_match("[<>]", $bericht) )
        {
            $aFout[] = "Er is geen bericht ingevuld.";
            unset($bericht);
            $fout['text']['bericht'] = TRUE;
            $fout['input']['bericht'] = TRUE;
        }
        if($cfg['CAPTCHA'])
        {
            if(isset($_SESSION['captcha_code']) && strtoupper($code) != $_SESSION['captcha_code'])
            {
                $aFout[] = "Er is geen correcte code ingevuld.";
                $fout['text']['code'] = TRUE;
                $fout['input']['code'] = TRUE;
            }
        }
        if(!$cfg['text'])
        {
            unset($fout['text']);
        }
        if(!$cfg['input'])
        {
            unset($fout['input']);
        }
        if(!empty( $aFout ))
        {
            $errors = '
            <div id="errors">
            <ul>';
            foreach($aFout as $sFout)

            {
                $errors .= "    <li>".$sFout."</li>\n";
            }
            $errors .= "</ul>
            </div>";
        }
        else
        {
            $formulier = FALSE;
            
            
            if($cfg['HTML'])
            {
                // Headers
                $headers = "From: \"Contactformulier Tuinonderhoudsbedrijf Gremme \" <".$cfg['email'].">\r\n"; 
                $headers .= "Reply-To: \"".$naam."\" <".$email.">\n";
                $headers .= "Return-Path: Mail-Error <".$cfg['email'].">\n";
                $headers .= "MIME-Version: 1.0\n";
                $headers .= "Content-Transfer-Encoding: 8bit\n";
                $headers .= "Content-type: text/html; charset=iso-8859-1\n";
                
                
                $bericht = '
                <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
                <html>
                <head>
                </head>
            
                <body>
                <br />
                <b>Aanhef:</b>&nbsp&nbsp&nbsp '.$aanhef.'<br />
				<b>Naam:</b>&nbsp&nbsp&nbsp&nbsp&nbsp '.$naam.'<br />
                <b>Email:</b>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp <a href=\"mailto:'.$email.'\">'.$email.'</a><br />
                <b>Telefoon:</b> '.$telefoon.'<br />
				<br />
				<b>Bericht:</b><br />
                '.$bericht.'
                <br />
                <br />
                <br />
                --------------------------------------------------------------------------<br />
                <b>Datum:</b> '.date("d-m-Y @ H:i:s").'<br />
                <b>IP:</b> <a href=\"http://sunny.nic.com/cgi-bin/whois?domain='.$_SERVER['REMOTE_ADDR'].'\">'.$_SERVER['REMOTE_ADDR'].'</a><br />
                </body>
                </html>';
            }
            else 
            {
                $bericht_wrap = wordwrap ($bericht, 40, "\n", 1);
                // Headers
                $headers = "From: \"Website Contact Formulier\" <".$cfg['email'].">\n"; 
                $headers .= "MIME-Version: 1.0\n";
                $headers .= "Content-type: text/plain; charset='iso-8859-1'\n"; 
            
                // Bericht
                //$message .= "Aanhef: ""        \n";
				$message .= "Naam: ".$naam."        \n";
                $message .= "Telefoon: ".$telefoon."        \n";
				$message .= "E-mail: ".$email."     \n";
                $message .= "Bericht:\n".$bericht_wrap."     \n ";
                $message .= "               \n ";
                $message .= "Datum: ".date("d-m-Y H:i:s")." \n";
                $message .= "------------------------------------------------------- \n ";
                $message .= "IP: ".$_SERVER['REMOTE_ADDR']."                    \n ";
                            
            }
        
            if(mail($cfg['email'], "[Contact] ".$onderwerp, $bericht, $headers)) 
            {
                if(isset($_POST['stuurkopie']))
                {
                    $headers .= "From: \"Gremm Contact Formulier\" <".$email.">\r\n"; 
                    $headers .= "Reply-To: \"".$naam."\" <".$email.">\n";
                    $headers .= "Return-Path: Mail-Error <".$email.">\n";
                    $headers .= "MIME-Version: 1.0\n";
                    $headers .= "Content-Transfer-Encoding: 8bit\n";
                    $headers .= "Content-type: text/html; charset=iso-8859-1\n";
                    
                    mail($email, "[Contact] ".$onderwerp, $bericht, $headers);
                
                }
            				
				unset($naam, $email, $onderwerp, $bericht);
                setcookie("formulier", 1, time() + ( $cfg['spam'] * 60 ) );
        
                echo "
                <p>
                Uw bericht is succesvol verzonden! Wij nemen zo spoedig mogelijk contact met u op.<br />
                <br />
                Met vriendelijke groeten,<br />
                <b>".$cfg['naam']."</b>
                </p>
                ";    
            }
            else
            {
                echo "Er is een fout opgetreden bij het verzenden van de email";
            }
            //header("refresh:10;url=".$cfg['url']."");
        }
    }
    if($formulier)
    {
?>
<link href="CSS/website.css" rel="stylesheet" type="text/css" />



   <div id="Content">
		 <?php
   		 if(isset($errors)) {
       	 echo $errors;
   		 }
   		 ?>
      	<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>">
        <p>Wilt u meer informatie of een offerte?<br> 
        Neem dan vrijblijvend contact met ons op via het onderstaande<br>
        formulier.</p>
       
          <label>Aanhef:</label>  
          <select name="aanhef" id="aanhef"> <option>Dhr.</option> <option>Mevr.</option> </select> <br />
                    
          <label <?php if(isset($fout['text']['naam'])) { echo 'class="fout"'; } ?>>Naam: *</label>
          <input type="text" id="naam" name="naam" maxlength="30" <?php if(isset($fout['input']['naam'])) { echo 'class="fout"'; } ?> value="<?php if (!empty($naam)) { echo stripslashes($naam); } ?>" />  
          <br />
          
          <label <?php if(isset($fout['text']['email'])) { echo 'class="fout"'; } ?>>E-mail: *</label>
          <input type="text" id="email" name="email" maxlength="40" <?php if(isset($fout['input']['email'])) { echo 'class="fout"'; } ?> value="<?php if (!empty($email)) { echo stripslashes($email); } ?>" />  <br />
          
          <label <?php if(isset($fout['text']['telefoon'])) { echo 'class="fout"'; } ?>>Telefoon: *</label>
          <input type="text" id="telefoon" name="telefoon" maxlength="11" <?php if(isset($fout['input']['telefoon'])) { echo 'class="fout'; } ?> value="<?php if (!empty($telefoon)) { echo stripslashes($telefoon); } ?>" />  <br />
          
          <label <?php if(isset($fout['text']['bericht'])) { echo 'class="fout"'; } ?>>Bericht: *</label>
          <textarea id="bericht" name="bericht" <?php if(isset($fout['input']['bericht'])) { echo 'class="fout"'; } ?> cols="39" rows="8"><?php if (!empty($bericht)) { echo stripslashes($bericht); } ?>
          </textarea> 
          <br />
          <?php
        if($cfg['CAPTCHA'])
        {
        ?>
          <label></label>
          <img src="captcha.php" alt="" /><br />
          
          <label <?php if(isset($fout['text']['code'])) { echo 'class="fout"'; } ?>>Code:</label>
          <input type="text" id="code" name="code" maxlength="4" size="4" <?php if(isset($fout['input']['code'])) { echo 'class="captcha fout"'; } ?> /><br />
          <?php 
        }
        ?>
          <label for="stuurkopie">Stuur mij een kopie</label>
          <input type="checkbox" id="stuurkopie" name="stuurkopie" value="1" /><br />
          <br>
          <label></label>
          <input type="submit" id="verzenden" name="verzenden" value="Verzenden!" />
          <input type="submit" id="wis" name="wis" value="Wis velden" /><br>
        </p>
		  </p>
        </form>
    </div>
 

 <?php
    }
}
else 
{
    echo "
    <p>
    U kunt maar eens in de ".$cfg['spam']." minuten een e-mail versturen!<br />
    U wordt nu automatisch doorgestuurd.
    </p>";
    header("refresh:3;url=".$cfg['url']."");
}
?>   
