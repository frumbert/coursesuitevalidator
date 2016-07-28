# validates token or apikey against coursesuite.ninja

At its simplest, this class looks at the value of "token" or "apikey" and validates it against coursesuite.ninja server.

    require_once 'NinjaValidator.php';
    $verifier = (new \Ninjitsu\Validator($_GET))->check();
    if (!$verifier->is_valid()) {
     	die("authentication failed or was not understood.");
    }

# Variables

    $validator->is_api() - boolean
    $validator->is_valid() - boolean, did the token or apikey validate? Must have an active subscription or valid api key
    $validator->is_trial() - boolean, whether the user has a free trial or not
    $validator->api_org() - string, the orgname used to generate the apikey
    $validator->get_tier() - int, the level the user has subscribed to
    $validator->get_response() - object, entire response
    $validator->get_username() - username of the person in coursesuite
    $validator->get_useremail() - email of the person in coursesuite


