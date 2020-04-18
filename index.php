<?php

if (isset($_SESSION)) {
    session_destroy();
    unset($_SESSION);
    session_start();
}

// random 
include 'activate/connection.php';

if (isset($_GET['referrer'])) {
    if ($_GET['referrer']) {
        $referrer = $_GET['referrer'];
    } else {
        $randomReferrer = db_select("SELECT username FROM accounts"); //WHERE accountStatus = 6;
        $randomReferrer = $randomReferrer[array_rand($randomReferrer)]['username'];
        $referrer = $randomReferrer;
    }
} else {
    $randomReferrer = db_select("SELECT username FROM accounts"); //WHERE accountStatus = 6;
    $randomReferrer = $randomReferrer[array_rand($randomReferrer)]['username'];
    $referrer = $randomReferrer;
}

$referrer = db_quote($referrer);
$result = db_select("SELECT fullName, username FROM accounts WHERE username = " . $referrer);
if (isset($result[0])) {
    $_SESSION['referrerName'] = $result[0]['fullName'];
    $_SESSION['referrer'] = $result[0]['username'];
}

if (isset($_POST['signIn'])) {
    $emailId = db_quote($_POST['emailId']);
    $password = $_POST['password'];
    if (isset($_POST['emailId']) && isset($_POST['password'])) {
        try {
            $result = db_select("SELECT * FROM accounts WHERE emailId = " . $emailId);
            if (isset($result[0])) {
                $x = password_verify($password, $result[0]['password']);
                if ($x) {
                    if (!isset($_SESSION)) session_start();
                    session_destroy();
                    unset($_SESSION);
                    session_start();
                    $_SESSION['valid'] = true;
                    $_SESSION['emailId'] = $result[0]['emailId'];
                    $_SESSION['accessLevel'] = $result[0]['accountStatus'];
                    $_SESSION['userDetail'] = $result[0];
                    $result = db_select("SELECT * FROM accounts WHERE username = " . db_quote($result[0]['referrer']));
                    $_SESSION['referrerAccount'] = $result[0];
                    if ($_SESSION['accessLevel'] > 2)
                        header('Location: app/');
                    else {
                        header('Location: activate/');
                    }
                } else {
                    echo '<script> wrongCredential = true;</script>';
                }
            } else {
                echo '<script> wrongCredential = true;</script>';
            }
        } catch (Exception $e) {
            return mysqli_error($result);
        }
    }
}
if (isset($_POST['createAccount'])) {
    // Quote and escape form submitted values
    $fullName = db_quote($_POST['fullName']);
    $emailId = db_quote($_POST['emailId']);
    $phoneNumber = db_quote($_POST['phoneNumber']);
    $country = db_quote($_POST['country']);
    $username = db_quote($_POST['username']);
    $password = $_POST['password'];
    $password = db_quote(password_hash($password, PASSWORD_DEFAULT));
    $referrer = db_quote($_POST['referrer']);
    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $token =  db_quote(substr(str_shuffle($permitted_chars), 0, 16));
    try {
        $result = array();
        // check if email is already registered
        $result = db_select("SELECT emailId FROM accounts WHERE emailId = " . $emailId);
        echo '<script>console.log(' . sizeof($result) . ')</script>';
        // if email is not already registered, proceed
        if ($result == array()) {
            // check if username is already registered
            $result = db_select("SELECT username FROM accounts WHERE username = " . $username);
            // if username is available, proceed
            // TO-DO simultaneous requests for two same username
            if ($result == array()) {
                $query = "
                    INSERT INTO `accounts`
                    (`fullName`,`emailId`,`phoneNumber`,`country`,`username`,`password`, `referrer`, `token`)
                    VALUES
                    (" . $fullName . "," . $emailId . "," . $phoneNumber . "," . $country . "," . $username . "," . $password . "," . $referrer . "," . $token . ")
		    ";
		$store = db_query($query);
		$result = db_select("SELECT * FROM accounts WHERE emailId = " . $emailId);
                unset($_POST);
                if (!isset($_SESSION)) session_start();
                session_destroy();
                unset($_SESSION);
                session_start();
                $_SESSION['valid'] = true;
                $_SESSION['emailId'] = $emailId;
                $_SESSION['userDetail'] = $result[0];
                $result = db_select("SELECT * FROM accounts WHERE username = " . db_quote($result[0]['referrer']));
                $_SESSION['referrerAccount'] = $result[0];
                header('Location: activate/');
                exit;
            }
            // else if username already exits set invalidUsername true
            else {
                echo '<script> invalidUsername = true; 
                 console.log("Invalid Username "+invalidUsername);</script>';
            }
        }
        // else set invalidEmail true
        else {
            echo '<script> invalidEmail = true; 
            console.log("Invalid Email "+invalidEmail);</script>';
        }
    } catch (Exception $e) {
        return mysqli_error($result);
    }
}
?>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>GrowPartner - Sign In</title>

    <link rel="shortcut icon" href="app/assets/images/icon.png" type="image/png">
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha256-pasqAKBDmFT4eHoN2ndd6lN370kFiGUFyTiUHWhU7k8=" crossorigin="anonymous"></script>
    <!-- Bootstrap core CSS -->
    <link href="app/main.css" rel="stylesheet">
    <meta name="theme-color" content="#4169E1">
    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }
    </style>
    <!-- Custom styles for this template -->
    <style type="text/css">
        :root #content>#right>.dose>.dosesingle,
        :root #content>#center>.dose>.dosesingle {
            display: none !important;
        }

        html,
        body {
            height: 100%;
        }

        body {
            display: -ms-flexbox;
            display: flex;
            /* -ms-flex-align: center; */
            /* align-items: center; */
            background-color: #f5f5f5;
        }

        .form-signin {
            width: 100%;
            max-width: 420px;
            padding: 15px;
            margin: auto;
        }

        .form-label-group {
            position: relative;
            margin-bottom: 1rem;
        }

        .form-label-group>input,
        .form-label-group>label {
            height: 3.125rem;
            padding: .75rem;
        }

        .form-label-group>label {
            position: absolute;
            top: 0;
            left: 0;
            display: block;
            width: 100%;
            margin-bottom: 0;
            /* Override default `<label>` margin */
            line-height: 1.5;
            color: #495057;
            pointer-events: none;
            cursor: text;
            /* Match the input under the label */
            border: 1px solid transparent;
            border-radius: .25rem;
            transition: all .1s ease-in-out;
        }

        .form-label-group input::-webkit-input-placeholder {
            color: transparent;
        }

        .form-label-group input:-ms-input-placeholder {
            color: transparent;
        }

        .form-label-group input::-ms-input-placeholder {
            color: transparent;
        }

        .form-label-group input::-moz-placeholder {
            color: transparent;
        }

        .form-label-group input::placeholder {
            color: transparent;
        }

        .form-label-group input:not(:placeholder-shown) {
            padding-top: 1.25rem;
            padding-bottom: .25rem;
        }

        .form-label-group input:not(:placeholder-shown)~label {
            padding-top: .25rem;
            padding-bottom: .25rem;
            font-size: 12px;
            color: #777;
        }

        .form-signin .form-control:focus {
            border: 0;
            box-shadow: 0 0 0 0.2rem #151f3277;
        }

        .btn:focus,
        .btn:hover {
            box-shadow: 0 0 0 0.2rem #151f3277;

        }

        .form-signup .form-control:focus {
            border: 0;
            box-shadow: 0 0 0 0.2rem #ffffff77;
        }

        .form-signup .custom-select:focus {
            border: 0;
            box-shadow: 0 0 0 0.2rem #ffffff77;
        }

        /* Fallback for Edge
-------------------------------------------------- */
        @supports (-ms-ime-align: auto) {
            .form-label-group>label {
                display: none;
            }

            .form-label-group input::-ms-input-placeholder {
                color: #777;
            }
        }

        /* Fallback for IE
-------------------------------------------------- */
        @media all and (-ms-high-contrast: none),
        (-ms-high-contrast: active) {
            .form-label-group>label {
                display: none;
            }

            .form-label-group input:-ms-input-placeholder {
                color: #777;
            }
        }

        .btn-white:hover {
            box-shadow: 0 0 0 0.2rem #ffffff77;
        }

        .btn-white:focus {
            box-shadow: 0 0 0 0.2rem #ffffff77;
        }
    </style>
</head>

<body>
    <div class="container my-auto">
        <div class="card rounded-corners shadow border border-dark my-5 px-0">
            <div class="row align-items-center">
                <div class="col-lg-5 py-4 px-4">
                <div class="text-center mb-4">
                    <img class="img-fluid" width="200px" src="app/assets/images/login-logo.png">
                </div>
                <!-- <div class="display-7 mb-3 text-center">Sign In</div> -->
                <hr class="w-50 py-1">
                <div>
                    <form class="form-signin px-4" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" style="z-index:1">
                        <div class="form-label-group">
                            <input type="email" name="emailId" id="inputEmail" class="form-control" placeholder="Email address" required>
                            <label for="inputEmail">Email address</label>
                        </div>
                        <div class="form-label-group mb-4">
                            <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
                            <label for="inputPassword">Password</label>
                        </div>
                        <small id="emailHelp" class="form-text text-danger text-center my-3" style="font-size: 0.85rem; display:none">Email or Password Incorrect</small>
                        <script>
                            if (typeof wrongCredential !== 'undefined') {
                                var e = document.querySelector('#emailHelp');
                                e.style.display = "block";
                            }
                        </script>
                        <input class="btn btn-lg bg-royal btn-block py-2 text-white" type="submit" name="signIn" value="Sign In" style="font-size: 1rem; ">

                        <div class="form-label-group mt-4">
                            <a href="" class="text-muted">Forgot Password?</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php
            // if (isset($_SESSION['referrer']))
            //         include 'signup.php';
            ?>
            <div class="col-lg-7 py-4 px-4 bg-royal text-white rounded-corners border border-dark">
                <div class="display-7 pl-3">Sign Up</div>
                <hr class="w-50 ">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <form class="form-signup" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
                                <input style="display:none" name="referrer" type="text" value="<?php echo $_SESSION['referrer'] ?>">
                                <div class="form-row">
                                    <div class="col-md-6 pr-3">
                                        <div class="position-relative form-group">
                                            <label for="fullName" class="">Full Name</label>
                                            <input name="fullName" id="fullName" placeholder="John Doe" type="text" class="form-control" required>
                                            <small for="inputPassword" id="emailHelp1" class="form-text text-danger text-center my-3" style="font-size: 0.85rem; display:none">Email or Password Incorrect</small>

                                        </div>
                                    </div>
                                    <div class="col-md-6 pr-3">
                                        <div class="position-relative form-group">
                                            <label for="newemailId" class="">Email</label>
                                            <input name="emailId" id="newemailId" placeholder="john@example.com" type="email" class="form-control" required>
                                            <small id="newemailHelp" class="form-text text-warning" style="font-size: 0.85rem; display:none">Please try again with another email</small>
                                        </div>
                                        <script>
                                            if (typeof invalidEmail !== 'undefined') {
                                                var e = document.querySelector('#newemailHelp');
                                                e.style.display = "block";
                                            }
                                        </script>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-md-6 pr-3">
                                        <div class="position-relative form-group">
                                            <label for="phoneNumber" class="">Phone Number</label>
                                            <input name="phoneNumber" id="phoneNumber" placeholder="9876543210" type="number" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 pr-3">
                                        <div class="position-relative form-group">
                                            <label for="country" class="">Country</label>
                                            <select id="country" name="country" class="custom-select" required>
                                                <option value="AF">Afghanistan</option>
                                                <option value="AX">Åland Islands</option>
                                                <option value="AL">Albania</option>
                                                <option value="DZ">Algeria</option>
                                                <option value="AS">American Samoa</option>
                                                <option value="AD">Andorra</option>
                                                <option value="AO">Angola</option>
                                                <option value="AI">Anguilla</option>
                                                <option value="AQ">Antarctica</option>
                                                <option value="AG">Antigua and Barbuda</option>
                                                <option value="AR">Argentina</option>
                                                <option value="AM">Armenia</option>
                                                <option value="AW">Aruba</option>
                                                <option value="AU">Australia</option>
                                                <option value="AT">Austria</option>
                                                <option value="AZ">Azerbaijan</option>
                                                <option value="BS">Bahamas</option>
                                                <option value="BH">Bahrain</option>
                                                <option value="BD">Bangladesh</option>
                                                <option value="BB">Barbados</option>
                                                <option value="BY">Belarus</option>
                                                <option value="BE">Belgium</option>
                                                <option value="BZ">Belize</option>
                                                <option value="BJ">Benin</option>
                                                <option value="BM">Bermuda</option>
                                                <option value="BT">Bhutan</option>
                                                <option value="BO">Bolivia, Plurinational State of</option>
                                                <option value="BQ">Bonaire, Sint Eustatius and Saba</option>
                                                <option value="BA">Bosnia and Herzegovina</option>
                                                <option value="BW">Botswana</option>
                                                <option value="BV">Bouvet Island</option>
                                                <option value="BR">Brazil</option>
                                                <option value="IO">British Indian Ocean Territory</option>
                                                <option value="BN">Brunei Darussalam</option>
                                                <option value="BG">Bulgaria</option>
                                                <option value="BF">Burkina Faso</option>
                                                <option value="BI">Burundi</option>
                                                <option value="KH">Cambodia</option>
                                                <option value="CM">Cameroon</option>
                                                <option value="CA">Canada</option>
                                                <option value="CV">Cape Verde</option>
                                                <option value="KY">Cayman Islands</option>
                                                <option value="CF">Central African Republic</option>
                                                <option value="TD">Chad</option>
                                                <option value="CL">Chile</option>
                                                <option value="CN">China</option>
                                                <option value="CX">Christmas Island</option>
                                                <option value="CC">Cocos (Keeling) Islands</option>
                                                <option value="CO">Colombia</option>
                                                <option value="KM">Comoros</option>
                                                <option value="CG">Congo</option>
                                                <option value="CD">Congo, the Democratic Republic of the</option>
                                                <option value="CK">Cook Islands</option>
                                                <option value="CR">Costa Rica</option>
                                                <option value="CI">Côte d'Ivoire</option>
                                                <option value="HR">Croatia</option>
                                                <option value="CU">Cuba</option>
                                                <option value="CW">Curaçao</option>
                                                <option value="CY">Cyprus</option>
                                                <option value="CZ">Czech Republic</option>
                                                <option value="DK">Denmark</option>
                                                <option value="DJ">Djibouti</option>
                                                <option value="DM">Dominica</option>
                                                <option value="DO">Dominican Republic</option>
                                                <option value="EC">Ecuador</option>
                                                <option value="EG">Egypt</option>
                                                <option value="SV">El Salvador</option>
                                                <option value="GQ">Equatorial Guinea</option>
                                                <option value="ER">Eritrea</option>
                                                <option value="EE">Estonia</option>
                                                <option value="ET">Ethiopia</option>
                                                <option value="FK">Falkland Islands (Malvinas)</option>
                                                <option value="FO">Faroe Islands</option>
                                                <option value="FJ">Fiji</option>
                                                <option value="FI">Finland</option>
                                                <option value="FR">France</option>
                                                <option value="GF">French Guiana</option>
                                                <option value="PF">French Polynesia</option>
                                                <option value="TF">French Southern Territories</option>
                                                <option value="GA">Gabon</option>
                                                <option value="GM">Gambia</option>
                                                <option value="GE">Georgia</option>
                                                <option value="DE">Germany</option>
                                                <option value="GH">Ghana</option>
                                                <option value="GI">Gibraltar</option>
                                                <option value="GR">Greece</option>
                                                <option value="GL">Greenland</option>
                                                <option value="GD">Grenada</option>
                                                <option value="GP">Guadeloupe</option>
                                                <option value="GU">Guam</option>
                                                <option value="GT">Guatemala</option>
                                                <option value="GG">Guernsey</option>
                                                <option value="GN">Guinea</option>
                                                <option value="GW">Guinea-Bissau</option>
                                                <option value="GY">Guyana</option>
                                                <option value="HT">Haiti</option>
                                                <option value="HM">Heard Island and McDonald Islands</option>
                                                <option value="VA">Holy See (Vatican City State)</option>
                                                <option value="HN">Honduras</option>
                                                <option value="HK">Hong Kong</option>
                                                <option value="HU">Hungary</option>
                                                <option value="IS">Iceland</option>
                                                <option value="IN" selected>India</option>
                                                <option value="ID">Indonesia</option>
                                                <option value="IR">Iran, Islamic Republic of</option>
                                                <option value="IQ">Iraq</option>
                                                <option value="IE">Ireland</option>
                                                <option value="IM">Isle of Man</option>
                                                <option value="IL">Israel</option>
                                                <option value="IT">Italy</option>
                                                <option value="JM">Jamaica</option>
                                                <option value="JP">Japan</option>
                                                <option value="JE">Jersey</option>
                                                <option value="JO">Jordan</option>
                                                <option value="KZ">Kazakhstan</option>
                                                <option value="KE">Kenya</option>
                                                <option value="KI">Kiribati</option>
                                                <option value="KP">Korea, Democratic People's Republic of</option>
                                                <option value="KR">Korea, Republic of</option>
                                                <option value="KW">Kuwait</option>
                                                <option value="KG">Kyrgyzstan</option>
                                                <option value="LA">Lao People's Democratic Republic</option>
                                                <option value="LV">Latvia</option>
                                                <option value="LB">Lebanon</option>
                                                <option value="LS">Lesotho</option>
                                                <option value="LR">Liberia</option>
                                                <option value="LY">Libya</option>
                                                <option value="LI">Liechtenstein</option>
                                                <option value="LT">Lithuania</option>
                                                <option value="LU">Luxembourg</option>
                                                <option value="MO">Macao</option>
                                                <option value="MK">Macedonia, the former Yugoslav Republic of</option>
                                                <option value="MG">Madagascar</option>
                                                <option value="MW">Malawi</option>
                                                <option value="MY">Malaysia</option>
                                                <option value="MV">Maldives</option>
                                                <option value="ML">Mali</option>
                                                <option value="MT">Malta</option>
                                                <option value="MH">Marshall Islands</option>
                                                <option value="MQ">Martinique</option>
                                                <option value="MR">Mauritania</option>
                                                <option value="MU">Mauritius</option>
                                                <option value="YT">Mayotte</option>
                                                <option value="MX">Mexico</option>
                                                <option value="FM">Micronesia, Federated States of</option>
                                                <option value="MD">Moldova, Republic of</option>
                                                <option value="MC">Monaco</option>
                                                <option value="MN">Mongolia</option>
                                                <option value="ME">Montenegro</option>
                                                <option value="MS">Montserrat</option>
                                                <option value="MA">Morocco</option>
                                                <option value="MZ">Mozambique</option>
                                                <option value="MM">Myanmar</option>
                                                <option value="NA">Namibia</option>
                                                <option value="NR">Nauru</option>
                                                <option value="NP">Nepal</option>
                                                <option value="NL">Netherlands</option>
                                                <option value="NC">New Caledonia</option>
                                                <option value="NZ">New Zealand</option>
                                                <option value="NI">Nicaragua</option>
                                                <option value="NE">Niger</option>
                                                <option value="NG">Nigeria</option>
                                                <option value="NU">Niue</option>
                                                <option value="NF">Norfolk Island</option>
                                                <option value="MP">Northern Mariana Islands</option>
                                                <option value="NO">Norway</option>
                                                <option value="OM">Oman</option>
                                                <option value="PK">Pakistan</option>
                                                <option value="PW">Palau</option>
                                                <option value="PS">Palestinian Territory, Occupied</option>
                                                <option value="PA">Panama</option>
                                                <option value="PG">Papua New Guinea</option>
                                                <option value="PY">Paraguay</option>
                                                <option value="PE">Peru</option>
                                                <option value="PH">Philippines</option>
                                                <option value="PN">Pitcairn</option>
                                                <option value="PL">Poland</option>
                                                <option value="PT">Portugal</option>
                                                <option value="PR">Puerto Rico</option>
                                                <option value="QA">Qatar</option>
                                                <option value="RE">Réunion</option>
                                                <option value="RO">Romania</option>
                                                <option value="RU">Russian Federation</option>
                                                <option value="RW">Rwanda</option>
                                                <option value="BL">Saint Barthélemy</option>
                                                <option value="SH">Saint Helena, Ascension and Tristan da Cunha</option>
                                                <option value="KN">Saint Kitts and Nevis</option>
                                                <option value="LC">Saint Lucia</option>
                                                <option value="MF">Saint Martin (French part)</option>
                                                <option value="PM">Saint Pierre and Miquelon</option>
                                                <option value="VC">Saint Vincent and the Grenadines</option>
                                                <option value="WS">Samoa</option>
                                                <option value="SM">San Marino</option>
                                                <option value="ST">Sao Tome and Principe</option>
                                                <option value="SA">Saudi Arabia</option>
                                                <option value="SN">Senegal</option>
                                                <option value="RS">Serbia</option>
                                                <option value="SC">Seychelles</option>
                                                <option value="SL">Sierra Leone</option>
                                                <option value="SG">Singapore</option>
                                                <option value="SX">Sint Maarten (Dutch part)</option>
                                                <option value="SK">Slovakia</option>
                                                <option value="SI">Slovenia</option>
                                                <option value="SB">Solomon Islands</option>
                                                <option value="SO">Somalia</option>
                                                <option value="ZA">South Africa</option>
                                                <option value="GS">South Georgia and the South Sandwich Islands</option>
                                                <option value="SS">South Sudan</option>
                                                <option value="ES">Spain</option>
                                                <option value="LK">Sri Lanka</option>
                                                <option value="SD">Sudan</option>
                                                <option value="SR">Suriname</option>
                                                <option value="SJ">Svalbard and Jan Mayen</option>
                                                <option value="SZ">Swaziland</option>
                                                <option value="SE">Sweden</option>
                                                <option value="CH">Switzerland</option>
                                                <option value="SY">Syrian Arab Republic</option>
                                                <option value="TW">Taiwan, Province of China</option>
                                                <option value="TJ">Tajikistan</option>
                                                <option value="TZ">Tanzania, United Republic of</option>
                                                <option value="TH">Thailand</option>
                                                <option value="TL">Timor-Leste</option>
                                                <option value="TG">Togo</option>
                                                <option value="TK">Tokelau</option>
                                                <option value="TO">Tonga</option>
                                                <option value="TT">Trinidad and Tobago</option>
                                                <option value="TN">Tunisia</option>
                                                <option value="TR">Turkey</option>
                                                <option value="TM">Turkmenistan</option>
                                                <option value="TC">Turks and Caicos Islands</option>
                                                <option value="TV">Tuvalu</option>
                                                <option value="UG">Uganda</option>
                                                <option value="UA">Ukraine</option>
                                                <option value="AE">United Arab Emirates</option>
                                                <option value="GB">United Kingdom</option>
                                                <option value="US">United States</option>
                                                <option value="UM">United States Minor Outlying Islands</option>
                                                <option value="UY">Uruguay</option>
                                                <option value="UZ">Uzbekistan</option>
                                                <option value="VU">Vanuatu</option>
                                                <option value="VE">Venezuela, Bolivarian Republic of</option>
                                                <option value="VN">Viet Nam</option>
                                                <option value="VG">Virgin Islands, British</option>
                                                <option value="VI">Virgin Islands, U.S.</option>
                                                <option value="WF">Wallis and Futuna</option>
                                                <option value="EH">Western Sahara</option>
                                                <option value="YE">Yemen</option>
                                                <option value="ZM">Zambia</option>
                                                <option value="ZW">Zimbabwe</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-md-4 pr-3">
                                        <div class="position-relative form-group">
                                            <label for="username" class="">Username</label>
                                            <input name="username" id="username" type="text" class="form-control" placeholder="johndoe" required>
                                            <small id="usernameHelp" class="form-text text-warning" style="font-size: 0.85rem; display: none">Username not available.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4 pr-3">
                                        <div class="position-relative form-group">
                                            <label for="password" class="">Password</label>
                                            <input name="password" id="password" type="password" placeholder="Password" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 pr-3">
                                        <div class="position-relative form-group">
                                            <label for="confirmPassword" class="">Confirm Password</label>
                                            <input name="confirmPassword" id="confirmPassword" type="password" placeholder="Re-enter password" class="form-control" required>
                                            <small id="passwordHelp" class="form-text text-warning" style="font-size: 0.85rem;display: none;">Passwords don't match.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="my-1 d-flex flex-column ">
                                    <div class="signupDiv">
                                        By clicking 'Create Account' you agree to our <a href="../landing/legal.html" class="text-white">terms</a>.<br>
                                    </div>
                                    <div class="partnerName mt-4 row justify-content-lg-between">
                                        <input type="submit" name="createAccount" id="createAccount" class="btn btn-lg px-4 py-2 btn-white col-xs-6" value="Create Account" style="font-size: 1rem; background: #42566c; color: #fff">
                                        <span class="col-xs-6 pr-4 pl-2 pt-2">
                                            Your partner is <b><?php echo $_SESSION['referrerName'] ?></b>
                                            <i class="pe-7s-info pe-lg pe-va ml-2"></i>
                                        </span>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</body>

</html>
