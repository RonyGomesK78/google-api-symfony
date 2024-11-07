
### 3. Google API Setup

1. **Create a Google Cloud Project**
   - Go to the [Google Cloud Console](https://console.cloud.google.com/).
   - Create a new project or select an existing project.
   
2. **Enable the Required APIs**
   - Navigate to **APIs & Services** > **Library**.
   - Enable both the **Gmail API** and **Google Drive API** for this project.

3. **Configure OAuth Consent Screen**
   - Go to **APIs & Services** > **OAuth consent screen**.
   - Select the appropriate **User Type** (usually **External** if other users will access it).
   - Fill in the required information, such as the app name, support email, and developer contact information.
   - In the **Scopes** section, add the following:
     - For Gmail: `https://www.googleapis.com/auth/gmail.readonly`
     - For Google Drive: `https://www.googleapis.com/auth/drive.metadata.readonly`
   - Complete the OAuth consent screen setup.

4. **Create OAuth 2.0 Credentials**
   - Go to **APIs & Services** > **Credentials**.
   - Click **Create Credentials** and select **OAuth Client ID**.
   - Choose **Web Application** as the application type.
   - Under **Authorized redirect URIs**, add the following URI to match your Symfony project’s callback endpoint:
     - `http://127.0.0.1:8000/google/callback`
   - Click **Create** to generate the OAuth client ID and secret.

5. **Download Credentials**
   - Once created, download the `credentials.json` file.
   - Place the file in your Symfony project directory under `config/google/credentials.json`.

6. **Setting Up Test Users**
   - If your OAuth consent screen is not in **Production** mode, add test user emails.
   - Go to **OAuth consent screen** > **Test users** and add the email addresses of users who will help with testing the application.

7. **Validate and Test Scopes**
   - Make sure you have the necessary scopes configured in your Symfony services to avoid permission issues:
     - In your Gmail service, include the `https://www.googleapis.com/auth/gmail.readonly` scope.
     - In your Google Drive service, include the `https://www.googleapis.com/auth/drive.metadata.readonly` scope.
   - Verify that the token refresh logic in your code updates tokens as needed for these scopes.

8. **Using User Emails for Testing**
   - When testing the application, you can use the authorized test users' email addresses to verify Gmail and Google Drive functionalities.
   - Ensure these users have permitted the application to access their Google data to fetch emails and files as intended.

After completing these steps, your Google API setup will be ready to interact with Gmail and Google Drive in your Symfony project. Make sure the `credentials.json` is not exposed in any public repository, as it contains sensitive information for API access.
