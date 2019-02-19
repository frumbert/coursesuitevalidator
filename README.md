# validates token or apikey against an endpoint

At its simplest, this class looks at the value of "token" or "apikey" and validates it against the configured server, which is set via environment variables in the web server.

    composer require coursesuite/validator

then ensure the web server (or host script) sets these environment variables

    HOME_URL            http://www.coursesuite.ninja/home/{app-key}
    AUTHAPI_URL         http://www.coursesuite.ninja/api/validate
    AUTHAPI_USER        (digest-username)
    AUTHAPI_PASSWORD    (digest-password)

then the code

    require '../vendor/autoload.php';
    $verifier = \CourseSuite\Validator::Instance(false)->Validate($_GET);

    if (!$verifier->valid) {
        header("location: " . $verifier->home . "bad-token");
        die();
    }

    if ($verifier->licence->remaining < 1) {
        header("location: " . $verifier->home . "in-use");
        die();
    }

    if ($verifier->licence->tier < 1) {
        header("location: " . $verifier->home . "bad-tier");
        die();
    }    

    if (!$verifier->is_valid()) {
     	die("authentication failed or was not understood.");
    }
    // ... rest of the app


# Returned as php object (default values)

    {
        valid: false,
        licence: {
            tier: 0,
            seats: 1,
            remaining: 1
        },
        code: {
            minified: true,
            debug: false
        },
        api: {
            bearer: null,
            publish: "",
            header: {
                html: "",
                css: ""
            },
            template: ""
        },
        user: {
            container: "",
            email: ""
        },
        addons: [],
        home: ""
    }

