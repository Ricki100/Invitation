# Google Apps Script Setup for RSVP System

This guide explains how to set up the Google Apps Script that handles RSVP checking and submission to prevent duplicate responses.

## Step 1: Create Google Apps Script

1. Go to [Google Apps Script](https://script.google.com/)
2. Click "New Project"
3. Replace the default code with the following:

```javascript
function doPost(e) {
  try {
    const params = e.parameter;
    const action = params.action;
    
    if (action === 'check_existing') {
      return checkExistingRSVP(params);
    } else {
      return addNewRSVP(params);
    }
  } catch (error) {
    return ContentService.createTextOutput('Error: ' + error.toString());
  }
}

function checkExistingRSVP(params) {
  const name = params.name;
  const sheetId = params.sheet_id;
  
  if (!name || !sheetId) {
    return ContentService.createTextOutput('Missing parameters');
  }
  
  try {
    const sheet = SpreadsheetApp.openById(sheetId);
    const dataSheet = sheet.getSheets()[0]; // Get first sheet
    const data = dataSheet.getDataRange().getValues();
    
    // Check if name exists in the sheet (assuming name is in first column)
    for (let i = 1; i < data.length; i++) { // Skip header row
      if (data[i][0] && data[i][0].toString().toLowerCase() === name.toLowerCase()) {
        return ContentService.createTextOutput('EXISTS');
      }
    }
    
    return ContentService.createTextOutput('NOT_FOUND');
  } catch (error) {
    return ContentService.createTextOutput('Error: ' + error.toString());
  }
}

function addNewRSVP(params) {
  const name = params.name;
  const rsvp = params.rsvp;
  const phone = params.phone || '';
  
  if (!name || !rsvp) {
    return ContentService.createTextOutput('Missing required parameters');
  }
  
  try {
    // You'll need to replace this with your actual Google Sheet ID
    const sheetId = 'YOUR_SHEET_ID_HERE';
    const sheet = SpreadsheetApp.openById(sheetId);
    const dataSheet = sheet.getSheets()[0];
    
    // Add new row with RSVP data
    const timestamp = new Date().toISOString();
    dataSheet.appendRow([name, rsvp, phone, timestamp]);
    
    return ContentService.createTextOutput('Success');
  } catch (error) {
    return ContentService.createTextOutput('Error: ' + error.toString());
  }
}
```

## Step 2: Deploy the Script

1. Click "Deploy" → "New deployment"
2. Choose "Web app" as the type
3. Set "Execute as" to "Me"
4. Set "Who has access" to "Anyone"
5. Click "Deploy"
6. Copy the Web App URL

## Step 3: Update the PHP Code

Replace the webapp URL in `rsvp.php` with your new deployment URL:

```php
$webapp_url = 'YOUR_DEPLOYMENT_URL_HERE';
```

## Step 4: Configure Your Google Sheet

1. Create a new Google Sheet
2. Add headers in the first row:
   - Column A: "Guest Name"
   - Column B: "Response" 
   - Column C: "Phone"
   - Column D: "Timestamp"
3. Copy the Sheet ID from the URL (the long string between /d/ and /edit)
4. Update the `sheetId` variable in the Apps Script code

## Step 5: Test the Integration

1. Create an event with a Google Sheet link
2. Try to RSVP with a guest name
3. Try to RSVP again with the same guest name - it should show an error
4. Check your Google Sheet to see the RSVP data

## Features

### Duplicate Prevention
- The system checks Google Sheets before allowing new RSVPs
- If a guest has already responded, they'll see an error message
- Only the event organizer can delete records from Google Sheets to allow re-RSVP

### Location Privacy
- Event location is only shown to guests who have accepted the invitation
- Guests who decline or haven't responded yet see "Available after accepting invitation"
- Google Maps link is only provided to accepted guests

### Error Handling
- Graceful handling of missing Google Sheet links
- Clear error messages for users
- Fallback to local storage if Google Sheets is unavailable

## Troubleshooting

### Common Issues

1. **"Error: Access denied"**
   - Make sure the Google Sheet is shared with "Anyone with the link can edit"
   - Check that the Sheet ID is correct

2. **"Error: Sheet not found"**
   - Verify the Sheet ID in the Apps Script code
   - Make sure the sheet exists and is accessible

3. **RSVP not being recorded**
   - Check the Apps Script logs for errors
   - Verify the deployment URL is correct
   - Ensure the sheet has the correct column structure

### Testing the Setup

1. **Test RSVP submission:**
   ```
   POST to your webapp URL with:
   - name: "Test Guest"
   - rsvp: "Accepted"
   - phone: "1234567890"
   ```

2. **Test duplicate check:**
   ```
   POST to your webapp URL with:
   - action: "check_existing"
   - name: "Test Guest"
   - sheet_id: "your_sheet_id"
   ```

## Security Notes

- The Google Sheet should be shared with appropriate permissions
- Consider adding authentication to the Apps Script for production use
- Regularly backup your RSVP data
- Monitor the Apps Script logs for any issues

## Customization

You can modify the Apps Script to:
- Add additional fields (dietary restrictions, number of guests, etc.)
- Send email notifications when RSVPs are received
- Generate reports or summaries
- Integrate with other Google services 