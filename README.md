# Event RSVP Invitation Generator

A lightweight PHP application that generates unique RSVP invitation links and QR codes for events. Each guest gets their own personalized RSVP link that can be shared via email, text, or QR code.

## ✨ New Features

- 🔐 **Google OAuth Login** - Secure authentication with Google accounts
- 🖼️ **Image Upload** - Add beautiful images to your event invitations
- 👤 **User Accounts** - Each user manages their own events
- 📱 **Enhanced UI** - Modern, responsive design with drag-and-drop uploads
- 🎨 **Event Images** - Display custom images in RSVP forms

## Features

- ✅ **Easy Setup**: Just paste your Google Sheet link
- ✅ **Unique Guest Links**: Each guest gets their own RSVP link
- ✅ **QR Code Generation**: Automatic QR codes for easy mobile access
- ✅ **Beautiful UI**: Modern, responsive design with Bootstrap
- ✅ **No Database Required**: Uses Google Sheets for data storage
- ✅ **Copy-to-Clipboard**: Easy sharing of RSVP links
- ✅ **Google OAuth**: Secure login with Google accounts
- ✅ **Image Upload**: Add custom images to invitations
- ✅ **User Dashboard**: Manage all your events in one place

## How It Works

1. **Sign In**: Login with your Google account
2. **Create Event**: Enter event details, upload image, and add guest list
3. **Generate Links**: Get unique RSVP links for each guest
4. **Share**: Send links or QR codes to your guests
5. **Collect RSVPs**: Guests respond via the personalized links
6. **Track Responses**: View all RSVPs in your Google Sheet

## Setup Instructions

### 1. Google OAuth Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable the Google+ API
4. Go to "Credentials" and create OAuth 2.0 Client ID
5. Add your domain to authorized redirect URIs
6. Copy Client ID and Client Secret

### 2. Configure the Application

1. Edit `config.php` and update:
   ```php
   define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID');
   define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
   define('GOOGLE_REDIRECT_URI', 'http://yourdomain.com/google_callback.php');
   ```

### 3. Prepare Your Google Sheet

1. Create a new Google Sheet
2. Add columns for tracking RSVPs (e.g., Guest Name, Response, Email, Notes, Date)
3. Share the sheet (make it editable by anyone with the link)
4. Copy the sheet URL

### 4. Install the Application

1. Upload all PHP files to your web server
2. Ensure PHP is installed and configured
3. Make sure the web server can write to the directory (for sessions and uploads)
4. Set proper permissions for the `uploads/` and `data/` directories

## File Structure

```
/Invitation generator/
├── index.php              # Main redirect page
├── google_login.php       # Google OAuth login page
├── google_callback.php    # OAuth callback handler
├── dashboard.php          # User dashboard
├── create_event.php       # Event creation form
├── event_results.php      # Generated RSVP links page
├── rsvp.php              # Guest RSVP form
├── logout.php            # Logout handler
├── config.php            # Configuration file
├── uploads/              # Uploaded images directory
├── data/                 # User data directory
└── README.md             # This file
```

## Usage

### For Event Organizers

1. **Login**: Visit the app and sign in with Google
2. **Dashboard**: View your events or create a new one
3. **Create Event**: 
   - Enter event details and Google Sheet link
   - Upload an optional event image
   - Add guest names (one per line)
4. **Generate**: Click "Generate RSVP Invitations"
5. **Share**: Copy links or QR codes for each guest

### For Guests

1. **Receive**: Get your unique RSVP link
2. **Click**: Visit the link or scan QR code
3. **View**: See event details and image (if provided)
4. **Respond**: Select Yes/No/Maybe and add optional details
5. **Submit**: Your response is recorded

## Image Upload Features

- **Supported Formats**: JPG, PNG, GIF
- **Max Size**: 5MB per image
- **Drag & Drop**: Easy upload interface
- **Preview**: See image before uploading
- **Display**: Images appear in RSVP forms

## Customization

### Styling
- Modify CSS in the `<style>` sections of each file
- Change colors, fonts, and layout as needed
- Bootstrap classes are used for responsive design

### RSVP Form Fields
Edit `rsvp.php` to add or modify form fields:
- Dietary restrictions
- Number of guests
- Special requests
- Contact information

### Google Forms Integration
To connect with Google Forms:

1. Get your form's POST URL
2. Find the entry IDs for each field
3. Update the commented section in `rsvp.php`
4. Uncomment and configure the cURL submission code

## Example Google Sheet Structure

| Guest Name | Response | Email | Notes | Date Submitted |
|------------|----------|-------|-------|----------------|
| John Smith | Yes      | john@email.com | Vegetarian | 2024-01-15 |
| Jane Doe   | No       | jane@email.com | Out of town | 2024-01-15 |

## Security Features

- **Google OAuth**: Secure authentication
- **Session Management**: Secure session handling
- **File Validation**: Image upload security
- **Input Sanitization**: XSS protection
- **CSRF Protection**: Form security

## Troubleshooting

### Common Issues

1. **OAuth Not Working**: Check Google Cloud Console settings
2. **Image Upload Fails**: Check directory permissions
3. **QR Codes Not Loading**: Check internet connection
4. **Session Errors**: Ensure PHP sessions are enabled
5. **Form Not Submitting**: Check Google Form configuration

### Server Requirements

- PHP 7.4 or higher
- Web server (Apache, Nginx, etc.)
- Internet connection (for QR code generation and OAuth)
- Write permissions for uploads/ and data/ directories

### File Permissions

```bash
chmod 755 uploads/
chmod 755 data/
chmod 644 *.php
```

## Security Notes

- This application uses Google OAuth for secure authentication
- User data is stored in JSON files (consider database for production)
- Image uploads are validated for security
- All user inputs are sanitized
- Consider adding rate limiting for production use

## Support

For issues or questions:
1. Check the troubleshooting section
2. Verify your Google Cloud Console settings
3. Check file permissions
4. Test with a simple event first

## License

This project is open source and available under the MIT License.

---

**Happy Event Planning! 🎉** 