
<?php
// Include WordPress functions
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Your reCAPTCHA secret key
$recaptcha_secret = '6Lfm8UEqAAAAAPJBL47zQFbo_4Fp0FbMTnBRDzfV';

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Verify reCAPTCHA
    $recaptcha_response = $_POST['g-recaptcha-response'];
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    
    $response = wp_remote_post($recaptcha_url, array(
        'body' => array(
            'secret' => $recaptcha_secret,
            'response' => $recaptcha_response
        )
    ));

    $response_keys = json_decode(wp_remote_retrieve_body($response), true);

    // Check if reCAPTCHA validation passed
    if ($response_keys['success']) {
        // Sanitize form inputs
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $description = sanitize_textarea_field($_POST['description']);
        $using_kinetic = sanitize_text_field($_POST['using-kinetic']);

        // Additional fields (if "Yes" is selected for using Epicor Kinetic)
        if ($using_kinetic === 'yes') {
            $kinetic_version = sanitize_text_field($_POST['kinetic-version']);
            $hosting = sanitize_text_field($_POST['hosting']);
            $number_of_users = sanitize_text_field($_POST['number-of-users']);
            $industry = sanitize_text_field($_POST['industry']);
        }

        // Email to Admin
        $to = 'info@epicforcetech.com';  // Main recipient
        $subject = 'New Form Submission';
        $message = "Name: $name\n";
        $message .= "Email: $email\n";
        $message .= "Phone: $phone\n";
        $message .= "Description: $description\n";
        $message .= "Using Epicor Kinetic: $using_kinetic\n";
        
        // Include additional fields if the user selected "Yes"
        if ($using_kinetic === 'yes') {
            $message .= "Kinetic Version: $kinetic_version\n";
            $message .= "Epicor Hosting: $hosting\n";
            $message .= "Number of Users: $number_of_users\n";
            $message .= "Industry: $industry\n";
        }

        // Send email to admin
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: Epicforce Tech <info@epicforcetech.com>',
            'Cc: hafeez.syed@epicforcetech.com, muhammad.sarfaraz@epicforcetech.com, muhammad.ali@epicforcetech.com'
        );
        
        wp_mail($to, $subject, $message, $headers);

        // Step 2: Send Confirmation Email to the User
        $user_subject = 'Thank You for Your Submission';
        $user_message = "Dear $name,\n\n";
        $user_message .= "Thank you for submitting the form. You can explore more of our services by viewing our portfolio here:\n";
        $user_message .= "Epicforce Tech Portfolio: https://epicforcetech.com/wp-content/uploads/2024/09/Epicforce-Tech-Portfolio.pdf\n\n";
        $user_message .= "We will get back to you shortly.\n\nBest Regards,\nEpicforce Tech Team";
        
        // Headers for the user's email with the "From" name
        $user_headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: Epicforce Tech <info@epicforcetech.com>'
        );
        
        // Send email to the user
        wp_mail($email, $user_subject, $user_message, $user_headers);

        // Redirect to thank you page
        wp_redirect('https://epicforcetech.com/contact-us/');  // Replace with your Thank You page URL
        exit;
    } else {
        echo '<script>alert("reCAPTCHA verification failed. Please try again.");</script>';
    }
}
?>