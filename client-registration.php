<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Client Registration - M25 Travel & Tour Agency</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@200;300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    
    <!-- Google Translate -->
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'en,fr,es,de,it,pt,ar,zh,hi,ja,ko,ru,nl,sv,no,da',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                autoDisplay: false
            }, 'google_translate_element');
        }
    </script>
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    
    <style>
        .form-section {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .section-title {
            color: #13357B;
            border-bottom: 3px solid #FEA116;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        .form-control:focus {
            border-color: #FEA116;
            box-shadow: 0 0 0 0.2rem rgba(254, 161, 22, 0.25);
        }
        .btn-primary {
            background-color: #13357B;
            border-color: #13357B;
        }
        .btn-primary:hover {
            background-color: #FEA116;
            border-color: #FEA116;
        }
        .required {
            color: #dc3545;
        }
    </style>
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-secondary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <!-- Hidden Google Translate Element -->
    <div id="google_translate_element" style="display: none;"></div>

    <!-- Navbar Start -->
    <div class="container-fluid bg-primary px-5 d-none d-lg-block">
        <div class="row gx-0 align-items-center">
            <div class="col-lg-5 text-center text-lg-start mb-lg-0">
                <div class="d-flex">
                    <a href="mailto:info@m25travelagency.com" class="text-muted me-4"><i class="fas fa-envelope text-secondary me-2"></i>info@m25travelagency.com</a>
                    <a href="tel:+233592605752" class="text-muted me-0"><i class="fas fa-phone-alt text-secondary me-2"></i>+233 592 605 752</a>
                </div>
            </div>
            <div class="col-lg-7 text-center text-lg-end">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <div class="dropdown me-3">
                        <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-globe me-1"></i> Language
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                            <li><a class="dropdown-item" href="#" onclick="translatePage('en')"><img src="https://flagcdn.com/16x12/us.png" class="me-2" alt="English">English</a></li>
                            <li><a class="dropdown-item" href="#" onclick="translatePage('fr')"><img src="https://flagcdn.com/16x12/fr.png" class="me-2" alt="French">French</a></li>
                            <li><a class="dropdown-item" href="#" onclick="translatePage('es')"><img src="https://flagcdn.com/16x12/es.png" class="me-2" alt="Spanish">Spanish</a></li>
                            <li><a class="dropdown-item" href="#" onclick="translatePage('de')"><img src="https://flagcdn.com/16x12/de.png" class="me-2" alt="German">German</a></li>
                            <li><a class="dropdown-item" href="#" onclick="translatePage('it')"><img src="https://flagcdn.com/16x12/it.png" class="me-2" alt="Italian">Italian</a></li>
                            <li><a class="dropdown-item" href="#" onclick="translatePage('pt')"><img src="https://flagcdn.com/16x12/pt.png" class="me-2" alt="Portuguese">Portuguese</a></li>
                            <li><a class="dropdown-item" href="#" onclick="translatePage('ar')"><img src="https://flagcdn.com/16x12/sa.png" class="me-2" alt="Arabic">Arabic</a></li>
                            <li><a class="dropdown-item" href="#" onclick="translatePage('zh')"><img src="https://flagcdn.com/16x12/cn.png" class="me-2" alt="Chinese">Chinese</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top px-4 px-lg-5">
        <a href="/" class="navbar-brand d-flex align-items-center">
            <h4 class="mb-0 text-primary"><i class="fas fa-plane me-2"></i>M25 Travel Agency</h4>
        </a>
        <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto py-0">
                <a href="/" class="nav-item nav-link">Home</a>
                <a href="client-registration" class="nav-item nav-link active">Client Registration</a>
                <a href="client-login.php" class="nav-item nav-link">Client Login</a>
                <!-- <a href="admin-login" class="nav-item nav-link">Admin</a> -->
            </div>
        </div>
    </nav>
    <!-- Navbar End -->

    <!-- Header Start -->
    <div class="container-fluid bg-breadcrumb">
        <div class="container text-center py-5" style="max-width: 900px;">
            <h3 class="text-white display-3 mb-4">Client Registration Form</h3>
            <p class="fs-5 text-white mb-4">Complete your visa application details</p>
        </div>
    </div>
    <!-- Header End -->

    <!-- Registration Form Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            
            <!-- Error/Success Messages -->
            <div id="messageArea">
                <?php
                session_start();
                if (isset($_SESSION['error_message'])) {
                    echo '<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-left: 5px solid #dc3545;">';
                    echo '<div class="d-flex align-items-center">';
                    echo '<i class="fas fa-exclamation-triangle me-3" style="font-size: 1.2em;"></i>';
                    echo '<div>';
                    echo '<strong>Registration Error:</strong><br>';
                    echo htmlspecialchars($_SESSION['error_message']);
                    echo '</div>';
                    echo '</div>';
                    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    echo '</div>';
                    unset($_SESSION['error_message']);
                }
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-left: 5px solid #28a745;">';
                    echo '<div class="d-flex align-items-center">';
                    echo '<i class="fas fa-check-circle me-3" style="font-size: 1.2em;"></i>';
                    echo '<div>';
                    echo '<strong>Success:</strong><br>';
                    echo htmlspecialchars($_SESSION['success_message']);
                    echo '</div>';
                    echo '</div>';
                    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    echo '</div>';
                    unset($_SESSION['success_message']);
                }
                ?>
            </div>
            
            <form id="clientForm" action="process-registration.php" method="POST" enctype="multipart/form-data">
                
                <!-- Client Information Section -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-user me-2"></i>Client Information</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="required">*</span></label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender <span class="required">*</span></label>
                            <select class="form-select" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth <span class="required">*</span></label>
                            <input type="date" class="form-control" name="date_of_birth" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country <span class="required">*</span></label>
                            <select class="form-select" name="country" required>
                                <option value="">Select Country</option>
                                <option value="Afghanistan">Afghanistan</option>
                                <option value="Albania">Albania</option>
                                <option value="Algeria">Algeria</option>
                                <option value="Argentina">Argentina</option>
                                <option value="Australia">Australia</option>
                                <option value="Austria">Austria</option>
                                <option value="Bangladesh">Bangladesh</option>
                                <option value="Belgium">Belgium</option>
                                <option value="Brazil">Brazil</option>
                                <option value="Canada">Canada</option>
                                <option value="China">China</option>
                                <option value="Denmark">Denmark</option>
                                <option value="Egypt">Egypt</option>
                                <option value="France">France</option>
                                <option value="Germany">Germany</option>
                                <option value="Ghana">Ghana</option>
                                <option value="India">India</option>
                                <option value="Indonesia">Indonesia</option>
                                <option value="Italy">Italy</option>
                                <option value="Japan">Japan</option>
                                <option value="Kenya">Kenya</option>
                                <option value="Malaysia">Malaysia</option>
                                <option value="Netherlands">Netherlands</option>
                                <option value="Nigeria">Nigeria</option>
                                <option value="Norway">Norway</option>
                                <option value="Pakistan">Pakistan</option>
                                <option value="Philippines">Philippines</option>
                                <option value="Poland">Poland</option>
                                <option value="Portugal">Portugal</option>
                                <option value="Russia">Russia</option>
                                <option value="Saudi Arabia">Saudi Arabia</option>
                                <option value="Singapore">Singapore</option>
                                <option value="South Africa">South Africa</option>
                                <option value="South Korea">South Korea</option>
                                <option value="Spain">Spain</option>
                                <option value="Sweden">Sweden</option>
                                <option value="Switzerland">Switzerland</option>
                                <option value="Thailand">Thailand</option>
                                <option value="Turkey">Turkey</option>
                                <option value="Ukraine">Ukraine</option>
                                <option value="United Arab Emirates">United Arab Emirates</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="United States">United States</option>
                                <option value="Vietnam">Vietnam</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile Number <span class="required">*</span></label>
                            <div class="input-group">
                                <select class="form-select" name="country_code" style="max-width: 120px;" required>
                                    <option value="">Code</option>
                                    <option value="+1">+1 (US/CA)</option>
                                    <option value="+7">+7 (RU/KZ)</option>
                                    <option value="+20">+20 (EG)</option>
                                    <option value="+27">+27 (ZA)</option>
                                    <option value="+30">+30 (GR)</option>
                                    <option value="+31">+31 (NL)</option>
                                    <option value="+32">+32 (BE)</option>
                                    <option value="+33">+33 (FR)</option>
                                    <option value="+34">+34 (ES)</option>
                                    <option value="+39">+39 (IT)</option>
                                    <option value="+40">+40 (RO)</option>
                                    <option value="+41">+41 (CH)</option>
                                    <option value="+43">+43 (AT)</option>
                                    <option value="+44">+44 (UK)</option>
                                    <option value="+45">+45 (DK)</option>
                                    <option value="+46">+46 (SE)</option>
                                    <option value="+47">+47 (NO)</option>
                                    <option value="+48">+48 (PL)</option>
                                    <option value="+49">+49 (DE)</option>
                                    <option value="+51">+51 (PE)</option>
                                    <option value="+52">+52 (MX)</option>
                                    <option value="+53">+53 (CU)</option>
                                    <option value="+54">+54 (AR)</option>
                                    <option value="+55">+55 (BR)</option>
                                    <option value="+56">+56 (CL)</option>
                                    <option value="+57">+57 (CO)</option>
                                    <option value="+58">+58 (VE)</option>
                                    <option value="+60">+60 (MY)</option>
                                    <option value="+61">+61 (AU)</option>
                                    <option value="+62">+62 (ID)</option>
                                    <option value="+63">+63 (PH)</option>
                                    <option value="+64">+64 (NZ)</option>
                                    <option value="+65">+65 (SG)</option>
                                    <option value="+66">+66 (TH)</option>
                                    <option value="+81">+81 (JP)</option>
                                    <option value="+82">+82 (KR)</option>
                                    <option value="+84">+84 (VN)</option>
                                    <option value="+86">+86 (CN)</option>
                                    <option value="+90">+90 (TR)</option>
                                    <option value="+91">+91 (IN)</option>
                                    <option value="+92">+92 (PK)</option>
                                    <option value="+93">+93 (AF)</option>
                                    <option value="+94">+94 (LK)</option>
                                    <option value="+95">+95 (MM)</option>
                                    <option value="+98">+98 (IR)</option>
                                    <option value="+212">+212 (MA)</option>
                                    <option value="+213">+213 (DZ)</option>
                                    <option value="+216">+216 (TN)</option>
                                    <option value="+218">+218 (LY)</option>
                                    <option value="+220">+220 (GM)</option>
                                    <option value="+221">+221 (SN)</option>
                                    <option value="+222">+222 (MR)</option>
                                    <option value="+223">+223 (ML)</option>
                                    <option value="+224">+224 (GN)</option>
                                    <option value="+225">+225 (CI)</option>
                                    <option value="+226">+226 (BF)</option>
                                    <option value="+227">+227 (NE)</option>
                                    <option value="+228">+228 (TG)</option>
                                    <option value="+229">+229 (BJ)</option>
                                    <option value="+230">+230 (MU)</option>
                                    <option value="+231">+231 (LR)</option>
                                    <option value="+232">+232 (SL)</option>
                                    <option value="+233">+233 (GH)</option>
                                    <option value="+234">+234 (NG)</option>
                                    <option value="+235">+235 (TD)</option>
                                    <option value="+236">+236 (CF)</option>
                                    <option value="+237">+237 (CM)</option>
                                    <option value="+238">+238 (CV)</option>
                                    <option value="+239">+239 (ST)</option>
                                    <option value="+240">+240 (GQ)</option>
                                    <option value="+241">+241 (GA)</option>
                                    <option value="+242">+242 (CG)</option>
                                    <option value="+243">+243 (CD)</option>
                                    <option value="+244">+244 (AO)</option>
                                    <option value="+245">+245 (GW)</option>
                                    <option value="+246">+246 (IO)</option>
                                    <option value="+248">+248 (SC)</option>
                                    <option value="+249">+249 (SD)</option>
                                    <option value="+250">+250 (RW)</option>
                                    <option value="+251">+251 (ET)</option>
                                    <option value="+252">+252 (SO)</option>
                                    <option value="+253">+253 (DJ)</option>
                                    <option value="+254">+254 (KE)</option>
                                    <option value="+255">+255 (TZ)</option>
                                    <option value="+256">+256 (UG)</option>
                                    <option value="+257">+257 (BI)</option>
                                    <option value="+258">+258 (MZ)</option>
                                    <option value="+260">+260 (ZM)</option>
                                    <option value="+261">+261 (MG)</option>
                                    <option value="+262">+262 (RE)</option>
                                    <option value="+263">+263 (ZW)</option>
                                    <option value="+264">+264 (NA)</option>
                                    <option value="+265">+265 (MW)</option>
                                    <option value="+266">+266 (LS)</option>
                                    <option value="+267">+267 (BW)</option>
                                    <option value="+268">+268 (SZ)</option>
                                    <option value="+269">+269 (KM)</option>
                                    <option value="+290">+290 (SH)</option>
                                    <option value="+291">+291 (ER)</option>
                                    <option value="+297">+297 (AW)</option>
                                    <option value="+298">+298 (FO)</option>
                                    <option value="+299">+299 (GL)</option>
                                    <option value="+350">+350 (GI)</option>
                                    <option value="+351">+351 (PT)</option>
                                    <option value="+352">+352 (LU)</option>
                                    <option value="+353">+353 (IE)</option>
                                    <option value="+354">+354 (IS)</option>
                                    <option value="+355">+355 (AL)</option>
                                    <option value="+356">+356 (MT)</option>
                                    <option value="+357">+357 (CY)</option>
                                    <option value="+358">+358 (FI)</option>
                                    <option value="+359">+359 (BG)</option>
                                    <option value="+370">+370 (LT)</option>
                                    <option value="+371">+371 (LV)</option>
                                    <option value="+372">+372 (EE)</option>
                                    <option value="+373">+373 (MD)</option>
                                    <option value="+374">+374 (AM)</option>
                                    <option value="+375">+375 (BY)</option>
                                    <option value="+376">+376 (AD)</option>
                                    <option value="+377">+377 (MC)</option>
                                    <option value="+378">+378 (SM)</option>
                                    <option value="+380">+380 (UA)</option>
                                    <option value="+381">+381 (RS)</option>
                                    <option value="+382">+382 (ME)</option>
                                    <option value="+383">+383 (XK)</option>
                                    <option value="+385">+385 (HR)</option>
                                    <option value="+386">+386 (SI)</option>
                                    <option value="+387">+387 (BA)</option>
                                    <option value="+389">+389 (MK)</option>
                                    <option value="+420">+420 (CZ)</option>
                                    <option value="+421">+421 (SK)</option>
                                    <option value="+423">+423 (LI)</option>
                                    <option value="+500">+500 (FK)</option>
                                    <option value="+501">+501 (BZ)</option>
                                    <option value="+502">+502 (GT)</option>
                                    <option value="+503">+503 (SV)</option>
                                    <option value="+504">+504 (HN)</option>
                                    <option value="+505">+505 (NI)</option>
                                    <option value="+506">+506 (CR)</option>
                                    <option value="+507">+507 (PA)</option>
                                    <option value="+508">+508 (PM)</option>
                                    <option value="+509">+509 (HT)</option>
                                    <option value="+590">+590 (GP)</option>
                                    <option value="+591">+591 (BO)</option>
                                    <option value="+592">+592 (GY)</option>
                                    <option value="+593">+593 (EC)</option>
                                    <option value="+594">+594 (GF)</option>
                                    <option value="+595">+595 (PY)</option>
                                    <option value="+596">+596 (MQ)</option>
                                    <option value="+597">+597 (SR)</option>
                                    <option value="+598">+598 (UY)</option>
                                    <option value="+599">+599 (CW)</option>
                                    <option value="+670">+670 (TL)</option>
                                    <option value="+672">+672 (NF)</option>
                                    <option value="+673">+673 (BN)</option>
                                    <option value="+674">+674 (NR)</option>
                                    <option value="+675">+675 (PG)</option>
                                    <option value="+676">+676 (TO)</option>
                                    <option value="+677">+677 (SB)</option>
                                    <option value="+678">+678 (VU)</option>
                                    <option value="+679">+679 (FJ)</option>
                                    <option value="+680">+680 (PW)</option>
                                    <option value="+681">+681 (WF)</option>
                                    <option value="+682">+682 (CK)</option>
                                    <option value="+683">+683 (NU)</option>
                                    <option value="+684">+684 (AS)</option>
                                    <option value="+685">+685 (WS)</option>
                                    <option value="+686">+686 (KI)</option>
                                    <option value="+687">+687 (NC)</option>
                                    <option value="+688">+688 (TV)</option>
                                    <option value="+689">+689 (PF)</option>
                                    <option value="+690">+690 (TK)</option>
                                    <option value="+691">+691 (FM)</option>
                                    <option value="+692">+692 (MH)</option>
                                    <option value="+850">+850 (KP)</option>
                                    <option value="+852">+852 (HK)</option>
                                    <option value="+853">+853 (MO)</option>
                                    <option value="+855">+855 (KH)</option>
                                    <option value="+856">+856 (LA)</option>
                                    <option value="+880">+880 (BD)</option>
                                    <option value="+886">+886 (TW)</option>
                                    <option value="+960">+960 (MV)</option>
                                    <option value="+961">+961 (LB)</option>
                                    <option value="+962">+962 (JO)</option>
                                    <option value="+963">+963 (SY)</option>
                                    <option value="+964">+964 (IQ)</option>
                                    <option value="+965">+965 (KW)</option>
                                    <option value="+966">+966 (SA)</option>
                                    <option value="+967">+967 (YE)</option>
                                    <option value="+968">+968 (OM)</option>
                                    <option value="+970">+970 (PS)</option>
                                    <option value="+971">+971 (AE)</option>
                                    <option value="+972">+972 (IL)</option>
                                    <option value="+973">+973 (BH)</option>
                                    <option value="+974">+974 (QA)</option>
                                    <option value="+975">+975 (BT)</option>
                                    <option value="+976">+976 (MN)</option>
                                    <option value="+977">+977 (NP)</option>
                                    <option value="+992">+992 (TJ)</option>
                                    <option value="+993">+993 (TM)</option>
                                    <option value="+994">+994 (AZ)</option>
                                    <option value="+995">+995 (GE)</option>
                                    <option value="+996">+996 (KG)</option>
                                    <option value="+998">+998 (UZ)</option>
                                </select>
                                <input type="tel" class="form-control" name="mobile_number" placeholder="Enter phone number" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Client Email <span class="required">*</span></label>
                            <input type="email" class="form-control" name="client_email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="required">*</span></label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Passport Number</label>
                            <input type="text" class="form-control" name="passport_number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Visa Type <span class="required">*</span></label>
                            <select class="form-select" name="visa_type" required>
                                <option value="">Select Visa Type</option>
                                <option value="Visit">Visit</option>
                                <option value="Work">Work</option>
                                <option value="Study">Study</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Work Type</label>
                            <input type="text" class="form-control" name="work_type">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date Contract Started</label>
                            <input type="date" class="form-control" name="date_contract_started">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Visa Application Hold</label>
                            <select class="form-select" name="visa_application_hold">
                                <option value="No">No</option>
                                <option value="Yes">Yes</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Visa Denial Appeal</label>
                            <select class="form-select" name="visa_denial_appeal">
                                <option value="No">No</option>
                                <option value="Yes">Yes</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Immigration History</label>
                            <textarea class="form-control" name="immigration_history" rows="3"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Result Outcome</label>
                            <input type="text" class="form-control" name="result_outcome">
                        </div>
                    </div>
                </div>

                <!-- Personal & Employment Details Section -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-briefcase me-2"></i>Personal & Employment Details</h3>
                    <div class="row g-3">
                        <!-- Detailed Address Information -->
                        <h5 class="text-primary mb-3">Address Details</h5>
                        <div class="col-md-6">
                            <label class="form-label">House Number</label>
                            <input type="text" class="form-control" name="house_number" placeholder="e.g., 123 or Apt 4B">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Street Name</label>
                            <input type="text" class="form-control" name="street_name" placeholder="e.g., Main Street or Oak Avenue">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City/Location</label>
                            <input type="text" class="form-control" name="location" placeholder="e.g., New York, London, Lagos">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Postal/ZIP Code</label>
                            <input type="text" class="form-control" name="digital_address" placeholder="e.g., 10001, SW1A 1AA, 100001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">State/Province/Region</label>
                            <input type="text" class="form-control" name="postal_address" placeholder="e.g., California, Ontario, Lagos State">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Physical Address <span class="required">*</span></label>
                            <textarea class="form-control" name="address" rows="2" required placeholder="Complete physical address"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Current Employer</label>
                            <input type="text" class="form-control" name="current_employer" placeholder="Company/Organization Name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Job Title/Position</label>
                            <input type="text" class="form-control" name="job_title" placeholder="Your current position">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Employment Status</label>
                            <select class="form-select" name="employment_status">
                                <option value="">Select Status</option>
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Contract">Contract</option>
                                <option value="Self-employed">Self-employed</option>
                                <option value="Unemployed">Unemployed</option>
                                <option value="Student">Student</option>
                                <option value="Retired">Retired</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Monthly Income (Local Currency)</label>
                            <input type="number" class="form-control" name="monthly_income" placeholder="0.00" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Years of Experience</label>
                            <input type="number" class="form-control" name="years_experience" placeholder="0" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Industry/Sector</label>
                            <select class="form-select" name="industry_sector">
                                <option value="">Select Industry</option>
                                <option value="Agriculture">Agriculture</option>
                                <option value="Banking & Finance">Banking & Finance</option>
                                <option value="Construction">Construction</option>
                                <option value="Education">Education</option>
                                <option value="Healthcare">Healthcare</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Manufacturing">Manufacturing</option>
                                <option value="Mining">Mining</option>
                                <option value="Oil & Gas">Oil & Gas</option>
                                <option value="Retail & Trade">Retail & Trade</option>
                                <option value="Tourism & Hospitality">Tourism & Hospitality</option>
                                <option value="Transportation">Transportation</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Employer Address</label>
                            <textarea class="form-control" name="employer_address" rows="2" placeholder="Complete address of your employer"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Employment Start Date</label>
                            <input type="date" class="form-control" name="employment_start_date">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Employment Letter Details</label>
                            <textarea class="form-control" name="employment_letter_details" rows="3" placeholder="Please provide details about your employment letter (Position, Salary, Duration of Employment, Leave Approval for Travel)"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Upload Employment Letter</label>
                            <input type="file" class="form-control" name="employment_letter" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <small class="text-muted">Upload employment letter on company letterhead</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Upload Payslips (Last 3-6 months)</label>
                            <input type="file" class="form-control" name="payslips" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>
                            <small class="text-muted">Upload salary evidence/payslips</small>
                        </div>
                    </div>
                </div>

                <!-- Educational Information Section -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-graduation-cap me-2"></i>Educational Information</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">University/Institution</label>
                            <input type="text" class="form-control" name="university" placeholder="Name of University/Institution">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Graduation Year</label>
                            <input type="number" class="form-control" name="graduation_year" placeholder="e.g., 2020" min="1950" max="2030">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bachelor's Degree</label>
                            <input type="text" class="form-control" name="bachelor_degree" placeholder="e.g., Bachelor of Arts in Economics">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Master's Degree (if applicable)</label>
                            <input type="text" class="form-control" name="master_degree" placeholder="e.g., Master of Business Administration">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Upload Educational Certificates</label>
                            <input type="file" class="form-control" name="educational_certificates" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>
                            <small class="text-muted">Upload degree, diploma, or training certificates</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Other Qualifications</label>
                            <textarea class="form-control" name="other_qualifications" rows="2" placeholder="List any other professional qualifications or certifications"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Family Details Section -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-users me-2"></i>Family Details</h3>
                    
                    <!-- Spouse Details -->
                    <h5 class="text-primary mb-3">Spouse Information</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Spouse Name</label>
                            <input type="text" class="form-control" name="spouse_name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Spouse Date of Birth</label>
                            <input type="date" class="form-control" name="spouse_dob">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Spouse Physical Address</label>
                            <textarea class="form-control" name="spouse_address" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Spouse Current Status</label>
                            <input type="text" class="form-control" name="spouse_status">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Upload Marriage Certificate</label>
                            <input type="file" class="form-control" name="marriage_certificate" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <small class="text-muted">Attach copy of marriage certificate</small>
                        </div>
                    </div>

                    <!-- Father Details -->
                    <h5 class="text-primary mb-3">Father Information</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Father's Name</label>
                            <input type="text" class="form-control" name="father_name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Father's Date of Birth</label>
                            <input type="date" class="form-control" name="father_dob">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Father's Physical Address</label>
                            <textarea class="form-control" name="father_address" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Father's Current Status</label>
                            <input type="text" class="form-control" name="father_status">
                        </div>
                    </div>

                    <!-- Mother Details -->
                    <h5 class="text-primary mb-3">Mother Information</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Mother's Name</label>
                            <input type="text" class="form-control" name="mother_name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mother's Date of Birth</label>
                            <input type="date" class="form-control" name="mother_dob">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mother's Physical Address</label>
                            <textarea class="form-control" name="mother_address" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mother's Current Status</label>
                            <input type="text" class="form-control" name="mother_status">
                        </div>
                    </div>

                    <!-- Children Details -->
                    <h5 class="text-primary mb-3">Children Information</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Child 1 Name</label>
                            <input type="text" class="form-control" name="child1_name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Child 1 Date of Birth</label>
                            <input type="date" class="form-control" name="child1_dob">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Child 2 Name</label>
                            <input type="text" class="form-control" name="child2_name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Child 2 Date of Birth</label>
                            <input type="date" class="form-control" name="child2_dob">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Child 3 Name</label>
                            <input type="text" class="form-control" name="child3_name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Child 3 Date of Birth</label>
                            <input type="date" class="form-control" name="child3_dob">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Child 4 Name</label>
                            <input type="text" class="form-control" name="child4_name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Child 4 Date of Birth</label>
                            <input type="date" class="form-control" name="child4_dob">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Upload Birth Certificates</label>
                            <input type="file" class="form-control" name="birth_certificates" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>
                            <small class="text-muted">Upload birth certificates for children (if applicable)</small>
                        </div>
                    </div>

                </div>

                <!-- Financial Information Section -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-money-check-alt me-2"></i>Financial Information</h3>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Bank Statement Requirements:</strong> Please upload bank statements for the last 6 months that clearly show your name, account number, bank name, consistent transactions, and sufficient funds to cover trip costs.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bank Name</label>
                            <input type="text" class="form-control" name="bank_name" placeholder="e.g., Chase Bank, HSBC, Standard Bank">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Account Number</label>
                            <input type="text" class="form-control" name="account_number" placeholder="Your bank account number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Account Holder Name</label>
                            <input type="text" class="form-control" name="account_holder_name" placeholder="Name as it appears on bank account">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Average Monthly Balance (Local Currency)</label>
                            <input type="number" class="form-control" name="average_monthly_balance" placeholder="0.00" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Upload Bank Statements (Last 6 months)</label>
                            <input type="file" class="form-control" name="bank_statements" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>
                            <small class="text-muted">Upload bank statements showing consistent transactions</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Upload Financial Evidence</label>
                            <input type="file" class="form-control" name="financial_evidence" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>
                            <small class="text-muted">Additional financial documents (investments, assets, etc.)</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Financial Statement/Declaration</label>
                            <textarea class="form-control" name="financial_declaration" rows="3" placeholder="Please provide a brief statement about your financial capacity to cover the trip costs, including estimated trip budget and funding sources"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estimated Trip Budget (Local Currency)</label>
                            <input type="number" class="form-control" name="estimated_trip_budget" placeholder="0.00" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Funding Source</label>
                            <select class="form-select" name="funding_source">
                                <option value="">Select Funding Source</option>
                                <option value="Personal Savings">Personal Savings</option>
                                <option value="Family Support">Family Support</option>
                                <option value="Employer Sponsorship">Employer Sponsorship</option>
                                <option value="Scholarship">Scholarship</option>
                                <option value="Loan">Loan</option>
                                <option value="Investment Returns">Investment Returns</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg px-5 py-3">
                        <i class="fas fa-paper-plane me-2"></i>Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- Registration Form End -->

    <!-- Footer Start -->
    <div class="container-fluid footer py-5 wow fadeIn" data-wow-delay="0.2s">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="footer-item d-flex flex-column">
                        <h4 class="text-secondary mb-4">M25 Travel & Tour Agency</h4>
                        <p class="mb-3">Your trusted partner for visa and immigration services.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="js/main.js"></script>

    <script>
        // Translation function
        function translatePage(lang) {
            var selectField = document.querySelector("select.goog-te-combo");
            if (selectField) {
                selectField.value = lang;
                selectField.dispatchEvent(new Event('change'));
            }
        }

        // Form validation and submission
        document.getElementById('clientForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (isValid) {
                // Show loading spinner
                document.getElementById('spinner').classList.add('show');
                
                // Submit form
                this.submit();
            } else {
                alert('Please fill in all required fields.');
            }
        });

        // Remove spinner on page load
        window.addEventListener('load', function() {
            document.getElementById('spinner').classList.remove('show');
            
            // Scroll to error message if it exists
            const errorAlert = document.querySelector('.alert-danger');
            if (errorAlert) {
                errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Add a subtle shake animation to draw attention
                errorAlert.style.animation = 'shake 0.5s ease-in-out';
            }
        });
        
        // Add shake animation CSS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
