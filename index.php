<?php include "db.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H2O</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <form action="login.php" method="POST">
        
    <div class="container">
        <!--left-->
        <div class="left">
            <img src="assets/logo_name.png" alt="logo" class="logo">

            <div class="forgot-box"><img src="assets/peek-water.png" class="peek-water" alt="water mascot"></div>

            <form action="login.php" method="POST">

                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>

                <div class="login-links">
                    <a href="forgot_password.php">Forgot Password?</a>
                    <p>Don't have an account? <a href="register.php">Sign up</a></p>
                </div>

                <div class="tnc">
                    <input type="checkbox" id="tnc" name="tnc" disabled>
                    <label for="tnc">I agree to the <a href="#" id="openTerms">Terms and Conditions</a></label>
                </div>

                <button type="submit" id="lgnBtn" disabled>Sign in</button>

            </form>
        </div>

        <!--right-->
        <div class="right">
            <div class="overlay">
                <img src="assets/logo_whole.png" alt="logo" class="logo">
            </div>
        </div>
    </div> 

    <!-- terms n condition n policy -->
    <div id="termsMod" class="modal">
        <div class="mContent">
            <span class="close">&times;</span>
            <div id="page1">
                <h2>TERMS AND CONDITIONS</h2>
                <p class="modal-sub">Hydro Operations Hub (H.O.H) System</p>
                <div id="termT" class="scrollBox">
                    <ol>
                        <li>
                            <strong>General Provisions</strong>
                            <ol>
                                <li>These Terms and Conditions govern access to and use of the Hydro Operations Hub (H.O.H) Water Billing and Collection System (“the System”).</li>
                                <li>By accessing or using the System, all users agree to be legally bound by these Terms and applicable laws of the Philippines.</li>
                                <li>The System shall be used solely for lawful purposes related to water billing, payment, and management.</li>
                            </ol>
                        </li>

                        <li>
                            <strong>Legal Compliance</strong>
                            <ol>
                                <li>The System shall operate in compliance with all applicable Philippine laws, including but not limited to:
                                    <ul>
                                        <li>Data Privacy Act of 2012</li>
                                        <li>Electronic Commerce Act of 2000</li>
                                        <li>Consumer Act of the Philippines</li>
                                    </ul>
                                </li>
                                <li>All users are required to comply with these laws when accessing and using the System.</li>
                            </ol>
                        </li>

                        <li>
                            <strong>Account Registration and Authentication</strong>
                            <ol>
                                <li>Users shall register through the Authentication Module using accurate and complete information.</li>
                                <li>The System shall implement reasonable and appropriate security measures to protect user accounts.</li>
                                <li>Users are responsible for safeguarding their credentials and shall be liable for activities conducted under their accounts.</li>
                                <li>Unauthorized access or misuse shall be subject to sanctions and possible legal action under applicable Philippine laws.</li>
                            </ol>
                        </li>

                        <li>
                            <strong>Data Privacy and Protection</strong>
                            <ol>
                                <li>The System adheres to the principles of transparency, legitimate purpose, and proportionality as mandated by the Data Privacy Act of 2012.</li>
                                <li>Personal data collected (e.g., name, address, billing information, payment details) shall be used solely for legitimate system operations such as billing, payment processing, and account management.</li>
                                <li>Users shall be informed of how their data is collected, processed, and stored.</li>
                                <li>The System shall implement appropriate organizational, physical, and technical security measures to protect personal data.</li>
                                <li>Data subjects (users) have the right to:
                                    <ul>
                                        <li>Access their personal data</li>
                                        <li>Request correction of inaccurate data</li>
                                        <li>Object to processing when applicable</li>
                                        <li>Request deletion or blocking of data, subject to legal limitations</li>
                                    </ul>
                                </li>
                                <li>Any data breach shall be handled in accordance with guidelines issued by the National Privacy Commission.</li>
                            </ol>
                        </li>

                        <li>
                            <strong>Roles and Responsibilities of Users</strong>
                            <ol>
                                <li>
                                    Customers
                                    <ol>
                                        <li>May access billing information, usage history, and payment services through the Customer Portal Module.</li>
                                        <li>Must ensure that all submitted personal and payment information is accurate and lawful.</li>
                                    </ol>
                                </li>
                                <li>
                                    Field Agents
                                    <ol>
                                        <li>Shall record and submit meter readings accurately and in real time using the Meter Reading Management Module.</li>
                                        <li>Shall be accountable for the integrity and correctness of submitted data.</li>
                                    </ol>
                                </li>
                                <li>
                                    Accountants
                                    <ol>
                                        <li>Shall verify payments, issue receipts, and generate financial reports.</li>
                                        <li>Must ensure compliance with applicable accounting and auditing standards in the Philippines.</li>
                                    </ol>
                                </li>
                                <li>
                                    Administrators
                                    <ol>
                                        <li>Shall manage user accounts and oversee system operations.</li>
                                        <li>Are responsible for enforcing security measures and ensuring compliance with applicable laws.</li>
                                    </ol>
                                </li>
                            </ol>
                        </li>

                        <li>
                            <strong>Meter Reading and Data Integrity</strong>
                            <ol>
                                <li>All submitted meter readings shall serve as the official basis for billing.</li>
                                <li>Any falsification or manipulation of data shall be subject to disciplinary action and may constitute a violation of applicable laws.</li>
                            </ol>
                        </li>

                        <li>
                            <strong>Billing and Payment Processing</strong>
                            <ol>
                                <li>Bills shall be automatically generated based on recorded readings and approved rate schedules.</li>
                                <li>Electronic transactions and records shall be recognized as valid under the Electronic Commerce Act of 2000.</li>
                                <li>Customers are obligated to settle payments within the prescribed period.</li>
                            </ol>
                        </li>

                        <li>
                            <strong>System Availability and Maintenance</strong>
                            <ol>
                                <li>The System shall aim for continuous availability but may be subject to downtime due to maintenance or unforeseen circumstances.</li>
                                <li>The System shall not be held liable for temporary service interruptions beyond reasonable control.</li>
                            </ol>
                        </li>

                        <li>
                            <strong>Prohibited Acts</strong>
                            <p>Users shall not:</p>
                            <ol>
                                <li>Access the System without authorization;</li>
                                <li>Input false or misleading data;</li>
                                <li>Interfere with system operations;</li>
                                <li>Engage in fraudulent or unlawful activities.</li>
                            </ol>
                            <p>Violations may result in account termination and legal action under Philippine law.</p>
                        </li>

                        <li>
                            <strong>Limitation of Liability</strong>
                            <ol>
                                <li>The System shall not be liable for damages arising from:
                                    <ul>
                                        <li>User negligence or misuse;</li>
                                        <li>Incorrect or incomplete data provided by users;</li>
                                        <li>External technical failures or cyber incidents beyond control.</li>
                                    </ul>
                                </li>
                            </ol>
                        </li>

                        <li>
                            <strong>Amendments and Modifications</strong>
                            <ol>
                                <li>The System reserves the right to amend these Terms at any time, subject to applicable legal requirements.</li>
                                <li>Continued use of the System constitutes acceptance of the revised Terms.</li>
                            </ol>
                        </li>

                        <li>
                            <strong>Governing Law and Jurisdiction</strong>
                            <ol>
                                <li>These Terms shall be governed by the laws of the Philippines.</li>
                                <li>Any disputes arising shall be subject to the jurisdiction of the appropriate courts of the Philippines.</li>
                            </ol>
                        </li>

                        <li>
                            <strong>Contact and Complaints</strong>
                            <ol>
                                <li>For concerns regarding data privacy, users may coordinate with the System Administrator or the National Privacy Commission.</li>
                                <li>Technical concerns and system issues may be reported to the designated support team.</li>
                            </ol>
                        </li>

                    </ol>
                </div>
                <button type="button" id="nextPageBtn" class="modal-btn">Next: Privacy Policy</button>
            </div>

            <div id="page2" style="display: none;">
                <h2>PRIVACY NOTICE</h2>
                <div id="privacyBox" class="scrollBox">
                    <ol>
                        <li>
                            <strong>Introduction</strong>
                            <p>
                                Hydro Operations Hub (H.O.H) respects your privacy and is committed to protecting your personal data in accordance with the Data Privacy Act of 2012.
                            </p>
                        </li>

                        <li>
                            <strong>Data Collection</strong>
                            <p>We may collect the following personal information:</p>
                            <ul>
                                <li>Name and contact details</li>
                                <li>Address and account information</li>
                                <li>Billing and payment records</li>
                                <li>System usage data</li>
                            </ul>
                        </li>

                        <li>
                            <strong>Purpose of Processing</strong>
                            <p>Your data is used for:</p>
                            <ul>
                                <li>Account registration and authentication</li>
                                <li>Water billing and meter reading management</li>
                                <li>Payment processing and verification</li>
                                <li>Providing billing history and usage tracking</li>
                            </ul>
                        </li>

                        <li>
                            <strong>Data Protection</strong>
                            <p>
                                Your data is securely stored and protected using appropriate technical and organizational measures to prevent unauthorized access, disclosure, or misuse.
                            </p>
                        </li>

                        <li>
                            <strong>Data Sharing</strong>
                            <p>
                                We do not share your personal information with third parties unless required by law or necessary for system operations.
                            </p>
                        </li>

                        <li>
                            <strong>User Rights</strong>
                            <p>You have the right to:</p>
                            <ul>
                                <li>Access your personal data</li>
                                <li>Request corrections</li>
                                <li>Request deletion (subject to legal limits)</li>
                                <li>Object to certain data processing</li>
                            </ul>
                        </li>

                        <li>
                            <strong>Retention of Data</strong>
                            <p>
                                Personal data will be retained only for as long as necessary to fulfill its purpose and comply with legal obligations.
                            </p>
                        </li>

                        <li>
                            <strong>Amendments</strong>
                            <p>
                                This Privacy Policy may be updated from time to time. Continued use of the system means you accept any changes.
                            </p>
                        </li>

                        <li>
                            <strong>Consent</strong>
                            <p>
                                By proceeding, you confirm that you have read, understood, and agreed to this Privacy Policy.
                            </p>
                        </li>
                    </ol>

                    <p>
                    <strong>I agree to the Terms and Conditions and Privacy Policy, and consent to data processing.</strong>
                    </p>
                </div>
                    <button id="aksipT" class="modal-btn" disabled>I agree</button>
            </div>
        </div>
    </div>

    <script src="script.js"></script>

    
</body>
</html>