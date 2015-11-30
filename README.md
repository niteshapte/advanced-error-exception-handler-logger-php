# advanced-error-exception-handler-logger-php
Handles and logs the errors and exception occurs in the project.

This is an advanced version of https://github.com/niteshapte/error-and-exception-handler-logger-php. In this one you can add custom codes for exception. Make sure when you create/use code for exception it follow some rules. For instance, use code 888 for exceptions occured during database connectivity. So, in case after a month you want to check how many times there was database connectivity failures, you just need to search for 888 in your log file.

 Also, it does not matter if exception class is custom or built-in.
