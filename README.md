# PHP & HTML Email Template System (Dark Mode Ready)
A robust, modular, and Dark Mode compatible email sending system using PHP. This template allows developers to send professional HTML emails with customizable colors and auto-responders, easily integrating into any Static HTML or React website.

### 🚀 Features

• Dark Mode Ready: Emails automatically adapt to the user's device theme (Light/Dark).

• Modular Design: Separate HTML files for "Owner Notification" and "User Auto-reply".

• Easy Configuration: Change button colors and site names from a single PHP file.

• Secure: Modern input sanitization to prevent XSS and injection attacks.

• React & HTML Compatible: Works with standard HTML forms and React `fetch` requests.
#
### 📂 File Structure
Copy these files into your server's root directory (e.g., `public_html/` or `dist/`):
```

/your-website-root
│
├── send-mail.php             <-- The main logic script
├── Email-templates/          <-- Folder containing templates
│    ├── owner.html           <-- Email sent to YOU (the business owner)
│    └── user.html            <-- Auto-reply sent to the CUSTOMER
│
└── index.html                <-- Your website file

```
#
### ⚙️ Configuration
Open `send-mail.php` and edit the top configuration section:
```

// 1. Company Name (Used in email subject and footer)
$company_arun = "Your Company Name";

// 2. Owner Email (Where inquiries will be sent)
$owner_email_to = "your-email@gmail.com";

```
#
### 💻 Integration Guide
**Option A: Using with Static HTML**
If you have a standard HTML website, follow these steps:
**1. Add IDs and Names to your Form Inputs** Ensure your  `<input>` tags have the `name` attribute matching the PHP variables (`name`, `email`, `phone`, `company`, `message`). Add `id="contactForm"` to the form tag.

```

<form id="contactForm" action="send-mail.php" method="POST">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email Address" required>
    <input type="tel" name="phone" placeholder="Phone Number">
    <input type="text" name="company" placeholder="Company (Optional)">
    <textarea name="message" placeholder="Your Message" required></textarea>
    
    <button type="submit" id="submitBtn">Send Message</button>
    
    <div id="formMessage" style="display:none; margin-top:10px;"></div>
</form>

```
#
**2. Add the AJAX Script** Place this script at the bottom of your HTML file, just before the closing `</body>` tag. This handles the submission without reloading the page.
```

<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("contactForm");
    const messageDiv = document.getElementById("formMessage");
    const submitBtn = document.getElementById("submitBtn");

    form.addEventListener("submit", function(e) {
        e.preventDefault(); 

        // Disable button & show loading text
        submitBtn.disabled = true;
        const originalText = submitBtn.innerText;
        submitBtn.innerText = "Sending...";
        messageDiv.style.display = "none";

        const formData = new FormData(form);

        fetch("send-mail.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                messageDiv.innerHTML = '<span style="color: green;">' + data.message + '</span>';
                form.reset();
            } else {
                messageDiv.innerHTML = '<span style="color: red;">' + data.message + '</span>';
            }
        })
        .catch(error => {
            console.error("Error:", error);
            messageDiv.innerHTML = '<span style="color: red;">Something went wrong. Please try again.</span>';
        })
        .finally(() => {
            messageDiv.style.display = "block";
            submitBtn.disabled = false;
            submitBtn.innerText = originalText;
            
            // Hide success message after 5 seconds
            setTimeout(() => {
                if(messageDiv.innerHTML.includes("green")) messageDiv.style.display = "none";
            }, 5000);
        });
    });
});
</script>

```
#
**Option B: Using with React (Vite / Next.js)**
If you are using React, you do not need the HTML script above. Instead, handle the submission in your component.

**1. Update your** `handleSubmit` function Inside your Contact component (e.g., `contact.tsx`), update the fetch logic to point to the PHP file in your public/dist folder.

**Important:** React sends data as JSON, which the PHP script automatically detects and handles.
```

const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);

    try {
        const response = await fetch('/send-mail.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json', // PHP script detects this header
            },
            body: JSON.stringify(formData), // Send state object directly
        });

        const result = await response.json();

        if (result.status === 'success') {
            alert('Message Sent Successfully!');
            setFormData({ name: '', email: '', phone: '', company: '', message: '' });
        } else {
            alert('Error: ' + result.message);
        }

    } catch (error) {
        console.error('Error:', error);
        alert('Failed to send message.');
    } finally {
        setIsSubmitting(false);
    }
};

```
**2. Build & Deploy** When using React, run your build command (`npm run build`). Then, **manually copy** `send-mail.php` and the `Email-templates` folder into your final build folder (e.g., `dist/` or `build/`) before uploading to your hosting provider.
#
### 🎨 Customizing the Emails
1. *Colors:* Open `Email-templates/owner.html` or `user.html`.

    • Find the `<style>` block at the top.

    • Edit `.config-btn-bg` to change the button color.

2. **Logo:** Replace `logo.webp` in your root folder, or update the image path in the HTML templates.

3. **Placeholders:** You can use these variables in your HTML templates:

    • `{{name}}` - User's Name

    • `{{phone}}` - Phone Number
  
    • `{{email}}` - Email Address

    • `{{company}}` - Company Name

    • `{{message}}` - The message body

    • `{{site_name}}` - Your Company Name (set in PHP config)

    • `{{server_url}}` - Your website URL (auto-detected)
  #
  ### Developed by ARUNARUDRA
