<html>
<head>
    <title>Support</title>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <link rel="stylesheet" href="mail_css.css">
    <link rel="stylesheet" href="bootstrap.min.css">
</head>
<body>

<?php
session_start();
require(".htsecretpasswords.inc"); // THis is not committed to github!!
/* request parameters sent via GET (lang QueuID as well es OwnerID and
 * ResponsibleID)
 * If you wonder about ID's just click on the respective users and queues in the
 * OTRS backend
 */
function req_param(&$param, $default){
    return isset($param) ? $param : $default;
}
$lang = req_param($_REQUEST['lang'], 'de');
$QueueID = req_param($_REQUEST['QueueID'], '1');
$OwnerID = req_param($_REQUEST['OwnerID'], '1');
$ResponsibleID = req_param($_REQUEST['ResponsibleID'], '1');
// lables
$lable = array (
"de" => array ("name" => "Vor- und Zuname",
    "mail" => "E-Mail",
    "sbj" => "Betreff",
    "msg" => "Ihre Nachricht",
    "sndbt" => "Senden",
    "cncbt" => "Abbrechen",
    "success" => "<div>Vielen Dank, wir haben Ihre Nachricht erhalten und "
    . "melden uns schnellstm&ouml;glich bei Ihnen. </div>" .
    "<div>&nbsp;</div><div>Sie erhalten umgehend eine Eingangsbest&auml;tigung "
    . "&uuml;ber die von Ihnen angegebene Adresse</div>"),
"en" => array ("name" => "Full name",
    "mail" => "Email",
    "sbj" => "Subject",
    "msg" => "Your message",
    "sndbt" => "Send",
    "cncbt" => "Cancel",
    "success" => "<div>Thank you, We received your message and we will " .
    "get in touch with you soon. </div><div>You will soon receive an " .
    "email confirmation</div>"),
);
// text to be shown at the beginning
$text = array (
    "de" => "<div>Bitte z&ouml;gern Sie nicht, sich bei allen Fragen direkt an "
    . "den <b>CLARIN-D Helpdesk</b> zu wenden. </div><div>&nbsp;</div>" .
    "<div>Ihre Anfrage wird dann umgehend an eine/-n Ansprechparter/-in in " .
    "CLARIN-D Weitergeleitet. </div><div>&nbsp;</div>",
    "en" => "<div>Please do not hesitate to contact the " .
    "<b>CLARIN-D Helpdesk</b> with any questions. </div>" .
    "<div>&nbsp;</div><div>Your inquiry will immediately be forwarded to a "
    "CLARIN-D expert. </div><div>&nbsp;</div>"
);
// error message that for some reason does not appear
$error = array (
    "de" => "<div>Ein Fehler bei der &Uuml;bermittlung des Formulars ist " .
    "aufgetreten. Bitte kontaktieren Sie uns mit Ihrem Anliegen direkt via " .
    "E-Mail <a href='mailto:support@clarin-d.de'>support@clarin-d.de</a></div>",
    "en" => "<div>Ein error occured. Please contact us directly via email to " .
    "<a href='mailto:support@clarin-d.de'>support@clarin-d.de</a></div>" .
    "<div>&nbsp;</div>"
);

// the incredible old-fashined form library, see
// http://www.imavex.com/pfbc3.x-php5/
use PFBC\Form;
use PFBC\Element;
include("PFBC/Form.php");
// if captcha is not sent
if(!isset($_POST['g-recaptcha-response'])){
    echo $text[$lang];
    $form = new Form("login");
    // store the parameters passed above in hidden fields
    $form->addElement(new Element\Hidden("lang", $lang));
    $form->addElement(new Element\Hidden("QueueID", $QueueID));
    $form->addElement(new Element\Hidden("OwnerID", $OwnerID));
    $form->addElement(new Element\Hidden("ResponsibleID", $ResponsibleID));
    // and here comes all the other fieldwork
    $form->addElement(new Element\Textbox($lable[$lang]["name"].":", "name",
        array(
        "required" => 1
    )));
    $form->addElement(new Element\Email($lable[$lang]["mail"].":", "mail",
        array(
        "required" => 1
    )));
    $form->addElement(new Element\Textbox($lable[$lang]["sbj"].":", "sbj",
        array(
        "required" => 1
    )));
    $form->addElement(new Element\Textarea($lable[$lang]["msg"].":", "msg",
        array(
        "required" => 1
    )));
    // "I am not a robot"
    $form->addElement(new Element\HTML('<br /><div class="g-recaptcha" ' .
        'data-sitekey="' . $recaptcha_public . '"></div>' .
        '<br />'));
    // buttonss
    $form->addElement(new Element\Button($lable[$lang]["sndbt"]));
    $form->addElement(new Element\Button($lable[$lang]["cncbt"], "button",
        array(
        "onclick" => "history.go(-1);"
    )));
    $form->render();
}
// and if captcha was sent ...
elseif(isset($_POST['g-recaptcha-response'])){
    $captcha=$_POST['g-recaptcha-response'];
    // XXX: is $_SERVER[REMOTE_ADDR] always good??
    $response=file_get_contents("https://www.google.com/recaptcha/api/" .
        "siteverify?secret=" .
        $recaptcha_secret . "&response=" . $captcha . "&remoteip=" .
        $_SERVER['REMOTE_ADDR']);
    // and proved successful ...
    if(json_decode($response, true)['success'] == 1) {
        // initiate a new SOAP Client based on
        $WSDL = 'GenericTicketConnector.wsdl';
        $SOAPCl = new SoapClient($WSDL);
        // Create Ticket
        $create = $SOAPCl->TicketCreate(
                array('CustomerUserLogin' => 'Webform',
                    'Password' => $ticketing_password,
                    'Ticket' => array(
                        'Title' => $_POST['sbj'],
                        'QueueID' => $QueueID,
                        'TypeID' => 1,
                        'StateID' => 1,
                        'PriorityID' => 3,
                        'OwnerID' => $OwnerID,
                        'ResponsibleID' => $ResponsibleID,
                        'CustomerUser' => 'Webform'
                    ),
                    'Article' => array(
                        'From' => $_POST['name'].'<'.$_POST['mail'].'>',
                        'Subject' => $_POST['sbj'],
                        'Body' => $_POST['msg'],
                        'ContentType' => 'text/plain; charset=ISO-8859-1'
                    ),
                )
                );
        /* Ok ... humm ... what did I do here?
         * Ah! OTRS needs the user for who the ticket is created to login. of course
         * people who send the form are not known For that reason we use the user
         * Webform (see above) changing the parameters of the ticket by the way does
         * not seem to work
         */
        $modify = $SOAPCl->TicketUpdate(
                    array('CustomerUserLogin' => 'Webform',
                        'Password' => $ticketing_password,
                        'TicektID' => $create->TicketID,
                        'Ticket' => array(
                            'CustomerUser' => $_POST['name'],
                            'CustomerID' => $_POST['mail']
                        ),
                       )
                    );

        // Success!
        echo $lable[$lang]["success"];
    }
      // else catcha error
    else {
        echo $error[$lang];
    }
}
// should never reach this but just in case
else {
    echo $error[$lang];
}
?>
</body>
</html>
