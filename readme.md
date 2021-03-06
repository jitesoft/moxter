# Moxter

A small mail proxy/relay to use enable email services without api keys.

**Observe** Using moxter is not the most secure way to send emails, but for a SPA or similar, without any real backend, 
using a proxy/relay for this is better than adding client secrets in the front-end!   
  
If you wish to make the application more secure, using a proxy with external auth (such as traefik) might be a good idea!  
  
## Setup

Copy the `.env-example` file in the root and name the copy `.env`. Updated the variables which you wish to change so they
correspond to your server and settings.

### Environment variables

The following variables are defined in the configuration and should be set with
your information and wanted behaviour.

```
LOG_FILE=/tmp/moxter.log
SMTP_SERVER=mail.example.com
SMTP_PORT=587
SMTP_AUTH=false
SMTP_USER=user@example.com
SMTP_PASSWORD=secret_password
SMTP_DEBUG=false
DEBUG=false
APP_ENV=production
SMTP_INSECURE=false
SIGN_CERT=/path/to/cert.crt
SIGN_KEY=/path/to/key.key
TLS=false
DOMAINS=/https:\/\/(.*\.)?(.*)(\.(.*))/,https://example.com
EMAIL_CONSTRAINT=/(.*)@(.*)/
HTML_EMAILS=false
SENDER=my@email.tdl
```

**LOG_FILE**  
Absolute path to the file that logs should be printed to.  
If not set, the `sys_get_temp_dir() . '/moxter.log` file will be used.  

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
This variable can be used as a list with a `,` between the different regular expressions.

**DEBUG**  
If true, the server will output debug information to the log file (`sys_get_temp_dir() . '/moxter/log.txt'`).  
Can be changed by setting the `LOG_FILE` variable.  

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

### Docker

Easiest way to run the application is to start it as a docker container. The container have latest php-fpm
installed and will expose FPM on port 9000 by default. Connect it to your favorite proxy and pass in the environment
variables as you seem fit and it should work right away!

```
docker run registry.gitlab.com/jitesoft/open-source/php/moxter:latest
```
