# Moxter

A small mail proxy web app to use with SPA's without api keys.  
  
## Setup

Copy the `.env-example` file in the root and name the copy `.env`. Updated the variables which you wish to change so they
correspond to your server and settings.

### Environment variables

The following variables are defined in the configuration and should be set with
your information and wanted behaviour.

```
SMTP_SERVER=mail.example.com
SMTP_PORT=587
SMTP_USER=user@example.com
SMTP_PASSWORD=secret_password
DOMAINS=/https:\/\/(.*\.)?(.*)(\.(.*))/
DEBUG=false
APP_ENV=production
EMAIL_CONSTRAINT=/(.*)@(.*)/
HTML_EMAILS=false
SENDER=my@email.tdl
```

**SMTP_SERVER**  
The smtp server which is to be used for sending emails.

**SMTP_PORT**  
The port that the smtp server listens to. This have to be a open port if you run the application on another server than
the same as the SMTP server.

**SMTP_USER**  
The username used when authenticating with the smtp server.  
This is usually a email address.

**SMTP_PASSWORD**  
Password used to auth with the smtp server.

**DOMAINS**  
A regular expression to use for cross site scripting.  
If the requesting origin matches the regex, it will allow for requests, else it will not.

**DEBUG**  
If true, the server will output debug information to the log file (`log.txt`).

**APP_ENV**  
Set this to production when running on a live server, if set to development, it will allow for x-site scripting and 
ignore any origin constraints and such.

**EMAIL_CONSTRAINT**  
A regex string to set a constraint to email addresses that the app will allow to send emails to.

**HTML_EMAILS**  
If you wish to allow the emails being sent by the API to be able to use HTML. Setting this to true will make all
emails behave as html emails.

**SENDER**  
The email address of the sender. Will be used on all emails sent by the api.
