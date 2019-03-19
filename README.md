# validates token or apikey against an endpoint

At its simplest, this class looks at the value of "token" or "apikey" and validates it against the configured server, which is set via environment variables in the web server.

    composer require frumbert/coursesuitevalidator

then ensure the web server (or host script) sets these environment variables

    HOME_URL            https://www.your-app-domain.com/
    AUTHAPI_URL         https://www.coursesuite.ninja/api/validate/your-app-name/{hash}/
    AUTHAPI_USER        (digest-username)
    AUTHAPI_PASSWORD    (digest-password)

The {hash} value will be string-replaced with the "hash" value from the querystring supplied

then the code

    require '../vendor/autoload.php';
    $verifier = (new CoursesuiteValidator(false,true))->Validate($_GET);

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
    // include "rest_of_app.php";


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

