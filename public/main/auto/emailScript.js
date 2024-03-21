// emailScript.js
const nodemailer = require('nodemailer');

// Function to send email
const sendEmail = async () => {
  // Create a Nodemailer transporter
  let transporter = nodemailer.createTransport({
    service: 'gmail',
    auth: {
      user: 'your-email@gmail.com',
      pass: 'your-email-password',
    },
  });

  // Email content
  let mailOptions = {
    from: 'your-email@gmail.com',
    to: 'recipient-email@example.com',
    subject: 'Email Subject',
    text: 'Email Body',
  };

  // Send email
  await transporter.sendMail(mailOptions);
  console.log('Email sent successfully!');
};

// Check condition and send email if true
const checkAndSendEmail = () => {
  // Implement your condition checking logic here
  const conditionMet = true; // Replace with your condition

  if (conditionMet) {
    sendEmail();
  } else {
    console.log('Condition not met. Email not sent.');
  }
};

// Execute the function
checkAndSendEmail();
