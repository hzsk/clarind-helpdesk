<?php
session_start();
require("/etc/clarin-helpdesk.conf"); // THis is not committed to github!
$debugging = true;
function req_param(&$param, $default){
    return isset($param) ? $param : $default;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>CLARIN-D helpdesk form</title>
    <meta charset="UTF-8">
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <link rel="stylesheet" href="mail_css.css">
    <link rel="stylesheet" href="bootstrap.min.css">
</head>
<body>
<?php
if (!isset($recaptcha_public, $recaptcha_secret,
    $ticketing_user, $ticketing_password,
    $default_queue, $default_owner, $default_responsible, $default_lang))
{
    echo("<div><strong>Helpdesk form has not been set up!</strong></div>");
    exit(1);
}

/* request parameters sent via GET (lang QueuID as well es OwnerID and
 * ResponsibleID)
 * If you wonder about ID's just click on the respective users and queues in the
 * OTRS backend
 */
$QueueID = $default_queue; // extra-careful
if (isset($_REQUEST['queue']) && !isset($_REQUEST['QueueID'])) {
    // I bet I could pull this from OTRS too... the previous impl. didn't work
    // so I just hard-coded stuff
    $qmap = array(
        "aggregator" => "41",
        "junk" => "3",
        "hzsk" => "5",
        "hzsk::corpora" => "9",
        "bas" => "19",
        "bbaw" => "20",
        "etue" => "21",
        "ids" => "18",
        "ims" => "22",
        "uds" => "24",
        "vlo" => "34"
    );
    $qq = strtolower($_REQUEST['queue']);
    if (array_key_exists($qq, $qmap)) {
        $QueueID = $qmap[$qq];
    }
    elseif (is_numeric($_REQUEST['queue'])) {
        if ($debugging) {
            print("<div>Queue $_REQUEST[queue] does not exist, maybe a bad link" .
                " meant to point <a href='" .
                $_SERVER['SCRIPT_SELF'] . "?QueueID=" .
                $_REQUEST['queue'] . ">here</a>instead?</div>");
        } else {
            file_put_contents("/var/log/helpdesk.log","numeric Q:" .
                print_r($_REQUEST, true) . print_r($_SERVER, true),
                FILE_APPEND);
        }
    }
    else {
        if ($debugging) {
            print("<div>Queue $_REQUEST[queue] does not exist.</div>");
        } else {
            file_put_contents("/var/log/helpdesk.log", "no Q:" .
                print_r($_REQUEST, true) . print_r($_SERVER, true),
                FILE_APPEND);
        }
    }
}
else {
    $QueueID = req_param($_REQUEST['QueueID'], $default_queue);
}
$lang = req_param($_REQUEST['lang'], $default_lang);
$OwnerID = req_param($_REQUEST['OwnerID'],  $default_owner);
$ResponsibleID = req_param($_REQUEST['ResponsibleID'], $default_responsible);

if ($debugging)
{
    echo("<div><strong>This page does not send messages to CLARIN-D helpdesk" .
        " (outside spam queue) " .
        " It is only used for testing purposes.</strong></div>");
}
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
    "CLARIN-D weitergeleitet. </div><div>&nbsp;</div>" .
    "<div>Sie erhalten sofort eine Bestätigung Ihrer Anfrage per E-Mail. " .
    "Sollten Sie keine Bestätigung erhalten, schreiben Sie bitte eine E-Mail " .
    "an <a href='mailto:support@clarin-d.de'>support@clarin-d.de</a>.",
    "en" => "<div>Please do not hesitate to contact the " .
    "<b>CLARIN-D Helpdesk</b> with any questions. </div>" .
    "<div>&nbsp;</div><div>Your inquiry will immediately be forwarded to a " .
    "CLARIN-D expert. </div><div>&nbsp;</div>" .
    "<div>You will receive a confirmation email immediately " .
    "after submitting your inquiry. In case you do not receive a " .
    "confirmation, please send us an email at " .
    "<a href='mailto:support@clarin-d.de'>support@clarin-d.de</a>."
);
$logo = "/images/clarin.png";
if ($QueueID == 40) {
    $text['de'] =
        "<div>Bitte zögern Sie nicht, sich bei allen Fragen direkt an den CATMA"
        . "Helpdesk zu wenden.</div>" .
        "<div>Ihre Anfrage wird dann umgehend an eine/-n Ansprechparter/-in " .
        "bei CATMA weitergeleitet.</div>" .
        "<div>Sie erhalten sofort eine Bestätigung Ihrer Anfrage per E-Mail. " .
        "Sollten Sie keine Bestätigung erhalten, schreiben Sie bitte " .
        "eine E-Mail an " .
        "<a href='mailto:support@catma.de'>support@catma.de</a>.";
    $text['en'] =
        "<div>Please do not hesitate to contact the CATMA Helpdesk with " .
        "any questions.</div>" .
        "<div>Your inquiry will immediately be forwarded to " .
        "a CATMA expert.</div>" .
        "<div>You will receive a confirmation email immediately " .
        "after submitting your inquiry. " .
        "In case you do not receive a confirmation, please send us an " .
        "email at <a href='mailto:support@catma.de'>support@catma.de.</a>" .
        "</div>";
    unset($logo);
}
$error = array (
    "de" => "<div>Ein Fehler bei der &Uuml;bermittlung des Formulars ist " .
    "aufgetreten. Bitte kontaktieren Sie uns mit Ihrem Anliegen direkt via " .
    "E-Mail <a href='mailto:support@clarin-d.de'>support@clarin-d.de</a></div>",
    "en" => "<div>Ein error occured. Please contact us directly via email to " .
    "<a href='mailto:support@clarin-d.de'>support@clarin-d.de</a></div>" .
    "<div>&nbsp;</div>"
);
$captchafail = array (
    "de" => "<div class='warning'>Leider wurde das Captcha nicht " .
        "korrekt beantwortet.</div>",
    "en" => "<div class='warning'>Unfortunately the captcha " .
        "was not answered correctly.</div>"
    );
if (isset($logo)) {
    printf("<img src='%s' alt='[logo]'/>", $logo);
}
// the incredible old-fashined form library, see
// http://www.imavex.com/pfbc3.x-php5/
use PFBC\Form;
use PFBC\Element;

require("PFBC/Form.php");
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
        if ($debugging) {
            $QueueID = "3";
            $ResponsibleID = "12";
            $OwnerID = "12";
            echo("<div>Would be creating a ticket here:</div>");
            echo("<pre>");
            var_dump(
                array('CustomerUserLogin' => 'FakeLogin',
                    'Password' => "this is not the real password!",
                    'Ticket' => array(
                        'Title' => $_POST['sbj'],
                        'QueueID' => $QueueID,
                        'TypeID' => 1,
                        'StateID' => 1,
                        'PriorityID' => 3,
                        'OwnerID' => $OwnerID,
                        'ResponsibleID' => $ResponsibleID,
                        'CustomerUser' => 'FakeUser'
                    ),
                    'Article' => array(
                        'From' => $_POST['name'].'<'.$_POST['mail'].'>',
                        'Subject' => $_POST['sbj'],
                        'Body' => $_POST['msg'],
                        'ContentType' => 'text/plain; charset=ISO-8859-1'
                    ),
                )
            );
            echo("</pre>");
        }
        // initiate a new SOAP Client based on
        $WSDL = 'GenericTicketConnector.wsdl';
        $SOAPCl = new SoapClient($WSDL);
        // Create Ticket
        $create = $SOAPCl->TicketCreate(
                array('CustomerUserLogin' => $ticketing_user,
                    'Password' => $ticketing_password,
                    'Ticket' => array(
                        'Title' => $_POST['sbj'],
                        'QueueID' => $QueueID,
                        'TypeID' => 1,
                        'StateID' => 1,
                        'PriorityID' => 3,
                        'OwnerID' => $OwnerID,
                        'ResponsibleID' => $ResponsibleID,
                        'CustomerUser' => $ticketing_user
                    ),
                    'Article' => array(
                        'From' => $_POST['name'].'<'.$_POST['mail'].'>',
                        'Subject' => $_POST['sbj'],
                        'Body' => $_POST['msg'],
                        'ContentType' => 'text/plain; charset=ISO-8859-1'
                    ),
                )
            );
        if ($debugging) {
            echo("<pre>");
            var_dump($create);
            echo( "</pre>");
        }
        if (!$create->TicketID || $create->Error) {
            echo("<div>Ticket creation error! " .
                "Please contact helpdesk directly via email at " .
                "<a href='mailto:support@clarin-d.de'>support@clarin-d.de</a>:"
                . "</div>\n<pre>");
            echo("To: Clarin-D Helpdesk &lt;support@clarin-d.d&gt;\n");
            echo("Subject: " . $_POST['sbj'] . "\n");
            echo("\n\n" . $_POST['msg'] . "\n");
            echo("</pre>");
        }
        /* Ok ... humm ... what did I do here?
         * Ah! OTRS needs the user for who the ticket is created to login. of course
         * people who send the form are not known For that reason we use the user
         * Webform (see above) changing the parameters of the ticket by the way does
         * not seem to work
         */
        else {
            $modify = $SOAPCl->TicketUpdate(
                    array('CustomerUserLogin' => $ticketing_user,
                        'Password' => $ticketing_password,
                        'TicketID' => $create->TicketID,
                        'Ticket' => array(
                            'CustomerUser' => $_POST['name'],
                            'CustomerID' => $_POST['mail']
                        ),
                       )
                    );
            if ($debugging) {
                echo("<pre>");
                var_dump($modify);
                echo("</pre>");
            }
            if ($modify->Error) {
                if ($debugging) {
                    echo("<div>Ticket updating error! " .
                        "This just means it wasn’t reassigned to a new customer"
                        . ". Nothing we can fix yet.</div>");
                } else {
                    // Success!
                    echo $lable[$lang]["success"];
                }
            } else {
                echo $lable[$lang]["success"];
            }
        }
    }
      // else catcha error
    else {
        echo $captchafail[$lang];
        $form = new Form("login");
        // store the parameters passed above in hidden fields
        $form->addElement(new Element\Hidden("lang", $lang));
        $form->addElement(new Element\Hidden("QueueID", $QueueID));
        $form->addElement(new Element\Hidden("OwnerID", $OwnerID));
        $form->addElement(new Element\Hidden("ResponsibleID", $ResponsibleID));
        // and here comes all the other fieldwork
        $form->addElement(new Element\Textbox($lable[$lang]["name"].":", "name",
            array(
                "required" => 1,
                "value" => $_POST['name']
        )));
        $form->addElement(new Element\Email($lable[$lang]["mail"].":", "mail",
            array(
                "required" => 1,
                "value" => $_POST['mail']
        )));
        $form->addElement(new Element\Textbox($lable[$lang]["sbj"].":", "sbj",
            array(
                "required" => 1,
                "value" => $_POST['sbj']
        )));
        $form->addElement(new Element\Textarea($lable[$lang]["msg"].":", "msg",
            array(
                "required" => 1,
                "value" => $_POST['msg']
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
}
// should never reach this but just in case
else {
    echo $error[$lang];
}
?>
</body>
</html>
