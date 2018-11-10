# SOS School Organising System

Die (Schul-)Lebensretter

## Quick start

### Install

#### Deploy api on PHP Server

You can find the SOS api in the api folder. Adjust the config.php in the ./include folder and create a new MySQL database with the relating dump.

#### Deploy static frontend on webserver

The frontend is static, which means you do not have to execute any script. Just change the baseURL and enter the apiBaseURL (the url where the api can be called from) globally in every js script and adjust it to the base url of your webserver. After that, deploy the entire project (for example via FTP) and you can start working with SOS.

Good luck!

## Documentation

License: released under the MIT License

## Support

If you have any questions or concerns, don't hesitate to contact us: opensource@dnddev.com. www.dnd.one
