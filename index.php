<?php include "db.php"; ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H2O</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">

        <!--eto ung makikita sa left kaya nga left eh-->
        <div class="left">
            <img src="assets/spongy.png" alt="logo" class="logo">

            <form action="login.php" method="POST">

                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>

                    
                <p>Don't have an account? <a href="register.php">Sign up</a></p>

                <div class="tnc">
                    <input type="checkbox" id="tnc" name="tnc" disabled>
                    <label for="tnc">I agree to the <a href="#" id="openTerms">Terms and Conditions</a></label>
                </div>

                <button type="submit" id="lgnBtn" disabled>Sign in</button>

            </form>
        </div>

        <!--eto ung makikita sa right kaya nga right eh-->
        <div class="right">
            <div class="overlay">
                <h2>Steady Flow, <br>Easy Go</h2>
            </div>
        </div>
    </div> 


    <!-- terms n condition n -->
    <div id="termsMod" class="modal">
        <div class="mContent">
            <span class="close">&times;</span>
            <h2>TERMS AND CONDITIONS <br>Hydro Operations Hub (H.O.H) Water Billing and Collection System</h2>
            
            <div id="termT">

                <ol>
                    <li><strong>General Provisions</strong>
                        <ol>
                            <li>These Terms and Conditions govern access to and use of the Hydro Operations Hub (H.O.H) Water Billing and Collection System (“the System”).</li>
                            <li>By accessing or using the System, all users agree to be legally bound by these Terms and applicable laws of the Philippines.</li>
                            <li>The System shall be used solely for lawful purposes related to water billing, payment, and management.</li>
                        </ol>
                    </li> 

                    <li><strong>Legal Compliance</strong> 
                        <ol>
                            <li>The System shall operate in compliance with all applicable Philippine laws, including but not limited to:</li>
                        
                            <ul>
                                <li> Data Privacy Act of 2012</li> 
                                <li>Electronic Commerce Act of 2000</li>
                                <li>Consumer Act of the Philippines</li> 
                            </ul>

                            <li>All users are required to comply with these laws when accessing and using the System.</li>
                        </ol>
                    </li>
                    
                    <li><strong>Account Registration and Authentication</strong>
                        <ol>
                            <li>Users shall register through the Authentication Module using accurate and complete information.</li>
                            <li>The System shall implement reasonable and appropriate security measures to protect user accounts.</li>
                            <li>Users are responsible for safeguarding their credentials and shall be liable for activities conducted under their accounts.</li>
                            <li>Unauthorized access or misuse shall be subject to sanctions and possible legal action under applicable Philippine laws.</li>
                        </ol>
                    </li>
                    <li><strong>Data Privacy Protection</strong>
                        <ol>
                            <li>The System adheres to the principles of transparency, legitimate purpose, and proportionality as mandated by the Data Privacy Act of 2012.</li>
                            <li>Personal data collected (e.g., name, address, billing information, payment details) shall be used solely for legitimate system operations such as billing, payment processing, and account management.</li>
                            <li>Users shall be informed of how their data is collected, processed, and stored.</li>
                            <li>The System shall implement appropriate organizational, physical, and technical security measures to protect personal data.</li>
                            <li>Data subjects (users) have the right to:</li>
                            <ul>
                                <li>Access their personal data</li>
                                <li>Request correction of inaccurate data</li>
                                <li> Object to processing when applicable</li>
                                <li>Request deletion or blocking of data, subject to legal limitations</li>
                            </ul>
                            <li> Any data breach shall be handled in accordance with guidelines issued by the National Privacy Commission.</li>
                        </ol>
                    </li>

                    <li><strong>Roles and Responsibilities of Users</strong>
                        <ol>
                            <li><strong> Customers</strong>
                                <ol>
                                    <li>May access billing information, usage history, and payment services through the Customer Portal Module</li>
                                    <li>Must ensure that all submitted personal and payment information is accurate and lawful.</li>
                                </ol>
                            </li>
                            <li><strong>Field Agents</strong>
                                <ol>
                                    <li>Shall record and submit meter readings accurately and in real time using the Meter Reading Management Module.</li>
                                    <li>Shall be accountable for the integrity and correctness of submitted data.</li>
                                </ol>
                            </li>
                            <li><strong>Accountants</strong>
                                <ol>
                                    <li> Shall verify payments, issue receipts, and generate financial reports.</li>
                                    <li>Must ensure compliance with applicable accounting and auditing standards in the Philippines.</li>
                                </ol>
                            </li>
                            <li><strong>Administrators</strong>
                                <ol>
                                    <li> Shall manage user accounts and oversee system operations.</li> 
                                    <li>Are responsible for enforcing security measures and ensuring compliance with applicable laws.</li>
                                </ol> ano english ng wag ka makikiapid thou shall not nakikiapid
                            </li>
                        </ol> 
                    </li>
                    <li><strong>Meter Reading and Data Integrity</strong>
                        <ol>
                            <li>All submitted meter readings shall serve as the official basis for billing.</li>
                            <li>Any falsification or manipulation of data shall be subject to disciplinary action and may constitute a violation of applicable laws.</li>
                        </ol>
                    </li>
                    <li><strong>Billing and Payment Processing</strong>
                        <ol>
                            <li>Bills shall be automatically generated based on recorded readings and approved rate schedules.</li>
                            <li>Electronic transactions and records shall be recognized as valid under the Electronic Commerce Act of 2000.</li>
                            <li>Customers are obligated to settle payments within the prescribed period.</li>
                        </ol>
                    </li>
                    <li><strong>System Availability and Maintenance</strong>
                        <ol>
                            <li></li>
                            <li></li>
                        </ol>
                    </li>
                    <li><strong> Prohibited Acts</strong>
                        <ol>
                            <li></li>
                            <li></li>
                        </ol>
                    </li>
                    <li><strong> Limitation of Liability</strong>
                        <ol>
                            <li></li>
                            <li></li>
                        </ol>
                    </li>
                    <li><strong>Amendments and Modifications</strong>
                        <ol>
                            <li></li>
                            <li></li>
                        </ol>
                    </li>
                    <li><strong>Governing Law and Jurisdiction</strong>
                        <ol>
                            <li></li>
                            <li></li>
                        </ol>
                    </li>
                    <li><strong>Contact and Complaints</strong>
                        <ol>
                            <li></li>
                            <li></li>
                        </ol>
                    </li> 
                        
                </ol>
            </div> 

            <p id="scrllMsg"> scroll to accept</p>
            <button id="aksipT" disabled>Yuz I do</button>

        </div>
    </div>

    <script src="script.js"></script>

    
</body>
</html>