# 🚀 Deployment Guide - Event RSVP Generator

This guide will help you deploy your Event RSVP Generator to various hosting platforms.

## 🏠 **Option 1: Local Development Server (Quick Start)**

### Prerequisites
- PHP 7.4 or higher installed
- Web browser

### Steps
1. **Navigate to your project directory:**
   ```bash
   cd "Invitation generator"
   ```

2. **Start the development server:**
   ```bash
   # Option A: Using the shell script
   ./start_server.sh
   
   # Option B: Direct PHP command
   php -S localhost:8000
   
   # Option C: Using the PHP script
   php start_server.php
   ```

3. **Open your browser:**
   - Go to: `http://localhost:8000`
   - You'll be redirected to the Google login page

### Configure Google OAuth (Required)
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project
3. Enable Google+ API
4. Create OAuth 2.0 credentials
5. Add `http://localhost:8000/google_callback.php` to authorized redirect URIs
6. Update `config.php` with your credentials

---

## ☁️ **Option 2: Free Hosting Services**

### **A. Heroku (Recommended for Beginners)**

#### Prerequisites
- [Heroku CLI](https://devcenter.heroku.com/articles/heroku-cli) installed
- Git repository

#### Steps
1. **Login to Heroku:**
   ```bash
   heroku login
   ```

2. **Create Heroku app:**
   ```bash
   heroku create your-rsvp-app-name
   ```

3. **Set environment variables:**
   ```bash
   heroku config:set GOOGLE_CLIENT_ID="your_client_id"
   heroku config:set GOOGLE_CLIENT_SECRET="your_client_secret"
   heroku config:set GOOGLE_REDIRECT_URI="https://your-app-name.herokuapp.com/google_callback.php"
   ```

4. **Deploy:**
   ```bash
   git add .
   git commit -m "Initial deployment"
   git push heroku main
   ```

5. **Open your app:**
   ```bash
   heroku open
   ```

### **B. Railway (Modern Alternative)**

#### Steps
1. Go to [Railway](https://railway.app/)
2. Connect your GitHub repository
3. Set environment variables in Railway dashboard
4. Deploy automatically

### **C. InfinityFree (Free PHP Hosting)**

#### Steps
1. Go to [InfinityFree](https://infinityfree.net/)
2. Create a free account
3. Create a new hosting account
4. Upload files via FTP or File Manager
5. Configure Google OAuth with your domain

---

## 🔧 **Option 3: VPS/Cloud Hosting**

### **A. DigitalOcean Droplet**

#### Steps
1. Create a new Ubuntu droplet
2. Install LAMP stack:
   ```bash
   sudo apt update
   sudo apt install apache2 php mysql-server
   ```

3. Upload files to `/var/www/html/`
4. Configure Apache virtual host
5. Set up SSL with Let's Encrypt

### **B. AWS EC2**

#### Steps
1. Launch EC2 instance (Amazon Linux 2)
2. Install LAMP stack
3. Configure security groups
4. Upload and configure application

---

## ⚙️ **Configuration Steps (All Platforms)**

### 1. Google OAuth Setup
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project
3. Enable Google+ API
4. Go to "Credentials" → "Create Credentials" → "OAuth 2.0 Client IDs"
5. Add authorized redirect URIs:
   - Local: `http://localhost:8000/google_callback.php`
   - Production: `https://yourdomain.com/google_callback.php`

### 2. Update Configuration
Edit `config.php`:
```php
define('GOOGLE_CLIENT_ID', 'your_client_id_here');
define('GOOGLE_CLIENT_SECRET', 'your_client_secret_here');
define('GOOGLE_REDIRECT_URI', 'https://yourdomain.com/google_callback.php');
```

### 3. Set File Permissions
```bash
chmod 755 uploads/
chmod 755 data/
chmod 644 *.php
```

---

## 🔒 **Security Considerations**

### Production Checklist
- [ ] Use HTTPS (SSL certificate)
- [ ] Set secure session cookies
- [ ] Validate all user inputs
- [ ] Use environment variables for secrets
- [ ] Enable error logging
- [ ] Set up backup strategy

### Environment Variables
```bash
# Set these in your hosting environment
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/google_callback.php
```

---

## 🐛 **Troubleshooting**

### Common Issues

1. **OAuth Not Working**
   - Check redirect URI matches exactly
   - Verify Google Cloud Console settings
   - Check browser console for errors

2. **Image Upload Fails**
   - Check directory permissions
   - Verify file size limits
   - Check PHP upload settings

3. **Session Issues**
   - Ensure sessions are enabled
   - Check session directory permissions
   - Verify session configuration

4. **500 Server Error**
   - Check error logs
   - Verify PHP version compatibility
   - Check file permissions

### Debug Mode
Add this to `config.php` for debugging:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

## 📞 **Support**

### Getting Help
1. Check the troubleshooting section
2. Review error logs
3. Test with a simple event first
4. Verify all configuration steps

### Useful Commands
```bash
# Check PHP version
php -v

# Check PHP modules
php -m

# Test PHP server
php -S localhost:8000

# Check file permissions
ls -la
```

---

## 🎉 **Success!**

Once deployed, your Event RSVP Generator will be available at:
- **Local**: `http://localhost:8000`
- **Production**: `https://yourdomain.com`

Users can now:
1. Sign in with Google
2. Create events with images
3. Generate RSVP invitations
4. Track responses in Google Sheets

**Happy Deploying! 🚀** 