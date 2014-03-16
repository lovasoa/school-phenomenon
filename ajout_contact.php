 <?php
require_once("google_api_info.php");
//TODO
$redirect_uri = "http://localhost/D%C3%A9veloppement/HISTU/ajout_contact.php";

$group_id = "6"; // Used as the default 'My Contacts' group.

require_once ('Google/Client.php');
require_once ('Google/Http/Request.php');

session_start();

$client = new Google_Client();
$client->setApplicationName("Ajout contact School Phenomenon");
$client->setScopes(array(
	'https://www.googleapis.com/auth/userinfo.email',
    'https://apps-apis.google.com/a/feeds/groups/',
    'https://apps-apis.google.com/a/feeds/alias/',
    'https://apps-apis.google.com/a/feeds/user/',
    'https://www.google.com/m8/feeds/',
    'https://www.google.com/m8/feeds/user/'
));

// Documentation: http://code.google.com/googleapps/domain/provisioning_API_v2_developers_guide.html
// Visit https://code.google.com/apis/console to generate your
// oauth2_client_id, oauth2_client_secret, and to register your oauth2_redirect_uri.

$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
//TODO
//$client->setDeveloperKey('DEVELOPER_KEY');

if (isset($_REQUEST['logout'])) {
    unset($_SESSION['access_token']);
}

if (isset($_GET['code'])) {
    $client->authenticate($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
    header('Location: ' . $redirect_uri);
}

if (isset($_SESSION['access_token'])) {

    $client->setAccessToken($_SESSION['access_token']);

	$token_data = $client->verifyIdToken()->getAttributes();

    //Get Email of User ------------------------------------
    // You are now logged in
    // We need the users email address for later use. We can get that here.

    $user_email = $token_data['payload']['email']; // email address

    //-------------------------------------
    // How to save an entry to your My Contacts List
    // This is an example contact XML that Google is looking for.

    $contact = "
    <atom:entry xmlns:atom='http://www.w3.org/2005/Atom'
        xmlns:gd='http://schemas.google.com/g/2005'
        xmlns:gContact='http://schemas.google.com/contact/2008'>
      <atom:category scheme='http://schemas.google.com/g/2005#kind'
        term='http://schemas.google.com/contact/2008#contact'/>
      <gd:name>
         <gd:givenName>HELLO</gd:givenName>
         <gd:familyName>WORLD</gd:familyName>
         <gd:fullName>Hello World</gd:fullName>
      </gd:name>
      <atom:content type='text'>Notes</atom:content>
      <gd:email rel='http://schemas.google.com/g/2005#work'
        primary='true'
        address='liz@gmail.com' displayName='E. Bennet'/>
      <gd:email rel='http://schemas.google.com/g/2005#home'
        address='liz@example.org'/>
      <gd:phoneNumber rel='http://schemas.google.com/g/2005#work'
        primary='true'>
        (206)555-1212
      </gd:phoneNumber>
      <gd:phoneNumber rel='http://schemas.google.com/g/2005#home'>
        (206)555-1213
      </gd:phoneNumber>
      <gd:im address='liz@gmail.com'
        protocol='http://schemas.google.com/g/2005#GOOGLE_TALK'
        primary='true'
        rel='http://schemas.google.com/g/2005#home'/>
      <gd:structuredPostalAddress
          rel='http://schemas.google.com/g/2005#work'
          primary='true'>
        <gd:city>Mountain View</gd:city>
        <gd:street>1600 Amphitheatre Pkwy</gd:street>
        <gd:region>CA</gd:region>
        <gd:postcode>94043</gd:postcode>
        <gd:country>United States</gd:country>
        <gd:formattedAddress>
          1600 Amphitheatre Pkwy Mountain View
        </gd:formattedAddress>
      </gd:structuredPostalAddress>
     <gContact:groupMembershipInfo deleted='false'
            href='http://www.google.com/m8/feeds/groups/" . $user_email . "/base/6'/>
    </atom:entry>
    ";

    $len = strlen($contact);
    $add = new Google_Http_Request("https://www.google.com/m8/feeds/contacts/default/full/");
    $add->setRequestMethod("POST");
    $add->setPostBody($contact);
    $add->setRequestHeaders(array(
        'content-length' => $len,
        'GData-Version' => '3.0',
        'content-type' => 'application/atom+xml; charset=UTF-8; type=feed'
    ));

	$add = $client->getAuth()->sign($add);

    $submit       = $client->execute($add);

    // The access token may have been updated lazily.
    $_SESSION['access_token'] = $client->getAccessToken();
} else {
    $authUrl = $client->createAuthUrl();
}

if (isset($authUrl)) {
    print "<a class='login' href='$authUrl'>Connect Me!</a>";
} else {
    print "<a class='logout' href='?logout'>Logout</a>";
}

?>
