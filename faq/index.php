<?php
$debugging = false;
if ($debugging) {
  error_reporting(-1);
  ini_set('display_errors', TRUE);
}

// https://stackoverflow.com/questions/24286935/soapclient-returning-response-with-empty-stdclass-objects
function soap_to_simplexml($rawResult) {
    $xml = simplexml_load_string($rawResult);
    $xml->registerXPathNamespace("soap", "http://www.w3.org/2003/05/soap-envelope");
    $result = $xml->xpath('//soap:Body');
    return $result;
}

function get_articles($catID, $SOAPCl){
    $FAQArticles = [];
    // Search in FAQ for all articles with $catID
    $SOAPArtIDs = $SOAPCl->PublicFAQSearch(['CategoryIDs'  =>  $catID]);
    // Get Items
    $rawResult = $SOAPCl->__getLastResponse();
    $result = soap_to_simplexml($rawResult);
    $FAQs = $result[0]->PublicFAQSearchResponse;
    foreach ($FAQs->ID as $faqid) {
        $FAQItem = $SOAPCl->PublicFAQGet(['ItemID' => $faqid]);
        $rawResult2 = $SOAPCl->__getLastResponse();
        $result2 = soap_to_simplexml($rawResult2);
        $FAQr = $result2[0]->PublicFAQGetResponse;
        $FAQItem = $FAQr->FAQItem;
        array_push($FAQArticles, ['Article' => ['ID' => $FAQItem -> ID,
            'Title' => $FAQItem -> Title,
            'question' => $FAQItem -> Field1,
            'answer' => $FAQItem -> Field3]]);
    }
    return $FAQArticles; 
}


?><!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>FAQ</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" />
        <style type="text/css">
            body{
                padding-top:30px;
            }
            
            .faq-cat-content{
                margin-top:25px;
            }
            
            .faq-cat-tabs li a{
                padding:15px 10px 15px 10px;
                background-color:#ffffff;
                border:1px solid #dddddd;
                color:#777777;
            }
            
            .nav-tabs li a:focus,
            .panel-heading a:focus{
                outline:none;
            }
            
            .panel-heading a,
            .panel-heading a:hover,
            .panel-heading a:focus{
                text-decoration:none;
                color:#777777;
            }
            
            .faq-cat-content .panel-heading:hover{
                background-color:#efefef;
            }
            
            .active-faq{
                border-left:5px solid #888888;
            }
            
            .panel-faq .panel-heading .panel-title span{
                font-size:13px;
                font-weight:normal;
            }</style>

        <script type="text/javascript" src="//code.jquery.com/jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>

    </head>
        
        
<?php
//Einbindung WSDL & SoapClient für Kategorien
$WSDL = 'GenericFAQConnectorSOAP.wsdl';
$SOAPCl = false;
if ($debugging) {
    $SOAPCl = new SoapClient($WSDL,
        array('cache_wsdl' => WSDL_CACHE_NONE, 'trace' => 1));
} else {
    $SOAPCl = new SoapClient($WSDL,
        array('trace' => 1));
}
$SOAPCat = $SOAPCl -> PublicCategoryList();
// FIXME: it's kind of unreal but PHP silently fails with some SOAP:
// https://stackoverflow.com/questions/24286935/soapclient-returning-response-with-empty-stdclass-objects
// and then we use our own custom XML parser instead. -_-
if ($debugging) {
  echo("SOAPCat:");
  var_dump($SOAPCat);
}
$rawResult = $SOAPCl->__getLastResponse();
if ($debugging) {
  echo(htmlentities($rawResult));
}
$result = soap_to_simplexml($rawResult);
if ($debugging) {
  echo("<pre>");
  print_r($result);
  echo("</pre>");
}
//Array Objekt mit Kategorien
$faqList = [];
// erst mal nur die Hauptkategorien (ohne ::), dabei nach dem GET-Parameter 'ID' Filtern
$categorylist = $result[0]->PublicCategoryListResponse;
foreach ($categorylist->Category as $cat) {
    // get all Articles of this Category
    if (!(preg_match('/\:\:/',$cat->Name))){
        if (isset($_GET['ID'])){
            if ($_GET['ID'] == $cat->ID){
              array_push($faqList,['Category' => 
                ['ID' => $cat->ID, 'Name' => $cat->Name],
                get_articles($cat->ID, $SOAPCl)]);
            }
        }
        else {
          array_push($faqList,['Category' => 
            ['ID' => $cat->ID,'Name' => $cat->Name],
            get_articles($cat->ID, $SOAPCl)]);
        }
    }
}
// und nun die Unterkategorien (mit ::)
foreach ($categorylist->Category as $cat) { 
    if (preg_match('/\:\:/', $cat->Name)){
        // Kategorie splitten
        $split = explode("::", $cat->Name);
        //suchen, wo die Hauptkategorie sich in $faqList befindet
        $i = 0; 
        foreach ($faqList as $searchcat){
            // wenn Hauptkategorie:
            if ($searchcat['Category']['Name'] == $split[0]) {
                //rein damit an der entsprechenden Stelle
              $faqList[$i][] = ['Category' =>
                ['ID' => $cat->ID, 'Name' => $split[1]],
                get_articles($cat -> ID, $SOAPCl)];
            }
            $i++;
        }
    }
}
        
        
?>

    <div class="container">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <!-- Nav tabs category -->
                    <ul class="nav nav-tabs faq-cat-tabs">
                        
                        <?php 
                        
                        $faq = $faqList[0];
                        // Ids der Kategorien, die wir ausgebe
                        $i=0;
                        foreach ($faq as $allcat){
                            if(!empty($allcat['Category']['Name'])){
                                echo '<li';
                                //on firt run only
                                if($i==0){echo ' class="active"';}
                                echo '><a href="#faq-cat-'.$allcat['Category']['ID'].'" data-toggle="tab">'.$allcat['Category']['Name'].'</a></li>';
                                $i++;

                                }
                        }
                                                                                
                        ?>
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content faq-cat-content">
                        <?php 
                        
                       $i=0;
                       foreach ($faq as $thiscat){
                            if(!empty($thiscat['Category']['ID'])){ 
                                echo '<div class="tab-pane';
                                //on firt run only
                                if($i==0){echo ' active in ';}
                                echo ' fade" id="faq-cat-'.$thiscat['Category']['ID'].'">'
                                        . '<div class="panel-group" id="accordion-cat-'.$thiscat['Category']['ID'].'">';
                                        
                                foreach ($thiscat[0] as $article){
                                    echo '<div class="panel panel-default panel-faq">'
                                        . '<div class="panel-heading">'
                                        . '<a data-toggle="collapse" data-parent="#accordion-cat-1" href="#faq-cat-'.$thiscat['Category']['ID'].'-art-'.$article['Article']['ID'].'">'
                                        . '<h4 class="panel-title"> '.$article['Article']['question'].'<span class="pull-right"><i class="glyphicon glyphicon-plus"></i></span></h4></a>'
                                        . '</div>
                                    <div id="faq-cat-'.$thiscat['Category']['ID'].'-art-'.$article['Article']['ID'].'" class="panel-collapse collapse">
                                        <div class="panel-body">'.$article['Article']['answer'].'
                                            </div>
                                        </div>
                                     </div>';
                                }
                        
                        echo '       
                                </div>
                            </div>';
                            $i++;
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
</body>
